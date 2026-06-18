<?php
// app/Http/Controllers/Tenant/ScreeningController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\ScreeningLog;
use App\Services\SentinelService;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function __construct(private SentinelService $sentinel) {}

    // ── Screening page ─────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenant = app('tenant');

        $client = null;
        if ($request->filled('client')) {
            $client = BullionClient::where('tenant_id', $tenant->id)->find($request->client);
        }

        $logsQuery = ScreeningLog::where('tenant_id', $tenant->id)
            ->with(['client', 'screener'])
            ->latest();

        if ($request->filled('search')) {
            $logsQuery->where('query', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $logsQuery->where('status', $request->status);
        }
        if ($request->filled('source')) {
            $logsQuery->where('source', $request->source);
        }

        $logs = $logsQuery->paginate(20)->withQueryString();

        return view('tenant.screening', compact('tenant', 'client', 'logs'));
    }

    // ── Run ad-hoc screening ───────────────────────────────────────────────

    public function run(Request $request)
    {
        $request->validate(['search_query' => 'required|string|min:2']);

        $tenant  = app('tenant');
        $type    = $request->input('entity_type', 'entity');
        $query   = $request->input('search_query');
        $country = $request->input('country', 'UAE');

        if ($type === 'individual') {
            $result = $this->sentinel->screenIndividual([
                'query'       => $query,
                'country'     => $country,
                'dob'         => $request->input('dob'),
                'nationality' => $request->input('nationality'),
            ]);
        } else {
            $result = $this->sentinel->screenEntity([
                'query'            => $query,
                'country'          => $country,
                'country_of_issue' => $country,
                'license_number'   => $request->input('license_number'),
                'date_of_issue'    => $request->input('date_of_issue'),
            ]);
        }

        if (!$result['success']) {
            return back()->with('error', 'Screening failed: ' . $result['error'])->withInput();
        }

        $summary   = SentinelService::summarise($result['data']);
        $reference = 'SCR-' . strtoupper(substr(md5($query . now()), 0, 8));

        // If linked to a client, save the result to their profile
        $client = null;
        if ($request->filled('client_id')) {
            $client = BullionClient::where('tenant_id', $tenant->id)->find($request->client_id);
            if ($client) {
                $client->update([
                    'screening_status'    => $summary['status'],
                    'screening_date'      => now(),
                    'screening_reference' => $reference,
                    'screening_result'    => $summary,
                ]);
            }
        }

        // Always log to screening history
        ScreeningLog::create([
            'tenant_id'         => $tenant->id,
            'bullion_client_id' => $client?->id,
            'screened_by'       => auth()->id(),
            'query'             => $query,
            'entity_type'       => $type,
            'status'            => $summary['status'],
            'total_hits'        => $summary['total_hits'],
            'source'            => 'adhoc',
            'reference'         => $reference,
            'result'            => $summary,
        ]);

        $logs = ScreeningLog::where('tenant_id', $tenant->id)
            ->with(['client', 'screener'])->latest()->paginate(20);

        return view('tenant.screening', [
            'tenant'  => $tenant,
            'client'  => $client,
            'result'  => $summary,
            'query'   => $query,
            'rawData' => $result['data'],
            'logs'    => $logs,
        ]);
    }

    // ── Screen a single subject (AJAX) ────────────────────────────────────
    public function screenSubject(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $type = $request->input('type', 'entity'); // entity | individual
        $name = $request->input('name');

        if ($type === 'entity') {
            $result = $this->sentinel->screenEntity([
                'query'            => (string) $client->company_name,
                'country'          => (string) ($client->country_of_incorporation ?? 'UAE'),
                'country_of_issue' => (string) ($client->country_of_incorporation ?? 'UAE'),
                'license_number'   => (string) ($client->trade_license_no ?? ''),
                'date_of_issue'    => $client->trade_license_issue?->format('Y-m-d') ?? '',
            ]);
        } else {
            $sh = $client->shareholders()->where('name', $name)->first();
            $result = $this->sentinel->screenIndividual([
                'query'       => (string) $name,
                'country'     => (string) ($sh?->nationality ?? 'UAE'),
                'nationality' => (string) ($sh?->nationality ?? ''),
                'dob'         => $sh?->dob?->format('Y-m-d') ?? '',
            ]);
        }

        if (!$result['success']) {
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Screening failed']);
        }

        $summary = SentinelService::summarise($result['data']);
        return response()->json(['success' => true, 'summary' => $summary, 'name' => $name ?? $client->company_name]);
    }

    // ── Save combined screening results (AJAX) ────────────────────────────
    public function screenSave(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $allResults   = $request->input('all_results', []);
        $hasMatch     = collect($allResults)->contains(fn($r) => ($r['summary']['status'] ?? '') === 'match');
        $shareholders = array_values(array_filter($allResults, fn($r) => $r['role'] !== 'Company'));
        $reference    = 'SCR-' . strtoupper(substr(md5($client->displayName() . now()), 0, 8));
        $totalHits    = collect($allResults)->sum(fn($r) => $r['summary']['total_hits'] ?? 0);

        $client->update([
            'screening_status'    => $hasMatch ? 'match' : 'clear',
            'screening_date'      => now(),
            'screening_reference' => $reference,
            'screening_result'    => [
                'status'         => $hasMatch ? 'match' : 'clear',
                'total_hits'     => $totalHits,
                'hits'           => $allResults[0]['summary']['hits'] ?? [],
                'shareholders'   => $shareholders,
                'all_results'    => $allResults,
                'screened_count' => count($allResults),
            ],
        ]);

        // Log one row per subject screened
        foreach ($allResults as $r) {
            ScreeningLog::create([
                'tenant_id'         => $tenant->id,
                'bullion_client_id' => $client->id,
                'screened_by'       => auth()->id(),
                'query'             => $r['name'] ?? $client->displayName(),
                'entity_type'       => ($r['role'] ?? '') === 'Company' ? 'entity' : 'individual',
                'status'            => $r['summary']['status'] ?? 'clear',
                'total_hits'        => $r['summary']['total_hits'] ?? 0,
                'source'            => 'kyc',
                'reference'         => $reference,
                'result'            => $r['summary'] ?? [],
            ]);
        }

        return response()->json(['success' => true, 'status' => $hasMatch ? 'match' : 'clear', 'tab' => 'screening']);
    }

    public function screenClient(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $client      = BullionClient::with('shareholders')->find($client->id);
        $isCorporate = $client->client_type !== 'individual';
        $allResults  = [];

        if ($isCorporate) {
            $result = $this->sentinel->screenEntity([
                'query'            => (string) $client->company_name,
                'country'          => (string) ($client->country_of_incorporation ?? 'UAE'),
                'country_of_issue' => (string) ($client->country_of_incorporation ?? 'UAE'),
                'license_number'   => (string) ($client->trade_license_no ?? ''),
                'date_of_issue'    => $client->trade_license_issue?->format('Y-m-d') ?? '',
            ]);
        } else {
            $result = $this->sentinel->screenIndividual([
                'query'       => (string) $client->full_name,
                'country'     => (string) ($client->nationality ?? 'UAE'),
                'nationality' => (string) ($client->nationality ?? ''),
                'dob'         => $client->dob?->format('Y-m-d') ?? '',
            ]);
        }

        if (!$result['success']) {
            return back()->with('error', 'Screening failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $mainSummary = SentinelService::summarise($result['data']);
        $allResults[] = [
            'name'    => $isCorporate ? $client->company_name : $client->full_name,
            'role'    => $isCorporate ? 'Company' : 'Individual',
            'summary' => $mainSummary,
        ];

        $shareholderResults = [];
        if ($isCorporate && $client->shareholders->count()) {
            foreach ($client->shareholders as $sh) {
                if (empty($sh->name)) continue;

                $shResult = $this->sentinel->screenIndividual([
                    'query'       => (string) $sh->name,
                    'country'     => (string) ($sh->nationality ?? 'UAE'),
                    'nationality' => (string) ($sh->nationality ?? ''),
                    'dob'         => $sh->dob?->format('Y-m-d') ?? '',
                ]);

                if ($shResult['success']) {
                    $shSummary = SentinelService::summarise($shResult['data']);
                    $shareholderResults[] = [
                        'name'    => $sh->name,
                        'role'    => $sh->is_ubo ? 'Shareholder / UBO' : 'Shareholder',
                        'summary' => $shSummary,
                    ];
                    $allResults[] = [
                        'name'    => $sh->name,
                        'role'    => $sh->is_ubo ? 'Shareholder / UBO' : 'Shareholder',
                        'summary' => $shSummary,
                    ];
                }
            }
        }

        $hasMatch      = collect($allResults)->contains(fn($r) => $r['summary']['status'] === 'match');
        $overallStatus = $hasMatch ? 'match' : 'clear';
        $totalHits     = collect($allResults)->sum(fn($r) => $r['summary']['total_hits'] ?? 0);
        $reference     = 'SCR-' . strtoupper(substr(md5($client->displayName() . now()), 0, 8));

        $client->update([
            'screening_status'    => $overallStatus,
            'screening_date'      => now(),
            'screening_reference' => $reference,
            'screening_result'    => array_merge($mainSummary, [
                'shareholders'   => $shareholderResults,
                'all_results'    => $allResults,
                'screened_count' => count($allResults),
            ]),
        ]);

        // Log one row per subject screened
        foreach ($allResults as $r) {
            ScreeningLog::create([
                'tenant_id'         => $tenant->id,
                'bullion_client_id' => $client->id,
                'screened_by'       => auth()->id(),
                'query'             => $r['name'] ?? $client->displayName(),
                'entity_type'       => ($r['role'] ?? '') === 'Company' ? 'entity' : 'individual',
                'status'            => $r['summary']['status'] ?? 'clear',
                'total_hits'        => $r['summary']['total_hits'] ?? 0,
                'source'            => 'kyc',
                'reference'         => $reference,
                'result'            => $r['summary'] ?? [],
            ]);
        }

        $msg = $hasMatch
            ? "⚠️ Screening complete — {$totalHits} potential match(es) found across " . count($allResults) . " subject(s). Review required."
            : '✓ Screening complete — No matches found for company or shareholders.';

        return back()->with($hasMatch ? 'error' : 'success', $msg);
    }
}
