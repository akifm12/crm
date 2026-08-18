<?php
// app/Http/Controllers/Tenant/Accounting/ClientPaymentController.php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\Accounting\InvoicingService;
use Illuminate\Http\Request;
use RuntimeException;

class ClientPaymentController extends Controller
{
    /**
     * Generic payment/deposit entry point for a client — used from the Client Statement
     * page. The invoice is optional: pick one to pay it down (even while pending_fix), or
     * leave it blank to record a pure margin/deposit held on the client's account.
     */
    public function store(Request $request, string $slug, BullionClient $client)
    {
        $tenant = app('tenant');
        abort_if($client->tenant_id !== $tenant->id, 404);

        $request->validate([
            'invoice_id'   => 'nullable|exists:invoices,id',
            'direction'    => 'nullable|in:in,out',
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'nullable|in:cash,bank_transfer,card,other',
            'reference'    => 'nullable|string|max:255',
            'purpose'      => 'nullable|string|max:255',
        ]);

        if ($request->filled('invoice_id')) {
            $invoice = Invoice::findOrFail($request->invoice_id);
            abort_if($invoice->tenant_id !== $tenant->id || $invoice->bullion_client_id !== $client->id, 404);
        } else {
            abort_if(! $request->filled('direction'), 422, 'Direction is required for a payment with no invoice.');
        }

        try {
            $payment = app(InvoicingService::class)->recordClientPayment($tenant, $client, [
                'invoice_id'   => $request->invoice_id,
                'direction'    => $request->direction,
                'payment_date' => $request->payment_date,
                'amount'       => $request->amount,
                'method'       => $request->method,
                'reference'    => $request->reference,
                'purpose'      => $request->purpose,
                'created_by'   => auth()->id(),
            ]);
        } catch (UnbalancedJournalEntryException|RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.accounting.reports.client-statement', [$tenant->slug, 'client_id' => $client->id])
            ->with('success', 'Payment recorded.')
            ->with('receipt_payment_id', $payment->id);
    }

    public function receipt(string $slug, InvoicePayment $payment)
    {
        $tenant = app('tenant');
        abort_if($payment->tenant_id !== $tenant->id, 404);

        $payment->load(['client', 'invoice', 'creator']);

        return view('tenant.accounting.payments.receipt', compact('tenant', 'payment'));
    }

    public function apply(Request $request, string $slug, InvoicePayment $deposit)
    {
        $tenant = app('tenant');
        abort_if($deposit->tenant_id !== $tenant->id, 404);
        abort_if($deposit->invoice_id !== null, 422, 'This payment is already linked to an invoice.');

        $request->validate(['invoice_id' => 'required|exists:invoices,id']);

        $invoice = Invoice::findOrFail($request->invoice_id);
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        app(InvoicingService::class)->applyDepositToInvoice($deposit, $invoice);

        return redirect()->route('tenant.accounting.reports.client-statement', [$tenant->slug, 'client_id' => $deposit->bullion_client_id])
            ->with('success', 'Deposit applied to ' . $invoice->invoice_number . '.');
    }
}
