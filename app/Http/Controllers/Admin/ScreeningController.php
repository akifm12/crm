<?php
// app/Http/Controllers/Admin/ScreeningController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmClient;
use App\Models\CrmShareholder;
use App\Services\SentinelService;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function __construct(private SentinelService $sentinel) {}

    // ── Standalone screening page ──────────────────────────────────────────

    public function index()
    {
        return view('admin.screening.index');
    }

    // ── Run ad-hoc screening from standalone form ──────────────────────────

    public function run(Request $request)
    {
        $request->validate(['search_query' => 'required|string|min:2']);

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

        return view('admin.screening.index', [
            'result'  => $summary,
            'query'   => $query,
            'rawData' => $result['data'],
        ]);
    }

    // ── Screen a CRM client (entity) ───────────────────────────────────────

    public function screenClient(Request $request, CrmClient $crm)
    {
        $result = $this->sentinel->screenEntity([
            'query'            => (string) $crm->company_name,
            'country'          => (string) ($crm->country_inc ?? 'UAE'),
            'country_of_issue' => (string) ($crm->country_inc ?? 'UAE'),
            'license_number'   => (string) ($crm->license_number ?? ''),
            'date_of_issue'    => $crm->license_issue?->format('Y-m-d') ?? '',
        ]);

        if (!$result['success']) {
            return back()->with('error', 'Screening failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $summary = SentinelService::summarise($result['data']);

        $crm->update([
            'screening_status'    => $summary['status'],
            'screening_date'      => now(),
            'screening_reference' => 'SCR-' . strtoupper(substr(md5($crm->company_name . now()), 0, 8)),
            'screening_result'    => $summary,
        ]);

        $msg = $summary['status'] === 'match'
            ? "⚠️ Screening complete — {$summary['total_hits']} potential match(es) found. Review required."
            : '✓ Screening complete — No matches found.';

        return back()->with($summary['status'] === 'match' ? 'error' : 'success', $msg);
    }

    // ── Screen a shareholder (individual) ─────────────────────────────────

    public function screenShareholder(Request $request, CrmShareholder $shareholder)
    {
        $result = $this->sentinel->screenIndividual([
            'query'       => (string) $shareholder->shareholder_name,
            'country'     => (string) ($shareholder->nationality ?? 'UAE'),
            'nationality' => (string) ($shareholder->nationality ?? ''),
            'dob'         => $shareholder->birthdate?->format('Y-m-d') ?? '',
        ]);

        if (!$result['success']) {
            return back()->with('error', 'Screening failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $summary  = SentinelService::summarise($result['data']);
        $existing = $shareholder->client->screening_result ?? [];

        $existing['shareholders'][$shareholder->id] = array_merge($summary, [
            'name'        => $shareholder->shareholder_name,
            'screened_at' => now()->toDateTimeString(),
        ]);

        $shareholder->client->update(['screening_result' => $existing]);

        $msg = $summary['status'] === 'match'
            ? "⚠️ {$shareholder->shareholder_name} — {$summary['total_hits']} potential match(es) found."
            : "✓ {$shareholder->shareholder_name} — No matches found.";

        return back()->with($summary['status'] === 'match' ? 'error' : 'success', $msg);
    }
}