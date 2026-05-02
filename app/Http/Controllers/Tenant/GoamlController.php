<?php
// app/Http/Controllers/Tenant/GoamlController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\GoamlReport;
use App\Models\GoamlStaticConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GoamlController extends Controller
{
    // ── Report list ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenant = app('tenant');
        $config = GoamlStaticConfig::where('tenant_id', $tenant->id)->first();

        $query = GoamlReport::where('tenant_id', $tenant->id)->with('client', 'author');

        if ($request->filled('type'))   $query->where('report_type', $request->type);
        if ($request->filled('client')) $query->where('bullion_client_id', $request->client);
        if ($request->filled('search')) $query->where(function($q) use ($request) {
            $q->where('entity_reference', 'like', '%'.$request->search.'%')
              ->orWhere('client_name', 'like', '%'.$request->search.'%');
        });

        $reports = $query->orderBy('created_at', 'desc')->get();

        $clients = BullionClient::where('tenant_id', $tenant->id)
            ->orderBy('company_name')->get(['id','company_name','full_name','client_type']);

        $stats = [
            'total'  => GoamlReport::where('tenant_id', $tenant->id)->count(),
            'dpmsr'  => GoamlReport::where('tenant_id', $tenant->id)->where('report_type', 'DPMSR')->count(),
            'str'    => GoamlReport::where('tenant_id', $tenant->id)->where('report_type', 'STR')->count(),
            'sar'    => GoamlReport::where('tenant_id', $tenant->id)->where('report_type', 'SAR')->count(),
        ];

        return view('tenant.goaml.index', compact('tenant', 'config', 'reports', 'clients', 'stats'));
    }

    // ── Create form ────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $tenant = app('tenant');
        $config = GoamlStaticConfig::where('tenant_id', $tenant->id)->first();

        if (!$config || !$config->isComplete()) {
            return redirect()->route('tenant.goaml.settings', $tenant->slug)
                ->with('error', 'Please complete your goAML configuration before filing a report.');
        }

        // Pre-load client if passed
        $client = null;
        if ($request->filled('client')) {
            $client = BullionClient::where('tenant_id', $tenant->id)->find($request->client);
        }

        $clients = BullionClient::where('tenant_id', $tenant->id)
            ->orderBy('company_name')->get();

        return view('tenant.goaml.create', compact('tenant', 'config', 'client', 'clients'));
    }

    // ── Generate XML + save record ─────────────────────────────────────────

    public function store(Request $request)
    {
        $tenant = app('tenant');
        $config = GoamlStaticConfig::where('tenant_id', $tenant->id)->firstOrFail();

        $request->validate([
            'report_type'       => 'required|in:DPMSR,STR,SAR',
            'entity_reference'  => 'required|string|max:100',
            'estimated_value'   => 'required|numeric|min:0',
            'disposed_value'    => 'required|numeric|min:0',
            'currency_code'     => 'required|in:AED,USD',
            'size'              => 'required|numeric|min:0',
            'size_uom'          => 'required|string',
            'registration_date' => 'required|date|before_or_equal:today',
            // Client/entity
            'name'                      => 'required|string',
            'commercial_name'           => 'required|string',
            'incorporation_number'      => 'required|string',
            'incorporation_country_code'=> 'required|string|max:3',
            'e_tph_number'              => 'required|string',
            // Director
            'first_name'     => 'required|string',
            'last_name'      => 'required|string',
            'birthdate'      => 'required|date',
            'passport_number'=> 'required|string',
            'passport_country'=> 'required|string|max:3',
            'id_number'      => 'required|string',
            'nationality1'   => 'required|string|max:3',
            'residence'      => 'required|string|max:3',
            'd_tph_number'   => 'required|string',
        ]);

        // Generate XML
        $xml      = new \DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $report   = $xml->createElement('report');
        $xml->appendChild($report);

        $submissionDate = now()->format('Y-m-d\TH:i:s');
        $reportCode     = $request->report_type;

        // Top-level report fields
        foreach ([
            'rentity_id'          => $config->rentity_id,
            'submission_code'     => 'E',
            'report_code'         => $reportCode,
            'entity_reference'    => $request->entity_reference,
            'submission_date'     => $submissionDate,
            'currency_code_local' => $request->currency_code,
        ] as $k => $v) {
            $report->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }

        // Reporting person (MLRO)
        $rp = $xml->createElement('reporting_person');
        $report->appendChild($rp);
        foreach ([
            'gender'       => $config->mlro_gender,
            'first_name'   => $config->mlro_first_name,
            'last_name'    => $config->mlro_last_name,
            'ssn'          => $config->mlro_ssn,
            'id_number'    => $config->mlro_id_number,
            'nationality1' => $config->mlro_nationality,
            'email'        => $config->mlro_email,
            'occupation'   => $config->mlro_occupation,
        ] as $k => $v) {
            $rp->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }

        // Location
        $loc = $xml->createElement('location');
        $report->appendChild($loc);
        foreach ([
            'address_type' => 'BU',
            'address'      => $config->entity_address,
            'city'         => $config->entity_city,
            'country_code' => $config->entity_country_code,
            'state'        => $config->entity_state ?? $config->entity_city,
        ] as $k => $v) {
            $loc->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }

        // Reason + action
        $reason = $reportCode === 'DPMSR' ? 'Sale Above AED 55000' : ($request->reason ?? 'Suspicious transaction');
        $action = 'KYC Documents Collected from Counterparty';
        $report->appendChild($xml->createElement('reason', htmlspecialchars($reason, ENT_QUOTES, 'UTF-8')));
        $report->appendChild($xml->createElement('action', htmlspecialchars($action, ENT_QUOTES, 'UTF-8')));

        // Activity
        $activity       = $xml->createElement('activity');
        $report->appendChild($activity);
        $report_parties = $xml->createElement('report_parties');
        $activity->appendChild($report_parties);
        $report_party   = $xml->createElement('report_party');
        $report_parties->appendChild($report_party);

        // Entity
        $entity = $xml->createElement('entity');
        $report_party->appendChild($entity);
        foreach ([
            'name'                 => $request->name,
            'commercial_name'      => $request->commercial_name,
            'incorporation_number' => $request->incorporation_number,
        ] as $k => $v) {
            $entity->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }

        // Entity phones
        $phones = $xml->createElement('phones');
        $entity->appendChild($phones);
        $phone  = $xml->createElement('phone');
        $phones->appendChild($phone);
        foreach ([
            'tph_contact_type'       => 'BU',
            'tph_communication_type' => 'L',
            'tph_number'             => $request->e_tph_number,
        ] as $k => $v) {
            $phone->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }

        // Incorporation
        foreach ([
            'incorporation_state'        => $request->incorporation_country_code,
            'incorporation_country_code' => $request->incorporation_country_code,
        ] as $k => $v) {
            $entity->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }

        // Director
        $director = $xml->createElement('director_id');
        $entity->appendChild($director);
        $bd = substr($request->birthdate, 0, 10) . 'T00:00:00';
        foreach ([
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'birthdate'       => $bd,
            'passport_number' => $request->passport_number,
            'passport_country'=> $request->passport_country,
            'id_number'       => $request->id_number,
            'nationality1'    => $request->nationality1,
            'residence'       => $request->residence,
        ] as $k => $v) {
            $director->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }

        // Director phones
        $dphones = $xml->createElement('phones');
        $director->appendChild($dphones);
        $dphone  = $xml->createElement('phone');
        $dphones->appendChild($dphone);
        foreach ([
            'tph_contact_type'       => 'BU',
            'tph_communication_type' => 'L',
            'tph_number'             => $request->d_tph_number,
        ] as $k => $v) {
            $dphone->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }
        $director->appendChild($xml->createElement('role', 'ATR'));

        // Party reason + comments
        $report_party->appendChild($xml->createElement('reason', htmlspecialchars($reason, ENT_QUOTES, 'UTF-8')));
        $report_party->appendChild($xml->createElement('comments', htmlspecialchars($request->comments ?? '', ENT_QUOTES, 'UTF-8')));

        // Goods & services
        $goods  = $xml->createElement('goods_services');
        $activity->appendChild($goods);
        $item   = $xml->createElement('item');
        $goods->appendChild($item);
        $regDate = substr($request->registration_date, 0, 10) . 'T00:00:00';
        foreach ([
            'item_type'        => 'GOLD',
            'estimated_value'  => $request->estimated_value,
            'disposed_value'   => $request->disposed_value,
            'currency_code'    => $request->currency_code,
            'size'             => $request->size,
            'size_uom'         => $request->size_uom,
            'registration_date'=> $regDate,
        ] as $k => $v) {
            $item->appendChild($xml->createElement($k, htmlspecialchars($v, ENT_QUOTES, 'UTF-8')));
        }

        // Report indicators
        $indicators = $xml->createElement('report_indicators');
        $report->appendChild($indicators);
        $indicators->appendChild($xml->createElement('indicator', 'DPMSJ'));

        // Save XML
        $clientSlug = preg_replace('/[^a-zA-Z0-9]/', '', substr($request->name, 0, 6)) ?: 'REPT';
        $filename   = strtoupper($reportCode) . '-' . $request->entity_reference . '-' . now()->format('Ymd') . '.xml';
        $path       = "goaml/{$tenant->id}/{$clientSlug}/{$filename}";
        Storage::disk('local')->put($path, $xml->saveXML());

        // Save DB record
        $saved = GoamlReport::create([
            'tenant_id'          => $tenant->id,
            'bullion_client_id'  => $request->bullion_client_id ?: null,
            'report_type'        => $reportCode,
            'entity_reference'   => $request->entity_reference,
            'client_name'        => $request->name,
            'estimated_value'    => $request->estimated_value,
            'disposed_value'     => $request->disposed_value,
            'currency_code'      => $request->currency_code,
            'size'               => $request->size,
            'size_uom'           => $request->size_uom,
            'registration_date'  => $request->registration_date,
            'reason'             => $reason,
            'comments'           => $request->comments,
            'xml_file_path'      => $path,
            'xml_file_name'      => $filename,
            'generated_by'       => auth()->id(),
        ]);

        return redirect()->route('tenant.goaml.download', [$tenant->slug, $saved->id])
            ->with('success', "{$reportCode} report generated — {$filename}");
    }

    // ── Download XML ───────────────────────────────────────────────────────

    public function download(string $slug, GoamlReport $report)
    {
        $tenant = app('tenant');
        abort_if($report->tenant_id !== $tenant->id, 404);

        if (!Storage::disk('local')->exists($report->xml_file_path)) {
            abort(404, 'XML file not found.');
        }

        return Storage::disk('local')->download($report->xml_file_path, $report->xml_file_name);
    }

    // ── Settings (one-time static config) ─────────────────────────────────

    public function settings()
    {
        $tenant = app('tenant');
        $config = GoamlStaticConfig::where('tenant_id', $tenant->id)->first();
        return view('tenant.goaml.settings', compact('tenant', 'config'));
    }

    public function saveSettings(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'rentity_id'         => 'required|string',
            'entity_name'        => 'required|string',
            'entity_address'     => 'required|string',
            'entity_city'        => 'required|string',
            'entity_country_code'=> 'required|string|max:3',
            'mlro_gender'        => 'required|in:M,F',
            'mlro_first_name'    => 'required|string',
            'mlro_last_name'     => 'required|string',
            'mlro_ssn'           => 'required|string',
            'mlro_id_number'     => 'required|string',
            'mlro_nationality'   => 'required|string|max:3',
            'mlro_email'         => 'required|email',
            'mlro_occupation'    => 'required|string',
        ]);

        GoamlStaticConfig::updateOrCreate(
            ['tenant_id' => $tenant->id],
            array_merge($request->except(['_token']), ['tenant_id' => $tenant->id])
        );

        return redirect()->route('tenant.goaml', $tenant->slug)
            ->with('success', 'goAML configuration saved.');
    }

    // ── Delete report ──────────────────────────────────────────────────────

    public function destroy(string $slug, GoamlReport $report)
    {
        $tenant = app('tenant');
        abort_if($report->tenant_id !== $tenant->id, 404);

        if ($report->xml_file_path) {
            Storage::disk('local')->delete($report->xml_file_path);
        }
        $report->delete();

        return back()->with('success', 'Report deleted.');
    }
}
