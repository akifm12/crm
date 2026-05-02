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

        $isCorporate = $client->client_type !== 'individual';

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

        $summary = SentinelService::summarise($result['data']);

        $client->update([
            'screening_status'    => $summary['status'],
            'screening_date'      => now(),
            'screening_reference' => 'SCR-' . strtoupper(substr(md5($client->displayName() . now()), 0, 8)),
            'screening_result'    => $summary,
        ]);

        $msg = $summary['status'] === 'match'
            ? "⚠️ Screening complete — {$summary['total_hits']} potential match(es) found. Review required."
            : '✓ Screening complete — No matches found.';

        return back()->with($summary['status'] === 'match' ? 'error' : 'success', $msg);
    }
}
