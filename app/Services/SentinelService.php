<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Screening via Sentinel AML (aml.bluearrow.ae).
 *
 * Every screening runs on Sentinel's engine — sanctions lists, adverse media,
 * PEP check and the flag-threshold verdict — and is saved there as a case owned
 * by the tenant's company, so the portal and Sentinel always show the same
 * result and the same report. Blue Arrow reviews flagged cases in Sentinel;
 * syncTenant() brings those decisions back into the portal.
 *
 * Authenticates with a service key (SENTINEL_API_KEY), never a user login.
 */
class SentinelService
{
    private ?Tenant $tenant = null;
    private ?string $actor  = null;
    private string $source  = 'portal';

    /**
     * Who the screenings are for, when there is no web request to tell
     * (batch and monitoring commands): tenant, a label for "screened by", and source.
     */
    public function actingFor(?Tenant $tenant, string $actor, string $source = 'batch'): static
    {
        $this->tenant = $tenant;
        $this->actor  = $actor;
        $this->source = $source;
        return $this;
    }

    private function http()
    {
        return Http::baseUrl(config('sentinel.base_url'))
            ->withHeaders(['X-API-Key' => config('sentinel.api_key')])
            ->acceptJson();
    }

    private function screen(array $input): array
    {
        $tenant = $this->tenant ?? (app()->bound('tenant') ? app('tenant') : null);
        $user   = auth()->user();

        try {
            // Sanctions + adverse media run in parallel on Sentinel; allow for slow news APIs
            $response = $this->http()->timeout(90)->post('/screenings', $input + [
                'portal' => [
                    'tenantId'   => $tenant?->id,
                    'tenantName' => $tenant?->name,
                    'userName'   => $this->actor ?? $user?->name,
                    'userEmail'  => $this->actor ? null : $user?->email,
                    'source'     => $this->source,
                ],
            ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
            Log::error('Sentinel screening failed', ['status' => $response->status(), 'body' => $response->body(), 'query' => $input['query']]);
            return ['success' => false, 'error' => $response->json('error') ?? "Sentinel error {$response->status()}"];

        } catch (\Exception $e) {
            Log::error('Sentinel screening failed', ['error' => $e->getMessage(), 'query' => $input['query']]);
            return ['success' => false, 'error' => 'Screening service unavailable — please try again.'];
        }
    }

    public function screenEntity(array $params): array
    {
        return $this->screen([
            'query'      => (string) trim($params['query']),
            'entityType' => 'entity',
            'country'    => $params['country_of_issue'] ?? $params['country'] ?? '',
        ]);
    }

    public function screenIndividual(array $params): array
    {
        return $this->screen([
            'query'      => (string) trim($params['query']),
            'entityType' => 'individual',
            'country'    => ($params['nationality'] ?? null) ?: (($params['country'] ?? null) ?: ''),
            'dob'        => $params['dob'] ?? '',
            'idNumber'   => $params['id_number'] ?? '',
        ]);
    }

    /**
     * Portal summary of a Sentinel screening. 'status' is 'match' whenever Sentinel
     * flagged the subject for review (a sanctions hit above the flag threshold or
     * adverse media) and 'clear' otherwise; 'flag_reason' says which.
     */
    public static function summarise(array $data): array
    {
        $case      = $data['case'] ?? [];
        $hits      = $data['results'] ?? [];
        $media     = $data['media'] ?? null;
        $threshold = (int) ($case['flag_threshold'] ?? 95);

        $strongHits = array_values(array_filter($hits, fn ($h) => ($h['matchScore'] ?? 0) > $threshold));
        $mediaHits  = (int) ($case['media_hits'] ?? 0);

        return [
            'status'          => self::portalStatus($case['status'] ?? null),
            'total_hits'      => count($strongHits),
            'hits'            => array_slice($hits, 0, 10),
            'flag_reason'     => self::flagReason(count($strongHits) > 0, $mediaHits > 0),
            'media_hits'      => $mediaHits,
            'media_checked'   => (bool) ($case['media_checked'] ?? false),
            'pep_detected'    => (bool) ($media['pepDetected'] ?? false),
            'flag_threshold'  => $threshold,
            'case_id'         => $case['id'] ?? null,
            'sentinel_status' => $case['status'] ?? null,
            'risk_level'      => $case['risk_level'] ?? null,
            'session_id'      => $case['id'] ?? null,
            'raw'             => ['results' => $hits],
        ];
    }

    public static function portalStatus(?string $sentinelStatus): string
    {
        return $sentinelStatus === 'MARKED_AS_CLEAR' ? 'clear' : 'match';
    }

    private static function flagReason(bool $sanctions, bool $media): ?string
    {
        return match (true) {
            $sanctions && $media => 'sanctions_and_media',
            $sanctions           => 'sanctions',
            $media               => 'adverse_media',
            default              => null,
        };
    }

    // ── Flag threshold (one setting in Sentinel for both apps) ────────────

    public function flagThreshold(): ?int
    {
        $response = $this->http()->timeout(10)->get('/settings/screening');
        return $response->successful() ? (int) $response->json('flagThreshold') : null;
    }

    public function setFlagThreshold(int $threshold, string $updatedBy): bool
    {
        return $this->http()->timeout(10)
            ->put('/settings/screening', ['flagThreshold' => $threshold, 'updatedBy' => $updatedBy])
            ->successful();
    }

    // ── Cases & reports ───────────────────────────────────────────────────

    /** Sentinel case if it belongs to this tenant (null tenant = BAMC admin screenings). */
    public function caseFor(string $caseId, ?Tenant $tenant): ?array
    {
        $response = $this->http()->timeout(15)->get('/cases/' . rawurlencode($caseId));
        if (!$response->successful()) return null;
        $case = $response->json();
        $owner = $case['portal_tenant_id'] ?? null;
        return $tenant ? ((string) $owner === (string) $tenant->id ? $case : null)
                       : ($owner === null ? $case : null);
    }

    /** The compliance report (HTML) — the same document Sentinel shows. */
    public function reportHtml(string $caseId, string $viewer): ?string
    {
        $response = $this->http()->timeout(20)
            ->get('/cases/' . rawurlencode($caseId) . '/report', ['viewer' => $viewer]);
        return $response->successful() ? $response->body() : null;
    }

    /**
     * Pull Blue Arrow's review decisions from Sentinel into the tenant's screening
     * history and client profiles. Returns the number of screening logs updated.
     */
    public function syncTenant(Tenant $tenant): int
    {
        $response = $this->http()->timeout(20)->get('/cases', ['tenant' => $tenant->id, 'limit' => 1000]);
        if (!$response->successful()) {
            Log::warning('Sentinel sync failed', ['tenant' => $tenant->id, 'status' => $response->status()]);
            return 0;
        }
        $statusById = collect($response->json())->pluck('status', 'id');

        $updated = 0;
        $clients = [];
        \App\Models\ScreeningLog::where('tenant_id', $tenant->id)
            ->whereNotNull('result->case_id')
            ->each(function ($log) use ($statusById, &$updated, &$clients) {
                $result = $log->result ?? [];
                $sentinelStatus = $statusById[$result['case_id']] ?? null;
                if (!$sentinelStatus || $sentinelStatus === ($result['sentinel_status'] ?? null)) return;

                $result['sentinel_status'] = $sentinelStatus;
                $log->update(['status' => self::portalStatus($sentinelStatus), 'result' => $result]);
                $updated++;
                if ($log->bullion_client_id) $clients[$log->bullion_client_id] = $log->reference;
            });

        // A client's status follows its latest screening run (all subjects share its reference)
        foreach ($clients as $clientId => $reference) {
            $client = \App\Models\BullionClient::find($clientId);
            if (!$client || $client->screening_reference !== $reference) continue;
            $anyMatch = \App\Models\ScreeningLog::where('bullion_client_id', $clientId)
                ->where('reference', $reference)->where('status', 'match')->exists();
            $client->update(['screening_status' => $anyMatch ? 'match' : 'clear']);
        }
        return $updated;
    }
}
