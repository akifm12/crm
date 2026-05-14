<?php
// app/Http/Controllers/Tenant/ScreeningController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Services\SentinelService;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function __construct(private SentinelService $sentinel) {}

    // ── Screening page ─────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenant = app('tenant');

        // Pre-load client if passed from profile page
        $client = null;
        if ($request->filled('client')) {
            $client = BullionClient::where('tenant_id', $tenant->id)
                ->find($request->client);
        }

        return view('tenant.screening', compact('tenant', 'client'));
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

        $summary = SentinelService::summarise($result['data']);

        // If linked to a client, save the result
        $client = null;
        if ($request->filled('client_id')) {
            $client = BullionClient::where('tenant_id', $tenant->id)->find($request->client_id);
            if ($client) {
                $client->update([
                    'screening_status'    => $summary['status'],
                    'screening_date'      => now(),
                    'screening_reference' => 'SCR-' . strtoupper(substr(md5($query . now()), 0, 8)),
                    'screening_result'    => $summary,
                ]);
            }
        }

        return view('tenant.screening', [
            'tenant'  => $tenant,
            'client'  => $client,
            'result'  => $summary,
            'query'   => $query,
            'rawData' => $result['data'],
        ]);
    }

    // ── Screen a client directly from their profile ────────────────────────

    public function screenClient(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $client = BullionClient::with('shareholders')->find($client->id);
        $isCorporate = $client->client_type !== 'individual';
        $allResults  = [];

        // ── Screen main entity / individual ──────────────────────────────
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

        // ── Screen shareholders ──────────────────────────────────────────
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

                \Log::info('SH screen result', ['name' => $sh->name, 'success' => $shResult['success'], 'error' => $shResult['error'] ?? null]);
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

        // ── Determine overall status ─────────────────────────────────────
        $hasMatch  = collect($allResults)->contains(fn($r) => $r['summary']['status'] === 'match');
        $overallStatus = $hasMatch ? 'match' : 'clear';
        $totalHits = collect($allResults)->sum(fn($r) => $r['summary']['total_hits'] ?? 0);

        // ── Save to client record ────────────────────────────────────────
        $client->update([
            'screening_status'    => $overallStatus,
            'screening_date'      => now(),
            'screening_reference' => 'SCR-' . strtoupper(substr(md5($client->displayName() . now()), 0, 8)),
            'screening_result'    => array_merge($mainSummary, [
                'shareholders' => $shareholderResults,
                'all_results'  => $allResults,
                'screened_count' => count($allResults),
            ]),
        ]);

        $msg = $hasMatch
            ? "⚠️ Screening complete — {$totalHits} potential match(es) found across " . count($allResults) . " subject(s). Review required."
            : '✓ Screening complete — No matches found for company or shareholders.';

        return back()->with($hasMatch ? 'error' : 'success', $msg);
    }
}
