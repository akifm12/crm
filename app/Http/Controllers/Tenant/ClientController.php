<?php
// app/Http/Controllers/Tenant/ClientController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\ScanUploadedDocument;
use App\Models\BullionClient;
use App\Models\ClientDocument;
use App\Models\DocumentScanLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    private function countries(): array
    {
        return \App\Models\Country::orderBy('country_name')->pluck('country_name', 'country_code')->toArray();
    }

    public function index(Request $request)
    {
        $tenant = app('tenant');
        $query  = BullionClient::where('tenant_id', $tenant->id);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhere('full_name',  'like', "%{$s}%")
                  ->orWhere('trade_license_no', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('risk'))   $query->where('risk_rating', $request->risk);
        if ($request->filled('type'))   $query->where('client_type', $request->type);
        if ($request->filled('year'))   $query->whereYear('created_at', $request->year);

        $sort = $request->input('sort', 'newest');

        $query->with(['documents:id,bullion_client_id,document_type']);

        $clients = match($sort) {
            'az'     => $query->orderBy('company_name')->orderBy('full_name')->paginate(50)->withQueryString(),
            'za'     => $query->orderByRaw('COALESCE(company_name, full_name) DESC')->paginate(50)->withQueryString(),
            'oldest' => $query->oldest()->paginate(50)->withQueryString(),
            default  => $query->latest()->paginate(50)->withQueryString(),
        };

        $typeCounts = BullionClient::where('tenant_id', $tenant->id)
            ->selectRaw('client_type, count(*) as total')
            ->groupBy('client_type')
            ->pluck('total', 'client_type')
            ->toArray();
        $typeCounts[''] = array_sum($typeCounts);

        // Available years for filter
        $years = BullionClient::where('tenant_id', $tenant->id)
            ->whereNotNull('created_at')
            ->selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        return view('tenant.clients.index', compact('tenant', 'clients', 'typeCounts', 'years'));
    }

    public function create()
    {
        $tenant    = app('tenant');
        $countries = $this->countries();
        return view('tenant.clients.create', compact('tenant', 'countries'));
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $tenant = app('tenant');
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) return response()->json([]);

        $clients = BullionClient::where('tenant_id', $tenant->id)
            ->where(function ($query) use ($q) {
                $query->where('full_name', 'like', "%{$q}%")
                      ->orWhere('company_name', 'like', "%{$q}%");
            })
            ->select('id', 'client_type', 'full_name', 'company_name', 'passport_number', 'eid_number', 'trade_license_no', 'nationality', 'dob', 'status', 'screening_status', 'created_at')
            ->limit(6)
            ->get()
            ->map(fn($c) => [
                'id'               => $c->id,
                'name'             => $c->displayName(),
                'type'             => $c->client_type,
                'identifier'       => $c->client_type === 'individual'
                    ? ($c->passport_number ? 'PP: '.$c->passport_number : ($c->eid_number ? 'EID: '.$c->eid_number : ''))
                    : ($c->trade_license_no ? 'TL: '.$c->trade_license_no : ''),
                'nationality'      => $c->nationality,
                'dob'              => $c->dob?->format('d M Y'),
                'status'           => $c->status,
                'screening_status' => $c->screening_status,
                'added'            => $c->created_at?->format('d M Y'),
            ]);

        return response()->json($clients);
    }

    public function scanDocument(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!config('services.anthropic.key')) {
            return response()->json(['error' => 'ANTHROPIC_API_KEY is not configured. Add it to your .env file.'], 500);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first('document')], 422);
        }

        try {
            $file   = $request->file('document');
            $mime   = $file->getMimeType();
            $base64 = base64_encode(file_get_contents($file->getRealPath()));

            // Normalise JPEG mime (some systems return image/jpg)
            if ($mime === 'image/jpg') $mime = 'image/jpeg';

            $contentBlock = $mime === 'application/pdf'
                ? ['type' => 'document', 'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => $base64]]
                : ['type' => 'image',    'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => $base64]];

            $prompt = <<<'PROMPT'
You are a compliance document scanner for a UAE-based financial services portal. Analyze this document and extract client information for KYC onboarding.

Return ONLY a valid JSON object in this exact format (use null for any missing or unreadable fields):

{
  "document_type": "passport|emirates_id|trade_licence|other",
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
  "legal_form": null,
  "address": null,
  "phone": null,
  "email": null
}

Rules:
- nationality must be ISO 3166-1 alpha-2 code (e.g. "AE", "IN", "GB", "US")
- All dates must be in YYYY-MM-DD format
- Emirates ID format: 784-XXXX-XXXXXXX-X
- Return ONLY the JSON object, no markdown, no explanation
PROMPT;

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key'         => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 1024,
                'messages'   => [[
                    'role'    => 'user',
                    'content' => [$contentBlock, ['type' => 'text', 'text' => $prompt]],
                ]],
            ]);

            if (!$response->successful()) {
                $apiError = $response->json('error.message') ?? 'Anthropic API error ('.$response->status().')';
                return response()->json(['error' => $apiError], 422);
            }

            $text = trim($response->json('content.0.text', ''));
            $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
            $text = preg_replace('/```\s*$/m', '', $text);
            $data = json_decode(trim($text), true);

            if (!$data) {
                return response()->json(['error' => 'Could not read document fields. Please fill the form manually.'], 422);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Scan failed: '.$e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');

        // For individual clients — require at least passport OR Emirates ID
        if ($request->input('client_type') === 'individual') {
            if (empty($request->passport_number) && empty($request->eid_number)) {
                return back()
                    ->withInput()
                    ->withErrors(['passport_number' => 'At least one of Passport number or Emirates ID is required.']);
            }

            // Duplicate check — passport number
            if (!empty($request->passport_number)) {
                $existing = BullionClient::where('tenant_id', $tenant->id)
                    ->where('passport_number', $request->passport_number)
                    ->first();
                if ($existing) {
                    return back()->withInput()->withErrors([
                        'passport_number' => 'A client with this passport number already exists: '
                            . $existing->displayName()
                            . '. Please open their profile and add a transaction there instead.',
                    ])->with('duplicate_client_id', $existing->id)
                      ->with('duplicate_client_name', $existing->displayName());
                }
            }

            // Duplicate check — Emirates ID
            if (!empty($request->eid_number)) {
                $existing = BullionClient::where('tenant_id', $tenant->id)
                    ->where('eid_number', $request->eid_number)
                    ->first();
                if ($existing) {
                    return back()->withInput()->withErrors([
                        'eid_number' => 'A client with this Emirates ID already exists: '
                            . $existing->displayName()
                            . '. Please open their profile and add a transaction there instead.',
                    ])->with('duplicate_client_id', $existing->id)
                      ->with('duplicate_client_name', $existing->displayName());
                }
            }
        }

        // Duplicate check for corporate clients — trade licence number
        if ($request->input('client_type') !== 'individual' && !empty($request->trade_license_no)) {
            $existing = BullionClient::where('tenant_id', $tenant->id)
                ->where('trade_license_no', $request->trade_license_no)
                ->first();
            if ($existing) {
                return back()->withInput()->withErrors([
                    'trade_license_no' => 'A client with this trade licence number already exists: '
                        . $existing->displayName()
                        . '. Please open their profile and add a transaction there instead.',
                ])->with('duplicate_client_id', $existing->id)
                  ->with('duplicate_client_name', $existing->displayName());
            }
        }

        $data = $request->except(['signatories', 'shareholders', 'ubos', 'documents', 'doc_labels', 'doc_expiry', 'doc_required', '_token', 'extra_data']);

        // Collect extra_data fields from sector config
        $extraData = $request->input('extra_data', []);

        $client = BullionClient::create(array_merge(
            $data,
            [
                'tenant_id'   => $tenant->id,
                'created_by'  => auth()->id(),
                'cdd_type'    => $request->input('cdd_type') ?: 'standard',
                'risk_rating' => $request->input('risk_rating') ?: 'low',
                'status'      => $request->input('status') ?: 'pending',
                'extra_data'  => !empty($extraData) ? $extraData : null,
            ]
        ));

        foreach ($request->input('signatories', []) as $sig) {
            if (!empty($sig['full_name'])) $client->signatories()->create($sig);
        }
        foreach ($request->input('shareholders', []) as $sh) {
            if (!empty($sh['name'])) $client->shareholders()->create($sh);
        }
        foreach ($request->input('ubos', []) as $ubo) {
            if (!empty($ubo['full_name'])) $client->ubos()->create($ubo);
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $docType => $file) {
                if (!$file || !$file->isValid()) continue;
                $path = $file->store("tenants/{$tenant->id}/clients/{$client->id}", 'local');
                ClientDocument::create([
                    'bullion_client_id' => $client->id,
                    'tenant_id'         => $tenant->id,
                    'document_type'     => $docType,
                    'document_label'    => $request->input("doc_labels.{$docType}", $docType),
                    'file_path'         => $path,
                    'file_name'         => $file->getClientOriginalName(),
                    'mime_type'         => $file->getMimeType(),
                    'file_size'         => $file->getSize(),
                    'expiry_date'       => $request->input("doc_expiry.{$docType}") ?: null,
                    'is_required'       => (bool) $request->input("doc_required.{$docType}", false),
                    'uploaded_by'       => auth()->id(),
                ]);
            }
        }

        return redirect()
            ->route('tenant.clients.confirm', [$tenant->slug, $client->id])
            ->with('success', 'Client record created successfully.');
    }

    // ── Soft delete (archive) client ──────────────────────────────────────
    public function destroy(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $client->delete(); // soft delete — sets deleted_at

        return redirect()
            ->route('tenant.clients.index', $tenant->slug)
            ->with('success', $client->displayName() . ' has been archived and removed from your client list.');
    }

    // ── Transactions ──────────────────────────────────────────────────────

    public function addTransaction(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $request->validate(['visit_date' => 'required|date']);

        \App\Models\ClientTransaction::create([
            'bullion_client_id' => $client->id,
            'tenant_id'         => $tenant->id,
            'visit_date'        => $request->visit_date,
            'invoice_number'    => $request->invoice_number,
            'invoice_amount'    => $request->invoice_amount,
            'transaction_type'  => $request->transaction_type,
            'notes'             => $request->notes,
            'created_by'        => auth()->id(),
        ]);

        return redirect(route('tenant.clients.show', [$tenant->slug, $client->id]) . '?tab=transactions')->with('success', 'Transaction added.');
    }

    public function deleteTransaction(Request $request, string $slug, BullionClient $client, \App\Models\ClientTransaction $transaction)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        abort_if($transaction->bullion_client_id !== $client->id, 404);
        $transaction->delete();
        return redirect(route('tenant.clients.show', [$tenant->slug, $client->id]) . '?tab=transactions')->with('success', 'Transaction removed.');
    }

    // ── Screen preview (during create wizard, before saving) ───────────────
    public function screenPreview(Request $request, string $slug)
    {
        $tenant  = app('tenant');
        $service = app(\App\Services\SentinelService::class);
        $results = [];

        // Screen main entity or individual
        $companyName = $request->input('company_name');
        $fullName    = $request->input('full_name');

        if ($companyName) {
            $res = $service->screenEntity([
                'query'          => $companyName,
                'country'        => $request->input('country_of_incorporation', 'UAE'),
                'country_of_issue' => $request->input('country_of_incorporation', 'UAE'),
                'license_number' => $request->input('trade_license_no', ''),
            ]);
            $summary   = \App\Services\SentinelService::summarise($res['data'] ?? []);
            $results[] = ['name' => $companyName, 'role' => 'Company', 'result' => $summary];
        } elseif ($fullName) {
            $res = $service->screenIndividual([
                'query'       => $fullName,
                'country'     => $request->input('nationality', 'UAE'),
                'nationality' => $request->input('nationality', ''),
                'dob'         => $request->input('dob', ''),
            ]);
            $summary   = \App\Services\SentinelService::summarise($res['data'] ?? []);
            $results[] = ['name' => $fullName, 'role' => 'Individual', 'result' => $summary];
        }

        // Screen signatories
        foreach ($request->input('signatories', []) as $sig) {
            if (empty($sig['full_name'])) continue;
            $res = $service->screenIndividual([
                'query'       => $sig['full_name'],
                'country'     => $sig['nationality'] ?? 'UAE',
                'nationality' => $sig['nationality'] ?? '',
                'dob'         => $sig['dob'] ?? '',
            ]);
            $summary   = \App\Services\SentinelService::summarise($res['data'] ?? []);
            $results[] = ['name' => $sig['full_name'], 'role' => 'Signatory', 'result' => $summary];
        }

        // Screen shareholders
        foreach ($request->input('shareholders', []) as $sh) {
            if (empty($sh['name'])) continue;
            $res = $service->screenIndividual([
                'query'       => $sh['name'],
                'country'     => $sh['nationality'] ?? 'UAE',
                'nationality' => $sh['nationality'] ?? '',
                'dob'         => $sh['dob'] ?? '',
            ]);
            $summary   = \App\Services\SentinelService::summarise($res['data'] ?? []);
            $results[] = ['name' => $sh['name'], 'role' => 'Shareholder', 'result' => $summary];
        }

        // Screen UBOs
        foreach ($request->input('ubos', []) as $ubo) {
            if (empty($ubo['full_name'])) continue;
            $res = $service->screenIndividual([
                'query'       => $ubo['full_name'],
                'country'     => $ubo['nationality'] ?? 'UAE',
                'nationality' => $ubo['nationality'] ?? '',
                'dob'         => $ubo['dob'] ?? '',
            ]);
            $summary   = \App\Services\SentinelService::summarise($res['data'] ?? []);
            $results[] = ['name' => $ubo['full_name'], 'role' => 'UBO', 'result' => $summary];
        }

        // Log each subject to screening history (client not created yet, so no bullion_client_id)
        foreach ($results as $r) {
            \App\Models\ScreeningLog::create([
                'tenant_id'         => $tenant->id,
                'bullion_client_id' => null,
                'screened_by'       => auth()->id(),
                'query'             => $r['name'],
                'entity_type'       => $r['role'] === 'Company' ? 'entity' : 'individual',
                'status'            => $r['result']['status'] ?? 'clear',
                'total_hits'        => $r['result']['total_hits'] ?? 0,
                'source'            => 'kyc',
                'reference'         => null,
                'result'            => $r['result'],
            ]);
        }

        return response()->json(['results' => $results]);
    }

    // ── Confirmation page after client creation ────────────────────────────
    public function confirm(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos']);
        return view('tenant.clients.confirm', compact('tenant', 'client'));
    }

    public function show(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos', 'creator', 'transactions']);
        $documents = ClientDocument::where('bullion_client_id', $client->id)->orderBy('document_type')->get();
        $scanLogs  = DocumentScanLog::where('bullion_client_id', $client->id)->with('document')->latest()->get();
        return view('tenant.clients.show', compact('tenant', 'client', 'documents', 'scanLogs'));
    }

    public function edit(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos']);
        $countries = $this->countries();
        return view('tenant.clients.edit', compact('tenant', 'client', 'countries'));
    }

    public function update(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $data = $request->except(['_token', '_method', 'signatories', 'shareholders', 'ubos', 'extra_data']);

        // Protect nullable fields from overwriting with empty string
        foreach (['country_of_incorporation', 'nationality', 'cdd_type', 'risk_rating', 'status'] as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                unset($data[$field]);
            }
        }

        $data['cdd_type']    = $request->input('cdd_type') ?: $client->cdd_type ?: 'standard';
        $data['risk_rating'] = $request->input('risk_rating') ?: $client->risk_rating ?: 'low';
        $data['status']      = $request->input('status') ?: $client->status ?: 'pending';

        // Merge extra_data (sector-specific fields)
        $extraData = $request->input('extra_data', []);
        if (!empty($extraData)) {
            $data['extra_data'] = array_merge($client->extra_data ?? [], $extraData);
        }

        $client->update($data);

        if ($request->has('signatories')) {
            $client->signatories()->delete();
            foreach ($request->input('signatories', []) as $sig) {
                if (!empty($sig['full_name'])) $client->signatories()->create($sig);
            }
        }

        if ($request->has('shareholders')) {
            $client->shareholders()->delete();
            foreach ($request->input('shareholders', []) as $sh) {
                if (!empty($sh['name'])) $client->shareholders()->create($sh);
            }
        }

        if ($request->has('ubos')) {
            $client->ubos()->delete();
            foreach ($request->input('ubos', []) as $ubo) {
                if (!empty($ubo['full_name'])) $client->ubos()->create($ubo);
            }
        }

        return redirect()
            ->route('tenant.clients.show', [$slug, $client->id])
            ->with('success', 'Client record updated successfully.');
    }

    // ── Image compression helper ──────────────────────────────────────────
    private function storeOptimised(\Illuminate\Http\UploadedFile $file, string $storagePath): string
    {
        $mime = $file->getMimeType();

        // Only compress images
        if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])) {
            return $file->store($storagePath, 'local');
        }

        $maxWidth   = 1500;
        $quality    = 82;
        $srcPath    = $file->getRealPath();

        // Load image
        $src = match($mime) {
            'image/png'  => imagecreatefrompng($srcPath),
            'image/webp' => imagecreatefromwebp($srcPath),
            default      => imagecreatefromjpeg($srcPath),
        };

        if (!$src) {
            return $file->store($storagePath, 'local');
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Only resize if wider than max
        if ($origW > $maxWidth) {
            $newH  = (int) round($origH * ($maxWidth / $origW));
            $dst   = imagecreatetruecolor($maxWidth, $newH);

            // Preserve transparency for PNG
            if ($mime === 'image/png') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxWidth, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $dst;
            $w   = $maxWidth;
            $h   = $newH;
        } else {
            $w = $origW;
            $h = $origH;
        }

        // Store compressed
        $ext      = $mime === 'image/png' ? 'png' : 'jpg';
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_opt.' . $ext;
        $fullPath = storage_path("app/local/{$storagePath}");

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $destFile = "{$fullPath}/{$filename}";

        if ($mime === 'image/png') {
            imagepng($src, $destFile, (int) round((100 - $quality) / 10));
        } else {
            imagejpeg($src, $destFile, $quality);
        }

        imagedestroy($src);

        return "{$storagePath}/{$filename}";
    }

    public function bulkUploadDocuments(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $request->validate(['files.*' => 'required|file|max:20480']);

        $typeMap = [
            'passport'    => ['passport', 'pp'],
            'emirates_id' => ['eid', 'emirates', 'emiratesid', 'national_id', 'nationalid'],
            'visa'        => ['visa', 'residency', 'residence'],
            'trade_licence' => ['license', 'licence', 'trade', 'tradelic', 'tl'],
            'moa'         => ['moa', 'memorandum', 'articles', 'incorporation'],
            'ejari'       => ['ejari', 'tenancy', 'lease'],
            'bank_statement' => ['bank', 'statement', 'bankstat'],
            'vat_certificate' => ['vat', 'trn'],
            'tax_certificate' => ['tax', 'corporate_tax', 'ct'],
            'power_of_attorney' => ['poa', 'power_of_attorney', 'attorney'],
            'audited_accounts' => ['audit', 'accounts', 'financial'],
        ];

        $uploaded = 0;
        foreach ($request->file('files', []) as $file) {
            $nameLower = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $nameLower = preg_replace('/[^a-z0-9]/', '_', $nameLower);

            // Match document type from filename
            $docType  = 'other';
            $docLabel = 'Other Document';
            foreach ($typeMap as $type => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($nameLower, $kw)) {
                        $docType  = $type;
                        $docLabel = ucwords(str_replace('_', ' ', $type));
                        break 2;
                    }
                }
            }

            $path = $file->store("tenants/{$tenant->id}/clients/{$client->id}", 'local');

            $doc = ClientDocument::create([
                'bullion_client_id' => $client->id,
                'tenant_id'         => $tenant->id,
                'document_type'     => $docType,
                'document_label'    => $docLabel,
                'file_path'         => $path,
                'file_name'         => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'file_size'         => $file->getSize(),
                'uploaded_by'       => auth()->id(),
            ]);

            (new ScanUploadedDocument($doc, auth()->id()))->handle();
            $uploaded++;
        }

        return redirect(route('tenant.clients.show', [$tenant->slug, $client->id]) . '?tab=documents')->with('success', "{$uploaded} file(s) uploaded successfully.");
    }

    public function uploadDocument(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $request->validate(['file' => 'required|file|max:10240', 'document_type' => 'required|string', 'document_label' => 'required|string', 'expiry_date' => 'nullable|date']);
        $file = $request->file('file');
        $path = $file->store("tenants/{$tenant->id}/clients/{$client->id}", 'local');
        $doc = ClientDocument::create([
            'bullion_client_id' => $client->id,
            'tenant_id'         => $tenant->id,
            'document_type'     => $request->document_type,
            'document_label'    => $request->document_label,
            'file_path'         => $path,
            'file_name'         => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'expiry_date'       => $request->expiry_date,
            'uploaded_by'       => auth()->id(),
        ]);
        (new ScanUploadedDocument($doc, auth()->id()))->handle();
        return redirect(route('tenant.clients.show', [$tenant->slug, $client->id]) . '?tab=documents')->with('success', 'Document uploaded and scanned.');
    }

    public function downloadDocument(string $slug, ClientDocument $document)
    {
        $tenant = app('tenant');
        abort_if($document->tenant_id !== $tenant->id, 404);
        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function deleteDocument(string $slug, ClientDocument $document)
    {
        $tenant   = app('tenant');
        $clientId = $document->bullion_client_id;
        abort_if($document->tenant_id !== $tenant->id, 404);
        Storage::disk('local')->delete($document->file_path);
        $document->delete();
        return redirect(route('tenant.clients.show', [$tenant->slug, $clientId]) . '?tab=documents')->with('success', 'Document deleted.');
    }

    public function revertScanLog(string $slug, DocumentScanLog $log)
    {
        $tenant = app('tenant');
        abort_if($log->tenant_id !== $tenant->id, 404);

        if ($log->revert()) {
            return back()->with('success', 'Scan changes reverted successfully.');
        }

        return back()->with('error', 'This scan log has already been reverted or has no changes to undo.');
    }

    public function updateRisk(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $request->validate(['risk_rating' => 'required|in:low,medium,high', 'cdd_type' => 'required|in:standard,enhanced', 'next_review_date' => 'nullable|date', 'risk_notes' => 'nullable|string']);
        $client->update($request->only(['risk_rating', 'cdd_type', 'next_review_date', 'risk_notes']));
        return redirect(route('tenant.clients.show', [$tenant->slug, $client->id]) . '?tab=risk')->with('success', 'Risk rating updated.');
    }

    public function updateStatus(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $request->validate(['status' => 'required|in:active,pending,inactive,suspended']);
        $client->update(['status' => $request->status]);
        return redirect(route('tenant.clients.show', [$tenant->slug, $client->id]) . '?tab=overview')->with('success', 'Status updated.');
    }

    public function updateDeclarations(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->update([
            'decl_pep'            => $request->boolean('decl_pep'),
            'decl_supply_chain'   => $request->boolean('decl_supply_chain'),
            'decl_cahra'          => $request->boolean('decl_cahra'),
            'decl_source_of_funds'=> $request->boolean('decl_source_of_funds'),
            'decl_sanctions'      => $request->boolean('decl_sanctions'),
            'decl_ubo'            => $request->boolean('decl_ubo'),
            'decl_master_signed'  => $request->boolean('decl_master_signed'),
        ]);
        return redirect(route('tenant.clients.show', [$tenant->slug, $client->id]) . '?tab=declarations')->with('success', 'Declarations updated.');
    }
}
