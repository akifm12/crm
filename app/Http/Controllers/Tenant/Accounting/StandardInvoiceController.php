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
    private function moduleType(): string
    {
        $name = request()->route()?->getName() ?? '';
        return str_contains($name, 'tenant.accounting.re.') ? 'real_estate' : 'general';
    }

    public function index(string $slug)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        $invoices   = StandardInvoice::where('tenant_id', $tenant->id)
            ->where('module_type', $moduleType)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(40);

        return view('tenant.accounting.standard_invoices.index', compact('tenant', 'invoices', 'moduleType', 'slug'));
    }

    public function create(string $slug)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        return view('tenant.accounting.standard_invoices.create', compact('tenant', 'moduleType', 'slug'));
    }

    public function store(Request $request, string $slug, StandardInvoicingService $svc)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        $request->validate($this->lineRules());

        try {
            $invoice = $svc->createDraft($tenant, $moduleType, $request->only(
                'client_name', 'client_vat_number', 'reference', 'invoice_date', 'due_date', 'notes', 'lines'
            ));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route($this->showRouteName($moduleType), [$slug, $invoice->id])
            ->with('success', 'Invoice saved as draft.');
    }

    public function show(string $slug, StandardInvoice $invoice)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $invoice->load('lines', 'payments', 'journalEntry');

        return view('tenant.accounting.standard_invoices.show', compact('tenant', 'invoice', 'moduleType', 'slug'));
    }

    public function edit(string $slug, StandardInvoice $invoice)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        abort_if($invoice->status !== 'draft', 403, 'Only draft invoices can be edited.');
        $invoice->load('lines');

        return view('tenant.accounting.standard_invoices.edit', compact('tenant', 'invoice', 'moduleType', 'slug'));
    }

    public function update(Request $request, string $slug, StandardInvoice $invoice, StandardInvoicingService $svc)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $request->validate($this->lineRules());

        try {
            $svc->updateDraft($invoice, $request->only(
                'client_name', 'client_vat_number', 'reference', 'invoice_date', 'due_date', 'notes', 'lines'
            ));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route($this->showRouteName($moduleType), [$slug, $invoice->id])
            ->with('success', 'Invoice updated.');
    }

    public function destroy(string $slug, StandardInvoice $invoice)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        abort_if($invoice->status !== 'draft', 403, 'Only draft invoices can be deleted.');

        $invoice->lines()->delete();
        $invoice->delete();

        return redirect()->route($this->indexRouteName($moduleType), [$slug])
            ->with('success', 'Draft deleted.');
    }

    public function post(string $slug, StandardInvoice $invoice, StandardInvoicingService $svc)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        try {
            $svc->post($invoice);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route($this->showRouteName($moduleType), [$slug, $invoice->id])
            ->with('success', 'Invoice posted.');
    }

    public function void(Request $request, string $slug, StandardInvoice $invoice, StandardInvoicingService $svc)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        try {
            $svc->void($invoice, $request->input('reason'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route($this->showRouteName($moduleType), [$slug, $invoice->id])
            ->with('success', 'Invoice voided.');
    }

    public function storePayment(Request $request, string $slug, StandardInvoice $invoice, StandardInvoicingService $svc)
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

    public function pdf(string $slug, StandardInvoice $invoice)
    {
        $tenant     = app('tenant');
        $moduleType = $this->moduleType();
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $invoice->load('lines');

        $pdf = Pdf::loadView('tenant.accounting.standard_invoices.pdf', compact('tenant', 'invoice', 'moduleType', 'slug'));

        return $pdf->stream("{$invoice->invoice_number}.pdf");
    }

    private function showRouteName(string $moduleType): string
    {
        return $moduleType === 'real_estate'
            ? 'tenant.accounting.re.invoices.show'
            : 'tenant.accounting.general.invoices.show';
    }

    private function indexRouteName(string $moduleType): string
    {
        return $moduleType === 'real_estate'
            ? 'tenant.accounting.re.invoices.index'
            : 'tenant.accounting.general.invoices.index';
    }

    private function lineRules(): array
    {
        return [
            'client_name'           => 'required|string|max:255',
            'client_vat_number'     => 'nullable|string|max:50',
            'reference'             => 'nullable|string|max:255',
            'invoice_date'          => 'required|date',
            'due_date'              => 'nullable|date|after_or_equal:invoice_date',
            'notes'                 => 'nullable|string',
            'lines'                 => 'required|array|min:1',
            'lines.*.description'   => 'required|string|max:255',
            'lines.*.quantity'      => 'required|numeric|min:0.0001',
            'lines.*.unit_price'    => 'required|numeric|min:0',
            'lines.*.vat_treatment' => ['required', Rule::in(StandardInvoice::VAT_TREATMENTS)],
            'lines.*.vat_rate'      => 'required|numeric|min:0|max:100',
        ];
    }
}
