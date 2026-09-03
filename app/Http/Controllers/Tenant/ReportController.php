<?php
// app/Http/Controllers/Tenant/ReportController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\ScreeningLog;
use Mpdf\Mpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    // ── Tenant logo, resolved to an absolute filesystem path for generate-kyc.cjs
    //    to read directly (same file the portal sidebar displays via logo_url).
    //    Only raster types are embeddable in the docx without a fallback image —
    //    an SVG logo just won't appear in the generated pack. ────────────────────
    private function logoDataFor($tenant): array
    {
        if (!$tenant->logo_url || !Storage::disk('public')->exists($tenant->logo_url)) {
            return ['logo_path' => null, 'logo_ext' => null];
        }

        $ext = strtolower(pathinfo($tenant->logo_url, PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
            return ['logo_path' => null, 'logo_ext' => null];
        }

        return [
            'logo_path' => Storage::disk('public')->path($tenant->logo_url),
            'logo_ext'  => $ext,
        ];
    }

    // ── Build KYC data array (shared between Word and PDF) ────────────────────

    private function buildKycData(BullionClient $client, $tenant): array
    {
        $isCorp = $client->client_type !== 'individual';
        $sig    = $client->signatories->first();

        $sofLabels = [
            'salary' => 'Salary / Employment Income', 'business_profits' => 'Business Profits',
            'investments' => 'Investment Returns', 'inheritance' => 'Inheritance / Gift',
            'loan' => 'Bank Loan / Financing', 'savings' => 'Personal Savings', 'other' => 'Other',
        ];
        $purposeLabels = [
            'investment' => 'Investment', 'trading' => 'Bullion Trading',
            'personal_use' => 'Personal Use', 'gift' => 'Gift',
            'business' => 'Business Operations', 'other' => 'Other',
        ];

        $sofStr = function ($val) use ($sofLabels) {
            if (!$val) return null;
            return implode(', ', array_map(fn($v) => $sofLabels[$v] ?? ucwords(str_replace('_', ' ', $v)), (array) $val));
        };

        return [
            'ref'       => 'KYC-' . str_pad($client->id, 5, '0', STR_PAD_LEFT),
            'generated' => now()->format('d M Y, H:i'),
            ...$this->logoDataFor($tenant),
            'sector'         => $tenant->business_type ?? 'gold',
            'tenant_name'    => $tenant->name,
            'tenant_address' => $tenant->address ?? '',
            'tenant_email'   => $tenant->contact_email ?? '',
            'dnfbp_reg_no'   => $tenant->dnfbp_reg_no ?? '',
            'is_corp'        => $isCorp,
            'client_name'    => $client->displayName(),
            'client_type'    => $client->client_type,
            'client_type_label' => ucwords(str_replace('_', ' ', $client->client_type)),
            'status'         => ucfirst($client->status ?? ''),
            'cdd_type'       => $client->cdd_type ?? 'standard',
            'risk_rating'    => $client->risk_rating ?? 'unrated',
            'next_review_date'  => $client->next_review_date?->format('d M Y'),
            'risk_assessed_at'  => $client->risk_assessed_at?->format('d M Y'),
            'risk_assessed_by'  => $client->risk_assessed_by ?? '',
            'risk_notes'        => $client->risk_notes ?? '',
            'company_name'           => $client->company_name ?? '',
            'legal_form'             => $client->legal_form ?? '',
            'country_of_incorporation' => $client->country_of_incorporation ?? '',
            'trade_license_no'       => $client->trade_license_no ?? '',
            'trade_license_validity' => ($client->trade_license_issue?->format('d M Y') ?? '—') . ' to ' . ($client->trade_license_expiry?->format('d M Y') ?? '—'),
            'trn_number'             => $client->trn_number ?? '',
            'ejari_number'           => $client->ejari_number ?? '',
            'ejari_expiry'           => $client->ejari_expiry?->format('d M Y'),
            'business_activity'      => $client->business_activity ?? '',
            'registered_address'     => $client->registered_address ?? '',
            'website'                => $client->website ?? '',
            'full_name'      => $client->full_name ?? '',
            'name_arabic'    => $client->name_arabic ?? '',
            'nationality'    => $client->nationality ?? '',
            'dob'            => $client->dob?->format('d M Y'),
            'passport_number'=> $client->passport_number ?? '',
            'passport_expiry'=> $client->passport_expiry?->format('d M Y'),
            'eid_number'     => $client->eid_number ?? '',
            'eid_expiry'     => $client->eid_expiry?->format('d M Y'),
            'occupation'     => $client->occupation ?? '',
            'employer_name'  => $client->employer_name ?? '',
            'pep_status'     => (bool) $client->pep_status,
            'email' => $client->email ?? '',
            'phone' => $client->phone ?? '',
            'source_of_funds_label'         => $sofStr($client->source_of_funds),
            'source_of_wealth_label'        => $sofStr($client->source_of_wealth),
            'purpose_of_relationship_label' => $purposeLabels[$client->purpose_of_relationship ?? ''] ?? ucwords(str_replace('_', ' ', $client->purpose_of_relationship ?? '')),
            'expected_monthly_volume'       => $client->expected_monthly_volume,
            'expected_monthly_frequency'    => $client->expected_monthly_frequency,
            'countries_involved'            => $client->countries_involved ? implode(', ', $client->countries_involved) : '',
            'screening_status'    => $client->screening_status ?? '',
            'screening_date'      => $client->screening_date?->format('d M Y, H:i'),
            'screening_reference' => $client->screening_reference ?? '',
            'signatories'  => $client->signatories->map(fn($s) => [
                'full_name'       => $s->full_name ?? '',
                'position'        => $s->position ?? '',
                'nationality'     => $s->nationality ?? '',
                'dob'             => $s->dob?->format('d M Y'),
                'passport_number' => $s->passport_number ?? '',
                'passport_expiry' => $s->passport_expiry?->format('d M Y'),
                'eid_number'      => $s->eid_number ?? '',
            ])->toArray(),
            'shareholders' => $client->shareholders->map(fn($s) => [
                'name'                 => $s->name ?? '',
                'shareholder_type'     => ucfirst($s->shareholder_type ?? ''),
                'nationality'          => $s->nationality ?? '',
                'ownership_percentage' => $s->ownership_percentage,
                'is_ubo'               => (bool) $s->is_ubo,
                'passport_number'      => $s->passport_number ?? '',
                'eid_number'           => $s->eid_number ?? '',
                'dob'                  => $s->dob?->format('d M Y'),
            ])->toArray(),
            'signatory_name'  => $sig?->full_name ?? $client->displayName(),
            'signatory_title' => $sig?->position ?? 'Client / Authorized Signatory',
            'mlro_name'       => $tenant->mlro_name ?? '',
            'questionnaire'   => $client->questionnaire ?? null,
        ];
    }

    // ── Build a blank/generic KYC template — no real client data, every fillable
    //    field rendered as a yellow-highlighted "[FIELD LABEL]" placeholder — for
    //    sending to a prospective client to fill out by hand. Existing filled KYC
    //    exports (buildKycData above) are untouched by this. ───────────────────────

    private function buildKycBlankData($tenant, string $type = 'corporate'): array
    {
        $isCorp = $type !== 'individual';

        $blankSignatory = fn () => [
            'full_name' => '[FULL NAME]', 'position' => '[POSITION]', 'nationality' => '[NATIONALITY]',
            'dob' => '[DATE OF BIRTH]', 'passport_number' => '[PASSPORT NO.]',
            'passport_expiry' => '[EXPIRY]', 'eid_number' => '[EMIRATES ID]',
        ];
        $blankShareholder = fn () => [
            'name' => '[NAME]', 'shareholder_type' => '[INDIVIDUAL / CORPORATE]', 'nationality' => '[NATIONALITY]',
            'ownership_percentage' => null, 'is_ubo' => false,
            'passport_number' => '[PASSPORT / ID NO.]', 'eid_number' => '', 'dob' => '[DATE OF BIRTH]',
        ];

        return [
            'blank'     => true,
            'ref'       => 'KYC-TEMPLATE',
            'generated' => now()->format('d M Y'),
            ...$this->logoDataFor($tenant),
            'sector'         => $tenant->business_type ?? 'gold',
            'tenant_name'    => $tenant->name,
            'tenant_address' => $tenant->address ?? '',
            'tenant_email'   => $tenant->contact_email ?? '',
            'dnfbp_reg_no'   => $tenant->dnfbp_reg_no ?? '',
            'is_corp'        => $isCorp,
            'client_name'    => $isCorp ? '[COMPANY NAME]' : '[FULL NAME]',
            // Corporate fields
            'company_name'             => '[COMPANY NAME]',
            'legal_form'               => 'x', 'country_of_incorporation' => 'x',
            'trade_license_no'         => 'x', 'trade_license_validity'   => 'x',
            'trn_number'               => 'x', 'ejari_number'             => 'x', 'ejari_expiry' => 'x',
            'business_activity'        => 'x', 'registered_address'       => 'x',
            'website'                  => 'x',
            // Individual fields
            'full_name'      => '[FULL NAME]', 'name_arabic' => 'x', 'nationality' => 'x', 'dob' => 'x',
            'passport_number'=> 'x', 'passport_expiry' => 'x', 'eid_number' => 'x', 'eid_expiry' => 'x',
            'occupation'     => 'x', 'employer_name' => 'x', 'pep_status' => false,
            // Shared contact
            'email' => 'x', 'phone' => 'x',
            // AML / CDD self-declared fields
            'source_of_funds_label'         => 'x',
            'source_of_wealth_label'        => 'x',
            'purpose_of_relationship_label' => 'x',
            'expected_monthly_volume'       => 1,
            'expected_monthly_frequency'    => 'x',
            'countries_involved'            => 'x',
            // Structure — a few blank rows for the client to write into by hand
            'signatories'  => $isCorp ? [$blankSignatory(), $blankSignatory(), $blankSignatory()] : [],
            'shareholders' => $isCorp ? [$blankShareholder(), $blankShareholder(), $blankShareholder()] : [],
            'signatory_name'  => '',
            'signatory_title' => 'Client / Authorized Signatory',
            'mlro_name'       => '',
        ];
    }

    public function kycBlankDocx(string $slug, Request $request)
    {
        $tenant = app('tenant');
        $type   = $request->input('type', 'corporate') === 'individual' ? 'individual' : 'corporate';

        $baseName = 'KYC-TEMPLATE-' . Str::upper($type) . '-' . uniqid();
        $filename = 'KYC-Blank-Template-' . Str::ucfirst($type) . '.docx';

        try {
            $outPath = $this->generateKycDocx($this->buildKycBlankData($tenant, $type), $baseName);
        } catch (\RuntimeException $e) {
            Log::error('KYC blank docx failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate blank KYC template. ' . $e->getMessage());
        }

        return response()->download($outPath, $filename)->deleteFileAfterSend(true);
    }

    // ── Generate the .docx file and return its path ───────────────────────────

    private function generateKycDocx(array $data, string $baseName): string
    {
        $tmpJson = storage_path('app/tmp/kyc_' . uniqid() . '.json');
        $outPath = storage_path('app/tmp/' . $baseName . '.docx');

        if (!file_exists(dirname($tmpJson))) mkdir(dirname($tmpJson), 0755, true);

        file_put_contents($tmpJson, json_encode($data, JSON_UNESCAPED_UNICODE));

        $cmd    = 'node ' . escapeshellarg(base_path('scripts/generate-kyc.cjs')) . ' ' . escapeshellarg($tmpJson) . ' ' . escapeshellarg($outPath) . ' 2>&1';
        $output = shell_exec($cmd);
        @unlink($tmpJson);

        if (!file_exists($outPath)) {
            throw new \RuntimeException('KYC docx generation failed: ' . $output);
        }

        return $outPath;
    }

    // ── KYC PDF (converted from Word via LibreOffice) ─────────────────────────

    public function kycPdf(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos']);

        $baseName = 'KYC-' . Str::upper(Str::slug($client->displayName())) . '-' . now()->format('Ymd') . '-' . uniqid();
        $filename = 'KYC-' . Str::upper(Str::slug($client->displayName())) . '-' . now()->format('Ymd') . '.pdf';

        try {
            $docxPath = $this->generateKycDocx($this->buildKycData($client, $tenant), $baseName);
        } catch (\RuntimeException $e) {
            Log::error('KYC PDF: docx step failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate KYC document. ' . $e->getMessage());
        }

        $tmpDir   = storage_path('app/tmp');
        $soffice  = env('SOFFICE_PATH', 'soffice');
        $cmd      = 'HOME=/tmp ' . escapeshellarg($soffice) . ' --headless --convert-to pdf --outdir ' . escapeshellarg($tmpDir) . ' ' . escapeshellarg($docxPath) . ' 2>&1';
        $output   = shell_exec($cmd);
        @unlink($docxPath);

        // soffice names the output after the input file with .pdf extension
        $pdfPath = $tmpDir . '/' . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        if (!file_exists($pdfPath)) {
            Log::error('KYC PDF: soffice conversion failed', ['output' => $output]);
            return back()->with('error', 'PDF conversion failed. Ensure LibreOffice is installed on the server.');
        }

        return response()->download($pdfPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }

    // ── KYC Word document ─────────────────────────────────────────────────────

    public function kycDocx(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos']);

        $baseName = 'KYC-' . Str::upper(Str::slug($client->displayName())) . '-' . now()->format('Ymd') . '-' . uniqid();
        $filename = 'KYC-' . Str::upper(Str::slug($client->displayName())) . '-' . now()->format('Ymd') . '.docx';

        try {
            $outPath = $this->generateKycDocx($this->buildKycData($client, $tenant), $baseName);
        } catch (\RuntimeException $e) {
            Log::error('KYC docx failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate Word document. ' . $e->getMessage());
        }

        return response()->download($outPath, $filename)->deleteFileAfterSend(true);
    }

    // ── Screening PDF ──────────────────────────────────────────────────────

    public function screeningPdf(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders']);

        $allResults = $client->screening_result['all_results'] ?? null;

        // Fallback — if old format, wrap main result only
        if (!$allResults && $client->screening_result) {
            $allResults = [[
                'name'    => $client->displayName(),
                'role'    => $client->client_type !== 'individual' ? 'Company' : 'Individual',
                'summary' => $client->screening_result,
            ]];
        }

        return view('tenant.reports.screening_pdf', compact('tenant', 'client', 'allResults'));
    }

    // ── Standalone screening log PDF (no client link) ─────────────────────

    public function screeningLogPdf(string $slug, ScreeningLog $log)
    {
        $tenant = app('tenant');
        abort_if($log->tenant_id !== $tenant->id, 404);

        // If the log is tied to a client, use the richer client-based PDF
        if ($log->bullion_client_id) {
            return redirect()->route('tenant.clients.screening.pdf', [$slug, $log->bullion_client_id]);
        }

        $allResults = [[
            'name'    => $log->query,
            'role'    => $log->entity_type === 'entity' ? 'Company' : 'Individual',
            'summary' => $log->result ?? [],
        ]];

        return view('tenant.reports.screening_log_pdf', compact('tenant', 'log', 'allResults'));
    }

    // ── Combined declaration Word doc ──────────────────────────────────────

    public function combinedDeclaration(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos']);

        $sig = $client->signatories->first();

        $data = json_encode([
            'client_name'     => $client->client_type !== 'individual' ? $client->company_name : $client->full_name,
            'client_type'     => $client->client_type,
            'sector'          => $tenant->business_type ?? 'gold',
            'trade_license'   => $client->trade_license_no ?? $client->passport_number ?? '',
            'country'         => $client->country_of_incorporation ?? $client->nationality ?? '',
			'director_nationality' => $sig?->nationality ?? $client->nationality ?? '',
            'signatory_name'  => $sig?->full_name ?? $client->displayName(),
            'signatory_title' => $sig?->position ?? 'Authorised Signatory',
            'mlro_name'       => $tenant->mlro_name ?? '',
            'entity_name'     => $tenant->name,
            'entity_address'  => $tenant->address ?? '',
            'date'            => now()->format('d F Y'),
            'ubos'            => $client->ubos->map(fn($u) => [
                'name'       => $u->full_name,
                'nationality'=> $u->nationality,
                'ownership'  => $u->ownership_percentage,
            ])->toArray(),
        ], JSON_UNESCAPED_UNICODE);

        $filename   = 'DECLARATION-' . Str::upper(Str::slug($client->displayName())) . '.docx';
        $outPath    = storage_path("app/tmp/{$filename}");
        $scriptPath = base_path('scripts/generate-combined-declaration-universal.cjs');

        if (!file_exists(dirname($outPath))) {
            mkdir(dirname($outPath), 0755, true);
        }

        $tmpData = storage_path('app/tmp/decl_' . uniqid() . '.json');
        file_put_contents($tmpData, $data);

        $cmd    = "node {$scriptPath} " . escapeshellarg($tmpData) . " " . escapeshellarg($outPath) . " 2>&1";
        $output = shell_exec($cmd);

        @unlink($tmpData);

        if (!file_exists($outPath)) {
            \Log::error("Combined declaration failed: {$output}");
            return back()->with('error', 'Failed to generate declaration. ' . $output);
        }

        return response()->download($outPath, $filename)->deleteFileAfterSend(true);
    }

    public function declaration(string $slug, BullionClient $client, string $type)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos']);

        // All possible declaration types across all sectors
        $declarations = [
            'pep'                 => 'Politically Exposed Person (PEP) Declaration',
            'supply_chain'        => 'Gold Supply Chain Declaration',
            'cahra'               => 'Conflict-Affected & High-Risk Areas (CAHRA) Declaration',
            'source_of_funds'     => 'Source of Funds & Source of Wealth Declaration',
            'sanctions'           => 'Sanctions Compliance Declaration',
            'ubo'                 => 'Ultimate Beneficial Ownership (UBO) Declaration',
            'property'            => 'Real Estate Transaction Declaration',
            'beneficial_ownership'=> 'Beneficial Ownership Structure Declaration',
            'client_funds'        => 'Client Funds Handling Declaration',
        ];

        abort_if(!array_key_exists($type, $declarations), 404);

        $sig = $client->signatories->first();

        $data = json_encode([
            'type'            => $type,
            'single_section'  => true,  // tells universal script to render only this section
            'sector'          => $tenant->business_type ?? 'gold',
            'client_name'     => $client->client_type !== 'individual' ? $client->company_name : $client->full_name,
            'client_type'     => $client->client_type,
            'trade_license'   => $client->trade_license_no ?? $client->passport_number ?? '',
            'country'         => $client->country_of_incorporation ?? $client->nationality ?? '',
            'signatory_name'  => $sig?->full_name ?? $client->displayName(),
            'signatory_title' => $sig?->position ?? 'Authorised Signatory',
            'mlro_name'       => $tenant->mlro_name ?? '',
            'entity_name'     => $tenant->name,
            'entity_address'  => $tenant->address ?? '',
            'date'            => now()->format('d F Y'),
            'ubos'            => $client->ubos->map(fn($u) => [
                'name'       => $u->full_name,
                'nationality'=> $u->nationality,
                'ownership'  => $u->ownership_percentage,
            ])->toArray(),
        ], JSON_UNESCAPED_UNICODE);

        $filename   = strtoupper($type) . '-DECL-' . Str::upper(Str::slug($client->displayName())) . '.docx';
        $outPath    = storage_path("app/tmp/{$filename}");
        $scriptPath = base_path('scripts/generate-combined-declaration-universal.cjs');

        if (!file_exists(dirname($outPath))) {
            mkdir(dirname($outPath), 0755, true);
        }

        $tmpData = storage_path('app/tmp/decl_' . uniqid() . '.json');
        file_put_contents($tmpData, $data);

        $cmd    = "node {$scriptPath} " . escapeshellarg($tmpData) . " " . escapeshellarg($outPath) . " 2>&1";
        $output = shell_exec($cmd);

        @unlink($tmpData);

        if (!file_exists($outPath)) {
            \Log::error("Declaration failed: {$output}");
            return back()->with('error', 'Failed to generate declaration. ' . $output);
        }

        return response()->download($outPath, $filename)->deleteFileAfterSend(true);
    }

}
