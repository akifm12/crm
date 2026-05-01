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
        $request->validate(['query' => 'required|string|min:2']);

        $type = $request->entity_type ?? 'entity';

        if ($type === 'individual') {
            $result = $this->sentinel->screenIndividual([
                'query'       => $request->query,
                'country'     => $request->country ?? 'UAE',
                'dob'         => $request->dob,
                'nationality' => $request->nationality,
            ]);
        } else {
            $result = $this->sentinel->screenEntity([
                'query'       => $request->query,
                'country'     => $request->country ?? 'UAE',
                'trade_license'   => $request->trade_license,
                'country_of_issue'=> $request->country_of_issue,
                'license_number'  => $request->license_number,
                'date_of_issue'   => $request->date_of_issue,
            ]);
        }

        if (!$result['success']) {
            return back()->with('error', 'Screening failed: ' . $result['error'])->withInput();
        }

        $summary = SentinelService::summarise($result['data']);

        return view('admin.screening.index', [
            'result'  => $summary,
            'query'       => $request->query,
                'country'     => $request->country ?? 'UAE',
            'rawData' => $result['data'],
        ]);
    }

    // ── Screen a CRM client (entity) ───────────────────────────────────────

    public function screenClient(Request $request, CrmClient $crm)
    {
        $result = $this->sentinel->screenEntity([
            'query'            => $crm->company_name,
            'trade_license'    => $crm->license_number,
            'country_of_issue' => $crm->country_inc,
            'license_number'   => $crm->license_number,
            'date_of_issue'    => $crm->license_issue?->format('Y-m-d'),
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
            'query'       => $shareholder->shareholder_name,
            'dob'         => $shareholder->birthdate?->format('Y-m-d'),
            'nationality' => $shareholder->nationality,
        ]);

        if (!$result['success']) {
            return back()->with('error', 'Screening failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $summary  = SentinelService::summarise($result['data']);
        $existing = $shareholder->client->screening_result ?? [];

        // Store shareholder result alongside client result
        $existing['shareholders'][$shareholder->id] = array_merge($summary, [
            'name'       => $shareholder->shareholder_name,
            'screened_at'=> now()->toDateTimeString(),
        ]);

        $shareholder->client->update(['screening_result' => $existing]);

        $msg = $summary['status'] === 'match'
            ? "⚠️ {$shareholder->shareholder_name} — {$summary['total_hits']} potential match(es) found."
            : "✓ {$shareholder->shareholder_name} — No matches found.";

        return back()->with($summary['status'] === 'match' ? 'error' : 'success', $msg);
    }
}
