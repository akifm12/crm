<?php

// app/Http/Controllers/Tenant/Accounting/InvoiceController.php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\UnbalancedJournalEntryException;
use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\Accounting\InvoicingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');

        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->with('client')
            ->when($request->filled('type'), fn ($q) => $q->where('invoice_type', $request->input('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('tenant.accounting.invoices.index', compact('tenant', 'invoices'));
    }

    public function create(Request $request)
    {
        $tenant = app('tenant');

        $clients = BullionClient::where('tenant_id', $tenant->id)->orderBy('company_name')->orderBy('full_name')->get();
        $items = InventoryItem::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $invoiceType = $request->input('type', 'purchase');
        $selectedClientId = $request->integer('client_id') ?: null;

        return view('tenant.accounting.invoices.create', compact('tenant', 'clients', 'items', 'invoiceType', 'selectedClientId'));
    }

    public function store(Request $request, InvoicingService $invoicing)
    {
        $tenant = app('tenant');

        $request->validate([
            'bullion_client_id' => 'required|exists:bullion_clients,id',
            'invoice_type' => ['required', Rule::in(Invoice::TYPES)],
            'pricing_type' => ['nullable', Rule::in(Invoice::PRICING_TYPES)],
            'invoice_date' => 'required|date',
            'currency_code' => 'nullable|string|size:3',
            'exchange_rate' => 'nullable|numeric|min:0.000001',
            'metal_rates' => 'nullable|array',
            'metal_rates.*.usd_per_oz' => 'nullable|numeric|min:0',
            'metal_rates.*.usd_aed_rate' => 'nullable|numeric|min:0',
            'party_reference' => 'nullable|string|max:255',
            'premium_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.line_type' => ['required', Rule::in(InvoiceLine::LINE_TYPES)],
            'lines.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
            'lines.*.metal_type' => 'nullable|string|max:20',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.purity' => 'nullable|numeric|min:0|max:999.999',
            'lines.*.quantity_grams' => 'nullable|numeric|min:0',
            'lines.*.gross_weight_grams' => 'nullable|numeric|min:0',
            'lines.*.pcs' => 'nullable|integer|min:0',
            'lines.*.unit_price' => 'nullable|numeric|min:0',
            'lines.*.line_subtotal' => 'nullable|numeric|min:0',
            'lines.*.metal_vat_treatment' => ['nullable', Rule::in(InvoiceLine::VAT_TREATMENTS)],
            'lines.*.metal_vat_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.making_charge_rate' => 'nullable|numeric|min:0',
            'lines.*.making_charge_amount' => 'nullable|numeric|min:0',
            'lines.*.making_vat_treatment' => ['nullable', Rule::in(InvoiceLine::VAT_TREATMENTS)],
            'lines.*.making_vat_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $client = BullionClient::findOrFail($request->bullion_client_id);
        abort_if($client->tenant_id !== $tenant->id, 404);

        $invoice = $invoicing->createDraft($tenant, [
            'bullion_client_id' => $request->bullion_client_id,
            'invoice_type' => $request->invoice_type,
            'pricing_type' => $request->pricing_type ?: 'fixed',
            'invoice_date' => $request->invoice_date,
            'currency_code' => $request->currency_code ?: 'AED',
            'exchange_rate' => $request->exchange_rate ?: 1,
            'metal_rates' => $request->input('metal_rates'),
            'party_reference' => $request->party_reference,
            'premium_amount' => $request->premium_amount ?: 0,
            'discount_amount' => $request->discount_amount ?: 0,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
            'lines' => $request->lines,
        ]);

        return redirect()->route('tenant.accounting.invoices.show', [$tenant->slug, $invoice->id])
            ->with('success', 'Draft invoice created.');
    }

    public function show(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        $invoice->load('lines.inventoryItem', 'client', 'payments', 'journalEntry');

        return view('tenant.accounting.invoices.show', compact('tenant', 'invoice'));
    }

    public function post(string $slug, Invoice $invoice, InvoicingService $invoicing)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        try {
            $invoicing->post($invoice);
        } catch (InsufficientStockException|UnbalancedJournalEntryException|RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.accounting.invoices.show', [$tenant->slug, $invoice->id])
            ->with('success', 'Invoice posted.');
    }

    public function fixPriceForm(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        abort_if($invoice->status !== 'pending_fix', 422, 'This invoice is not awaiting a price fix.');

        $invoice->load('lines.inventoryItem', 'client');

        return view('tenant.accounting.invoices.fix', compact('tenant', 'invoice'));
    }

    public function fixPrice(Request $request, string $slug, Invoice $invoice, InvoicingService $invoicing)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        $request->validate([
            'metal_rates' => 'nullable|array',
            'metal_rates.*.usd_per_oz' => 'nullable|numeric|min:0',
            'metal_rates.*.usd_aed_rate' => 'nullable|numeric|min:0',
            'lines' => 'required|array',
            'lines.*.unit_price' => 'nullable|numeric|min:0',
            'lines.*.metal_vat_treatment' => ['required', Rule::in(InvoiceLine::VAT_TREATMENTS)],
            'lines.*.metal_vat_rate' => 'required|numeric|min:0|max:100',
            'lines.*.making_charge_rate' => 'nullable|numeric|min:0',
            'lines.*.making_vat_treatment' => ['required', Rule::in(InvoiceLine::VAT_TREATMENTS)],
            'lines.*.making_vat_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($request->filled('metal_rates')) {
            $invoice->update(['metal_rates' => $request->input('metal_rates')]);
        }

        try {
            $invoicing->fixPrice($invoice, $request->input('lines'), auth()->id());
        } catch (UnbalancedJournalEntryException|RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.accounting.invoices.show', [$tenant->slug, $invoice->id])
            ->with('success', 'Price fixed and invoice posted.');
    }

    public function void(Request $request, string $slug, Invoice $invoice, InvoicingService $invoicing)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        $invoicing->void($invoice, $request->input('reason'));

        return redirect()->route('tenant.accounting.invoices.show', [$tenant->slug, $invoice->id])
            ->with('success', 'Invoice voided.');
    }

    public function destroy(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        abort_if($invoice->status !== 'draft', 422, 'Only draft invoices can be deleted.');

        $invoice->lines()->delete();
        $invoice->delete();

        return redirect()->route('tenant.accounting.invoices.index', $tenant->slug)
            ->with('success', 'Draft invoice deleted.');
    }

    public function pdf(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        $invoice->load('lines', 'client');

        $pdf = Pdf::loadView('tenant.accounting.invoices.pdf', compact('tenant', 'invoice'));

        return $pdf->stream("{$invoice->invoice_number}.pdf");
    }

    public function storePayment(Request $request, string $slug, Invoice $invoice, InvoicingService $invoicing)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'nullable|in:cash,bank_transfer,card,other',
            'reference' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
        ]);

        $payment = $invoicing->recordPayment($invoice, [
            'payment_date' => $request->payment_date,
            'amount' => $request->amount,
            'method' => $request->method,
            'reference' => $request->reference,
            'purpose' => $request->purpose,
            'created_by' => auth()->id(),
        ]);

        return back()
            ->with('success', $invoice->status === 'pending_fix' ? 'Deposit recorded.' : 'Payment recorded.')
            ->with('receipt_payment_id', $payment->id);
    }
}
