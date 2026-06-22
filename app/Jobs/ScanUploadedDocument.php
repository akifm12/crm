<?php

namespace App\Jobs;

use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\ClientShareholder;
use App\Models\DocumentScanLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScanUploadedDocument
{
    public function __construct(
        public ClientDocument $document,
        public ?int $userId = null,
    ) {}

    public function handle(): void
    {
        if (!config('services.anthropic.key')) {
            Log::warning('ScanUploadedDocument: ANTHROPIC_API_KEY not set');
            return;
        }

        $client = BullionClient::with('shareholders')->find($this->document->bullion_client_id);
        if (!$client) return;

        // Build image/document content block
        try {
            $filePath = Storage::disk('local')->path($this->document->file_path);
            if (!file_exists($filePath)) return;

            $mime   = $this->document->mime_type ?? mime_content_type($filePath);
            $base64 = base64_encode(file_get_contents($filePath));

            if ($mime === 'image/jpg') $mime = 'image/jpeg';

            // Only scannable types
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'])) {
                return;
            }

            $contentBlock = $mime === 'application/pdf'
                ? ['type' => 'document', 'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => $base64]]
                : ['type' => 'image',    'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => $base64]];

        } catch (\Exception $e) {
            Log::error('ScanUploadedDocument: file read failed', ['error' => $e->getMessage()]);
            return;
        }

        $prompt = <<<'PROMPT'
You are a compliance document scanner for a UAE-based financial services portal. Analyze this document and extract all readable information.

Return ONLY a valid JSON object in this exact format (use null for any missing or unreadable fields):

{
  "document_type": "passport|emirates_id|trade_licence|ejari|other",
  "full_name": null,
  "company_name": null,
  "nationality": null,
  "dob": null,
  "gender": null,
  "passport_number": null,
  "passport_expiry": null,
  "eid_number": null,
  "eid_expiry": null,
  "trade_license_no": null,
  "trade_license_expiry": null,
  "ejari_number": null,
  "ejari_expiry": null
}

Rules:
- nationality must be ISO 3166-1 alpha-2 code (e.g. "AE", "IN", "GB", "US")
- All dates must be in YYYY-MM-DD format
- Emirates ID format: 784-XXXX-XXXXXXX-X
- Return ONLY the JSON object, no markdown, no explanation
PROMPT;

        try {
            $response = Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 512,
                'messages'   => [[
                    'role'    => 'user',
                    'content' => [$contentBlock, ['type' => 'text', 'text' => $prompt]],
                ]],
            ]);

            if (!$response->successful()) {
                DocumentScanLog::create([
                    'tenant_id'          => $this->document->tenant_id,
                    'bullion_client_id'  => $client->id,
                    'client_document_id' => $this->document->id,
                    'changes'            => [],
                    'status'             => 'failed',
                    'failure_reason'     => 'API error: ' . $response->status(),
                    'created_by'         => $this->userId,
                ]);
                return;
            }

            $text = trim($response->json('content.0.text', ''));
            $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
            $text = preg_replace('/```\s*$/m', '', $text);
            $scanned = json_decode(trim($text), true);

            if (!$scanned) {
                DocumentScanLog::create([
                    'tenant_id'          => $this->document->tenant_id,
                    'bullion_client_id'  => $client->id,
                    'client_document_id' => $this->document->id,
                    'changes'            => [],
                    'status'             => 'failed',
                    'failure_reason'     => 'Could not parse API response',
                    'created_by'         => $this->userId,
                ]);
                return;
            }

            $docType = $scanned['document_type'] ?? 'other';
            $changes = [];

            switch ($docType) {

                case 'trade_licence':
                    $changes = $this->applyTradelicence($client, $scanned);
                    break;

                case 'ejari':
                    $changes = $this->applyEjari($client, $scanned);
                    break;

                case 'passport':
                    $changes = $this->applyPassport($client, $scanned);
                    break;

                case 'emirates_id':
                    $changes = $this->applyEmiratesId($client, $scanned);
                    break;
            }

            // Also update the ClientDocument expiry_date if we found one and it's empty
            if (!$this->document->expiry_date) {
                $docExpiry = match ($docType) {
                    'trade_licence' => $scanned['trade_license_expiry'] ?? null,
                    'ejari'         => $scanned['ejari_expiry'] ?? null,
                    'passport'      => $scanned['passport_expiry'] ?? null,
                    'emirates_id'   => $scanned['eid_expiry'] ?? null,
                    default         => null,
                };
                if ($docExpiry) {
                    $this->document->update(['expiry_date' => $docExpiry]);
                }
            }

            DocumentScanLog::create([
                'tenant_id'              => $this->document->tenant_id,
                'bullion_client_id'      => $client->id,
                'client_document_id'     => $this->document->id,
                'document_type_detected' => $docType,
                'raw_response'           => $scanned,
                'changes'                => $changes,
                'status'                 => empty($changes) ? 'no_changes' : 'applied',
                'created_by'             => $this->userId,
            ]);

        } catch (\Exception $e) {
            Log::error('ScanUploadedDocument: exception', ['error' => $e->getMessage()]);
        }
    }

    // ── Per-document-type handlers ──────────────────────────────────────────

    private function applyTradelicence(BullionClient $client, array $s): array
    {
        $changes = [];

        // Licence number: fill if empty
        if (!empty($s['trade_license_no']) && empty($client->trade_license_no)) {
            $changes[] = $this->applyChange($client, 'client', 'trade_license_no', $s['trade_license_no']);
        }

        // Expiry: fill if empty OR overwrite if currently expired
        if (!empty($s['trade_license_expiry'])) {
            $isEmpty   = empty($client->trade_license_expiry);
            $isExpired = $client->trade_license_expiry && $client->trade_license_expiry->isPast();

            if ($isEmpty || $isExpired) {
                $changes[] = $this->applyChange($client, 'client', 'trade_license_expiry', $s['trade_license_expiry']);
            }
        }

        // Company name: fill if empty
        if (!empty($s['company_name']) && empty($client->company_name)) {
            $changes[] = $this->applyChange($client, 'client', 'company_name', $s['company_name']);
        }

        return array_filter($changes);
    }

    private function applyEjari(BullionClient $client, array $s): array
    {
        $changes = [];

        if (!empty($s['ejari_number']) && empty($client->ejari_number)) {
            $changes[] = $this->applyChange($client, 'client', 'ejari_number', $s['ejari_number']);
        }

        if (!empty($s['ejari_expiry'])) {
            $isEmpty   = empty($client->ejari_expiry);
            $isExpired = $client->ejari_expiry && $client->ejari_expiry->isPast();

            if ($isEmpty || $isExpired) {
                $changes[] = $this->applyChange($client, 'client', 'ejari_expiry', $s['ejari_expiry']);
            }
        }

        return array_filter($changes);
    }

    private function applyPassport(BullionClient $client, array $s): array
    {
        $changes = [];

        // Individual client: fields are directly on BullionClient
        if ($client->client_type === 'individual') {
            foreach ([
                'full_name'       => 'full_name',
                'nationality'     => 'nationality',
                'dob'             => 'dob',
                'passport_number' => 'passport_number',
            ] as $src => $dest) {
                if (!empty($s[$src]) && empty($client->$dest)) {
                    $changes[] = $this->applyChange($client, 'client', $dest, $s[$src]);
                }
            }
            // Expiry: fill if empty OR expired
            if (!empty($s['passport_expiry'])) {
                $isEmpty   = empty($client->passport_expiry);
                $isExpired = $client->passport_expiry && $client->passport_expiry->isPast();
                if ($isEmpty || $isExpired) {
                    $changes[] = $this->applyChange($client, 'client', 'passport_expiry', $s['passport_expiry']);
                }
            }
            return array_filter($changes);
        }

        // Corporate: match to a shareholder
        $shareholder = $this->matchShareholder($client, $s['full_name'] ?? null, $s['passport_number'] ?? null, 'passport_number');

        if (!$shareholder) return [];

        foreach ([
            'full_name'       => 'name',
            'nationality'     => 'nationality',
            'dob'             => 'dob',
            'passport_number' => 'passport_number',
        ] as $src => $dest) {
            if (!empty($s[$src]) && empty($shareholder->$dest)) {
                $changes[] = $this->applyChange($shareholder, 'shareholder', $dest, $s[$src]);
            }
        }

        if (!empty($s['passport_expiry'])) {
            $isEmpty   = empty($shareholder->passport_expiry);
            $isExpired = $shareholder->passport_expiry && $shareholder->passport_expiry->isPast();
            if ($isEmpty || $isExpired) {
                $changes[] = $this->applyChange($shareholder, 'shareholder', 'passport_expiry', $s['passport_expiry']);
            }
        }

        return array_filter($changes);
    }

    private function applyEmiratesId(BullionClient $client, array $s): array
    {
        $changes = [];

        if ($client->client_type === 'individual') {
            if (!empty($s['eid_number']) && empty($client->eid_number)) {
                $changes[] = $this->applyChange($client, 'client', 'eid_number', $s['eid_number']);
            }
            if (!empty($s['eid_expiry'])) {
                $isEmpty   = empty($client->eid_expiry);
                $isExpired = $client->eid_expiry && $client->eid_expiry->isPast();
                if ($isEmpty || $isExpired) {
                    $changes[] = $this->applyChange($client, 'client', 'eid_expiry', $s['eid_expiry']);
                }
            }
            return array_filter($changes);
        }

        $shareholder = $this->matchShareholder($client, $s['full_name'] ?? null, $s['eid_number'] ?? null, 'eid_number');
        if (!$shareholder) return [];

        if (!empty($s['eid_number']) && empty($shareholder->eid_number)) {
            $changes[] = $this->applyChange($shareholder, 'shareholder', 'eid_number', $s['eid_number']);
        }
        if (!empty($s['eid_expiry'])) {
            $isEmpty   = empty($shareholder->eid_expiry);
            $isExpired = $shareholder->eid_expiry && $shareholder->eid_expiry->isPast();
            if ($isEmpty || $isExpired) {
                $changes[] = $this->applyChange($shareholder, 'shareholder', 'eid_expiry', $s['eid_expiry']);
            }
        }
        if (!empty($s['full_name']) && empty($shareholder->name)) {
            $changes[] = $this->applyChange($shareholder, 'shareholder', 'name', $s['full_name']);
        }

        return array_filter($changes);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Match a shareholder by doc number first, then fuzzy name match.
     * Returns null if no confident match.
     */
    private function matchShareholder(BullionClient $client, ?string $name, ?string $docNumber, string $docField): ?ClientShareholder
    {
        $shareholders = $client->shareholders;

        if ($shareholders->isEmpty()) return null;

        // Exact doc number match
        if ($docNumber) {
            $match = $shareholders->firstWhere($docField, $docNumber);
            if ($match) return $match;
        }

        // Only one shareholder — assume it's them
        if ($shareholders->count() === 1) return $shareholders->first();

        // Fuzzy name match (must be > 60% similar)
        if ($name) {
            $best      = null;
            $bestScore = 0;
            foreach ($shareholders as $sh) {
                similar_text(
                    strtolower(trim($name)),
                    strtolower(trim($sh->name)),
                    $pct
                );
                if ($pct > $bestScore) {
                    $bestScore = $pct;
                    $best      = $sh;
                }
            }
            if ($bestScore >= 60) return $best;
        }

        return null;
    }

    /**
     * Apply a single field change and return the change record.
     * Returns null if the value didn't actually change.
     */
    private function applyChange(object $model, string $modelType, string $field, mixed $newValue): ?array
    {
        $oldValue = $model->$field;

        // Normalise Carbon instances for comparison/storage
        if ($oldValue instanceof \Carbon\Carbon) {
            $oldValue = $oldValue->toDateString();
        }

        $normalNew = is_string($newValue) ? trim($newValue) : $newValue;

        if ($oldValue === $normalNew) return null;

        $model->update([$field => $normalNew]);

        return [
            'model'     => $modelType,
            'model_id'  => $model->id,
            'field'     => $field,
            'old_value' => $oldValue,
            'new_value' => $normalNew,
        ];
    }
}
