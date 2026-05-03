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

    // ── Declaration Word docs ──────────────────────────────────────────────

    public function declaration(string $slug, BullionClient $client, string $type)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);
        $client->load(['signatories', 'shareholders', 'ubos']);

        $declarations = [
            'pep'            => 'Declaration 1 — Politically Exposed Person',
            'supply_chain'   => 'Declaration 2 — Gold Supply Chain',
            'cahra'          => 'Declaration 3 — CAHRA / Conflict Minerals',
            'source_of_funds'=> 'Declaration 4 — Source of Funds & Wealth',
            'sanctions'      => 'Declaration 5 — Sanctions Compliance',
            'ubo'            => 'Declaration 6 — Ultimate Beneficial Ownership',
        ];

        abort_if(!array_key_exists($type, $declarations), 404);

        $sig = $client->signatories->first();
        $signatoryName = $sig?->full_name ?? $client->displayName();

        $data = json_encode([
            'type'            => $type,
            'title'           => $declarations[$type],
            'client_name'     => $client->client_type !== 'individual' ? $client->company_name : $client->full_name,
            'client_type'     => $client->client_type,
            'trade_license'   => $client->trade_license_no ?? '',
            'country'         => $client->country_of_incorporation ?? $client->nationality ?? '',
            'signatory_name'  => $signatoryName,
            'signatory_title' => $sig?->position ?? 'Authorised Signatory',
            'mlro_name'       => $tenant->mlro_name ?? '',
            'mlro_title'      => 'Money Laundering Reporting Officer (MLRO)',
            'entity_name'     => $tenant->name,
            'entity_address'  => $tenant->address ?? '',
            'entity_email'    => $tenant->contact_email ?? '',
            'date'            => now()->format('d F Y'),
            'shareholders'    => $client->shareholders->map(fn($s) => [
                'name'       => $s->name,
                'nationality'=> $s->nationality,
                'ownership'  => $s->ownership_percentage,
            ])->toArray(),
            'ubos' => $client->ubos->map(fn($u) => [
                'name'       => $u->full_name,
                'nationality'=> $u->nationality,
                'ownership'  => $u->ownership_percentage,
            ])->toArray(),
        ], JSON_UNESCAPED_UNICODE);

        $filename  = strtoupper($type) . '-DECL-' . Str::upper(Str::slug($client->displayName())) . '.docx';
        $outPath   = storage_path("app/tmp/{$filename}");
        $scriptPath = base_path('scripts/generate-declaration.cjs');

        if (!file_exists(dirname($outPath))) {
            mkdir(dirname($outPath), 0755, true);
        }

        // Pass data via temp file to avoid shell escaping issues
        $tmpData = storage_path('app/tmp/decl_data_' . uniqid() . '.json');
        file_put_contents($tmpData, $data);

        $cmd    = "node {$scriptPath} " . escapeshellarg($tmpData) . " " . escapeshellarg($outPath) . " 2>&1";
        $output = shell_exec($cmd);

        @unlink($tmpData);

        if (!file_exists($outPath)) {
            \Log::error("Declaration generation failed: {$output}");
            return back()->with('error', 'Failed to generate declaration document. ' . $output);
        }

        return response()->download($outPath, $filename)->deleteFileAfterSend(true);
    }
}
