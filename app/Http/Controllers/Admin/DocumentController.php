<?php
// app/Http/Controllers/Admin/DocumentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmSla;
use App\Models\CrmQuotation;
use App\Models\QuotationTemplate;
use App\Models\CrmClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    // ── Generate SLA document ──────────────────────────────────────────────

    public function generateSla(CrmSla $sla)
    {
        $sla->load(['client', 'client.shareholders']);
        $client = $sla->client;

        // Build signatory from first shareholder or contact person
        $signatory      = $client?->shareholders?->first()?->shareholder_name ?? $client?->contact_person ?? '';
        $signatoryTitle = 'Authorised Signatory';

        $data = [
            'client_name'            => $client?->company_name ?? 'Client',
            'client_signatory'       => $signatory,
            'client_signatory_title' => $signatoryTitle,
            'service_type'           => $sla->name,
            'reference'              => $sla->sla_reference,
            'start_date'             => $sla->start_date?->format('d F Y') ?? now()->format('d F Y'),
            'end_date'               => $sla->end_date?->format('d F Y') ?? '',
            'scope_of_work'          => $sla->scope_of_work,
            'client_obligations'     => $sla->client_obligations,
            'deliverables'           => $sla->deliverables,
            'fee'                    => $sla->fee,
            'fee_frequency'          => $sla->fee_frequency,
            'payment_terms'          => $sla->payment_terms,
            'termination_clause'     => $sla->termination_clause,
            'governing_law'          => $sla->governing_law,
        ];

        return $this->runGenerator('generate-sla', $data,
            'SLA-' . $sla->sla_reference . '.docx');
    }

    // ── Generate Quotation document (from CRM quotation record) ───────────

    public function generateQuotation(CrmQuotation $quotation)
    {
        $quotation->load('client');
        $client = $quotation->client;

        $data = [
            'reference'      => $quotation->quotation_reference,
            'subject'        => $quotation->subject,
            'client_name'    => $client?->company_name ?? 'Client',
            'client_address' => $client?->address ?? '',
            'client_email'   => $client?->email ?? '',
            'issued_date'    => $quotation->issued_date?->format('d M Y') ?? now()->format('d M Y'),
            'valid_until'    => $quotation->valid_until?->format('d M Y') ?? '',
            'line_items'     => $quotation->line_items ?? [],
            'terms'          => $quotation->terms,
        ];

        return $this->runGenerator('generate-quotation', $data,
            'QT-' . $quotation->quotation_reference . '.docx');
    }

    // ── Standalone quotation — list ────────────────────────────────────────

    public function quotationIndex()
    {
        $quotations  = CrmQuotation::with('client')->latest()->paginate(20);
        $templates   = QuotationTemplate::where('is_active', true)->orderBy('name')->get();
        $clients     = CrmClient::orderBy('company_name')->get(['id', 'company_name', 'email', 'address']);
        return view('admin.quotations.index', compact('quotations', 'templates', 'clients'));
    }

    // ── Standalone quotation — create form ────────────────────────────────

    public function quotationCreate()
    {
        $templates = QuotationTemplate::where('is_active', true)->orderBy('name')->get();
        $clients   = CrmClient::orderBy('company_name')->get(['id', 'company_name', 'email', 'address']);
        return view('admin.quotations.create', compact('templates', 'clients'));
    }

    // ── Standalone quotation — store ──────────────────────────────────────

    public function quotationStore(Request $request)
    {
        $request->validate(['subject' => 'required', 'client_type' => 'required']);

        // Resolve client info
        if ($request->client_type === 'existing' && $request->crm_client_id) {
            $client    = CrmClient::findOrFail($request->crm_client_id);
            $clientId  = $client->id;
        } else {
            $clientId = null;
        }

        // Build line items
        $items = collect($request->input('items', []))
            ->filter(fn($i) => !empty($i['description']))
            ->map(fn($i) => [
                'description' => $i['description'],
                'qty'         => (float)($i['qty'] ?? 1),
                'unit_price'  => (float)($i['unit_price'] ?? 0),
            ])->values()->toArray();

        $subtotal = collect($items)->sum(fn($i) => $i['qty'] * $i['unit_price']);
        $vat      = round($subtotal * 0.05, 2);

        $qt = CrmQuotation::create([
            'crm_client_id'         => $clientId,
            'quotation_template_id' => $request->quotation_template_id ?: null,
            'quotation_reference'   => CrmQuotation::generateReference(),
            'subject'               => $request->subject,
            'line_items'            => $items,
            'subtotal'              => $subtotal,
            'vat_amount'            => $vat,
            'total_amount'          => $subtotal + $vat,
            'terms'                 => $request->terms,
            'issued_date'           => now()->toDateString(),
            'valid_until'           => now()->addDays($request->validity_days ?? 30)->toDateString(),
            'status'                => 'draft',
            'created_by'            => auth()->id(),
        ]);

        // Store standalone client name/email for non-CRM quotations
        if (!$clientId) {
            $qt->update(['subject' => $request->subject]);
            // Store recipient in quotation's terms field temporarily as metadata
            session(['qt_recipient_' . $qt->id => [
                'name'    => $request->recipient_name,
                'email'   => $request->recipient_email,
                'address' => $request->recipient_address,
            ]]);
        }

        return redirect()->route('quotations.show', $qt->id)
            ->with('success', 'Quotation created — ' . $qt->quotation_reference);
    }

    // ── Standalone quotation — show ────────────────────────────────────────

    public function quotationShow(CrmQuotation $quotation)
    {
        $quotation->load(['client', 'creator']);
        return view('admin.quotations.show', compact('quotation'));
    }

    // ── Generate standalone quotation document ─────────────────────────────

    public function generateStandaloneQuotation(Request $request, CrmQuotation $quotation)
    {
        $quotation->load('client');

        $data = [
            'reference'      => $quotation->quotation_reference,
            'subject'        => $quotation->subject,
            'client_name'    => $request->recipient_name ?? $quotation->client?->company_name ?? 'Client',
            'client_address' => $request->recipient_address ?? $quotation->client?->address ?? '',
            'client_email'   => $request->recipient_email ?? $quotation->client?->email ?? '',
            'issued_date'    => $quotation->issued_date?->format('d M Y') ?? now()->format('d M Y'),
            'valid_until'    => $quotation->valid_until?->format('d M Y') ?? '',
            'line_items'     => $quotation->line_items ?? [],
            'terms'          => $quotation->terms,
        ];

        return $this->runGenerator('generate-quotation', $data,
            'QT-' . $quotation->quotation_reference . '.docx');
    }

    // ── Core generator helper ──────────────────────────────────────────────

    private function runGenerator(string $script, array $data, string $filename)
    {
        $tmpDir    = storage_path('app/private/generated');
        $dataFile  = $tmpDir . '/' . Str::uuid() . '.json';
        $outputFile= $tmpDir . '/' . Str::uuid() . '.docx';

        if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

        file_put_contents($dataFile, json_encode($data));

		$scriptPath = base_path('scripts/' . $script . '.cjs');
        $nodeCmd    = 'node ' . escapeshellarg($scriptPath)
                    . ' ' . escapeshellarg($dataFile)
                    . ' ' . escapeshellarg($outputFile)
                    . ' 2>&1';

        $output = shell_exec($nodeCmd);

        // Clean up data file
        @unlink($dataFile);

        if (!file_exists($outputFile) || !str_starts_with(trim($output ?? ''), 'OK:')) {
            return back()->with('error', 'Document generation failed. ' . $output);
        }

        $content = file_get_contents($outputFile);
        @unlink($outputFile);

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => strlen($content),
        ]);
    }
}
