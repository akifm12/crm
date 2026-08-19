<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\StandardInvoice;
use App\Models\StandardInvoiceLine;
use App\Models\StandardInvoicePayment;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class StandardInvoicingService
{
    public function __construct(private readonly LedgerService $ledger) {}

    public function createDraft(Tenant $tenant, string $moduleType, array $data): StandardInvoice
    {
        return DB::transaction(function () use ($tenant, $moduleType, $data) {
            $computed = $this->computeLines($data['lines'] ?? [], $moduleType);
            $subtotal = round(collect($computed)->sum('amount'), 2);
            $vatTotal = round(collect($computed)->sum('vat_amount'), 2);
            $total    = round($subtotal + $vatTotal, 2);

            $invoice = StandardInvoice::create([
                'tenant_id'          => $tenant->id,
                'module_type'        => $moduleType,
                'invoice_number'     => $this->nextNumber($tenant, $moduleType),
                'client_name'        => $data['client_name'],
                'client_vat_number'  => $data['client_vat_number'] ?? null,
                'reference'          => $data['reference'] ?? null,
                'invoice_date'       => $data['invoice_date'],
                'due_date'           => $data['due_date'] ?? null,
                'status'             => 'draft',
                'subtotal'           => $subtotal,
                'vat_total'          => $vatTotal,
                'total'              => $total,
                'notes'              => $data['notes'] ?? null,
                'created_by'         => auth()->id(),
            ]);

            foreach ($computed as $i => $line) {
                StandardInvoiceLine::create(array_merge($line, ['standard_invoice_id' => $invoice->id, 'line_order' => $i]));
            }

            return $invoice->fresh('lines');
        });
    }

    public function updateDraft(StandardInvoice $invoice, array $data): StandardInvoice
    {
        abort_if($invoice->status !== 'draft', 422, 'Only draft invoices can be edited.');

        return DB::transaction(function () use ($invoice, $data) {
            $computed = $this->computeLines($data['lines'] ?? [], $invoice->module_type);
            $subtotal = round(collect($computed)->sum('amount'), 2);
            $vatTotal = round(collect($computed)->sum('vat_amount'), 2);
            $total    = round($subtotal + $vatTotal, 2);

            $invoice->update([
                'client_name'       => $data['client_name'],
                'client_vat_number' => $data['client_vat_number'] ?? null,
                'reference'         => $data['reference'] ?? null,
                'invoice_date'      => $data['invoice_date'],
                'due_date'          => $data['due_date'] ?? null,
                'subtotal'          => $subtotal,
                'vat_total'         => $vatTotal,
                'total'             => $total,
                'notes'             => $data['notes'] ?? null,
            ]);

            $invoice->lines()->delete();
            foreach ($computed as $i => $line) {
                StandardInvoiceLine::create(array_merge($line, ['standard_invoice_id' => $invoice->id, 'line_order' => $i]));
            }

            return $invoice->fresh('lines');
        });
    }

    public function post(StandardInvoice $invoice): StandardInvoice
    {
        abort_if($invoice->status !== 'draft', 422, 'Only draft invoices can be posted.');

        return DB::transaction(function () use ($invoice) {
            $tenant  = $invoice->tenant;
            $ar      = $this->requireAccount($tenant, 'ar');
            $vatPay  = $invoice->vat_total > 0 ? $this->requireAccount($tenant, 'vat_payable') : null;
            $revenue = $this->requireAccount($tenant, 'sales_revenue');

            $lines = [];

            // Dr Accounts Receivable for total
            $lines[] = [
                'chart_of_account_id' => $ar->id,
                'debit'               => (float) $invoice->total,
                'credit'              => 0,
                'description'         => $invoice->client_name . ($invoice->reference ? ' / ' . $invoice->reference : ''),
            ];

            // Cr Revenue for subtotal
            $lines[] = [
                'chart_of_account_id' => $revenue->id,
                'debit'               => 0,
                'credit'              => (float) $invoice->subtotal,
                'description'         => 'Sales — ' . $invoice->client_name,
            ];

            // Cr VAT Payable
            if ($vatPay && $invoice->vat_total > 0) {
                $lines[] = [
                    'chart_of_account_id' => $vatPay->id,
                    'debit'               => 0,
                    'credit'              => (float) $invoice->vat_total,
                    'description'         => 'VAT — ' . $invoice->invoice_number,
                ];
            }

            $entry = $this->ledger->post($tenant, [
                'entry_date'  => $invoice->invoice_date->toDateString(),
                'reference'   => $invoice->invoice_number,
                'source_type' => 'standard_invoice',
                'source_id'   => $invoice->id,
                'created_by'  => auth()->id(),
            ], $lines);

            $invoice->update([
                'status'           => 'posted',
                'journal_entry_id' => $entry->id,
                'posted_at'        => now(),
            ]);

            return $invoice->fresh(['lines', 'journalEntry']);
        });
    }

    public function recordPayment(StandardInvoice $invoice, array $data): StandardInvoicePayment
    {
        abort_if($invoice->status !== 'posted', 422, 'Payments can only be recorded on posted invoices.');

        return DB::transaction(function () use ($invoice, $data) {
            $tenant = $invoice->tenant;
            $amount = round((float) $data['amount'], 2);

            $ar   = $this->requireAccount($tenant, 'ar');
            $cash = $this->requireAccount($tenant, $data['account'] ?? 'bank');

            $entry = $this->ledger->post($tenant, [
                'entry_date'  => $data['payment_date'],
                'reference'   => $invoice->invoice_number,
                'source_type' => 'standard_invoice_payment',
                'source_id'   => $invoice->id,
                'created_by'  => auth()->id(),
            ], [
                ['chart_of_account_id' => $cash->id, 'debit' => $amount, 'credit' => 0,      'description' => 'Receipt — ' . $invoice->client_name],
                ['chart_of_account_id' => $ar->id,   'debit' => 0,       'credit' => $amount, 'description' => 'Receipt — ' . $invoice->client_name],
            ]);

            $payment = StandardInvoicePayment::create([
                'standard_invoice_id' => $invoice->id,
                'tenant_id'           => $tenant->id,
                'payment_date'        => $data['payment_date'],
                'amount'              => $amount,
                'method'              => $data['method'] ?? null,
                'reference'           => $data['reference'] ?? null,
                'journal_entry_id'    => $entry->id,
                'created_by'          => auth()->id(),
            ]);

            $invoice->increment('amount_paid', $amount);

            return $payment;
        });
    }

    public function void(StandardInvoice $invoice, ?string $reason = null): StandardInvoice
    {
        abort_if(! in_array($invoice->status, ['draft', 'posted'], true), 422, 'This invoice cannot be voided.');

        return DB::transaction(function () use ($invoice, $reason) {
            if ($invoice->status === 'posted' && $invoice->journalEntry) {
                $this->ledger->void($invoice->journalEntry, $reason);
            }

            $invoice->update(['status' => 'void', 'voided_at' => now()]);

            return $invoice->fresh();
        });
    }

    private function computeLines(array $lines, string $moduleType = 'general'): array
    {
        return collect($lines)->map(function (array $line) use ($moduleType) {
            $qty       = (float) ($line['quantity'] ?? 1);
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            // For real estate: quantity = property value, unit_price = commission rate %
            $amount = $moduleType === 'real_estate'
                ? round($qty * $unitPrice / 100, 2)
                : round($qty * $unitPrice, 2);
            $vatRate   = (float) ($line['vat_rate'] ?? 0);
            $treatment = $line['vat_treatment'] ?? 'standard';

            $vatAmount = in_array($treatment, ['standard', 'reverse_charge'])
                ? round($amount * $vatRate / 100, 2)
                : 0.0;

            return [
                'description'   => $line['description'] ?? '',
                'quantity'      => $qty,
                'unit_price'    => $unitPrice,
                'amount'        => $amount,
                'vat_treatment' => $treatment,
                'vat_rate'      => $vatRate,
                'vat_amount'    => $vatAmount,
                'line_total'    => round($amount + $vatAmount, 2),
            ];
        })->filter(fn ($l) => $l['amount'] > 0 || strlen($l['description']) > 0)
          ->values()->all();
    }

    private function nextNumber(Tenant $tenant, string $moduleType): string
    {
        $prefix = $moduleType === 'real_estate' ? 'RENT' : 'INV';

        $last = StandardInvoice::where('tenant_id', $tenant->id)
            ->where('module_type', $moduleType)
            ->where('invoice_number', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING(invoice_number, ' . (strlen($prefix) + 2) . ') AS UNSIGNED) DESC')
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, strlen($prefix) + 1)) + 1 : 1;

        return $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    private function requireAccount(Tenant $tenant, string $subtype): ChartOfAccount
    {
        $account = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('subtype', $subtype)
            ->where('is_active', true)
            ->first();

        abort_if(! $account, 500, "Chart of accounts is missing a required '{$subtype}' account.");

        return $account;
    }
}
