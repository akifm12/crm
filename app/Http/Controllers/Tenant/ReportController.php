<?php
// app/Http/Controllers/Tenant/ReportController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    // ── Screening PDF ──────────────────────────────────────────────────────

    public function screeningPdf(string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories']);

        return view('tenant.reports.screening_pdf', compact('tenant', 'client'));
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