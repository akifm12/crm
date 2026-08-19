<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\StandardInvoice;
use App\Services\Accounting\StandardInvoicingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StandardInvoiceController extends Controller
{
    public function index(string $slug, string $moduleType)
    {
        $tenant   = app('tenant');
        $invoices = StandardInvoice::where('tenant_id', $tenant->id)
            ->where('module_type', $moduleType)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(40);

        return view('tenant.accounting.standard_invoices.index', compact('tenant', 'invoices', 'moduleType'));
    }

    public function create(string $slug, string $moduleType)
    {
        $tenant = app('tenant');
        return view('tenant.accounting.standard_invoices.create', compact('tenant', 'moduleType'));
    }

    public function store(Request $request, string $slug, string $moduleType, StandardInvoicingService $svc)
    {
        $tenant = app('tenant');
        $request->validate($this->lineRules());

        try {
            $invoice = $svc->createDraft($tenant, $moduleType, $request->only(
                'client_name', 'client_vat_number', 'reference', 'invoice_date', 'due_date', 'notes', 'lines'
            ));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('tenant.accounting.standard-invoices.show', [$slug, $moduleType, $invoice->id])
            ->with('success', 'Invoice saved as draft.');
    }

    public function show(string $slug, string $moduleType, StandardInvoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $invoice->load('lines', 'payments', 'journalEntry');

        return view('tenant.accounting.standard_invoices.show', compact('tenant', 'invoice', 'moduleType'));
    }

    public function edit(string $slug, string $moduleType, StandardInvoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        abort_if($invoice->status !== 'draft', 403, 'Only draft invoices can be edited.');
        $invoice->load('lines');

        return view('tenant.accounting.standard_invoices.edit', compact('tenant', 'invoice', 'moduleType'));
    }

    public function update(Request $request, string $slug, string $moduleType, StandardInvoice $invoice, StandardInvoicingService $svc)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $request->validate($this->lineRules());

        try {
            $svc->updateDraft($invoice, $request->only(
                'client_name', 'client_vat_number', 'reference', 'invoice_date', 'due_date', 'notes', 'lines'
            ));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('tenant.accounting.standard-invoices.show', [$slug, $moduleType, $invoice->id])
            ->with('success', 'Invoice updated.');
    }

    public function destroy(string $slug, string $moduleType, StandardInvoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        abort_if($invoice->status !== 'draft', 403, 'Only draft invoices can be deleted.');

        $invoice->lines()->delete();
        $invoice->delete();

        return redirect()->route('tenant.accounting.standard-invoices.index', [$slug, $moduleType])
            ->with('success', 'Draft deleted.');
    }

    public function post(string $slug, string $moduleType, StandardInvoice $invoice, StandardInvoicingService $svc)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        try {
            $svc->post($invoice);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.accounting.standard-invoices.show', [$slug, $moduleType, $invoice->id])
            ->with('success', 'Invoice posted.');
    }

    public function void(Request $request, string $slug, string $moduleType, StandardInvoice $invoice, StandardInvoicingService $svc)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        try {
            $svc->void($invoice, $request->input('reason'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.accounting.standard-invoices.show', [$slug, $moduleType, $invoice->id])
            ->with('success', 'Invoice voided.');
    }

    public function storePayment(Request $request, string $slug, string $moduleType, StandardInvoice $invoice, StandardInvoicingService $svc)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        $request->validate([
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'nullable|string',
            'account'      => 'nullable|string',
            'reference'    => 'nullable|string|max:255',
        ]);

        try {
            $svc->recordPayment($invoice, $request->only('payment_date', 'amount', 'method', 'account', 'reference'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment recorded.');
    }

    public function pdf(string $slug, string $moduleType, StandardInvoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $invoice->load('lines');

        $pdf = Pdf::loadView('tenant.accounting.standard_invoices.pdf', compact('tenant', 'invoice', 'moduleType'));

        return $pdf->stream("{$invoice->invoice_number}.pdf");
    }

    private function lineRules(): array
    {
        return [
            'client_name'         => 'required|string|max:255',
            'client_vat_number'   => 'nullable|string|max:50',
            'reference'           => 'nullable|string|max:255',
            'invoice_date'        => 'required|date',
            'due_date'            => 'nullable|date|after_or_equal:invoice_date',
            'notes'               => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.quantity'    => 'required|numeric|min:0.0001',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'lines.*.vat_treatment' => ['required', Rule::in(StandardInvoice::VAT_TREATMENTS)],
            'lines.*.vat_rate'    => 'required|numeric|min:0|max:100',
        ];
    }
}
