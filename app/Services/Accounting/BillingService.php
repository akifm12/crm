<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\BillLine;
use App\Models\BillPayment;
use App\Models\ChartOfAccount;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(private readonly LedgerService $ledger) {}

    // ── Create / update ──────────────────────────────────────────────────────

    public function createDraft(Tenant $tenant, array $data): Bill
    {
        return DB::transaction(function () use ($tenant, $data) {
            $computed = $this->computeLines($data['lines'] ?? []);
            $subtotal = round(collect($computed)->sum('amount'), 2);
            $vatTotal = round(collect($computed)->sum('vat_amount'), 2);
            $total    = round($subtotal + $vatTotal, 2);

            [$supplierName, $supplierVat, $supplierId] = $this->resolveSupplier($data);

            $bill = Bill::create([
                'tenant_id'           => $tenant->id,
                'supplier_id'         => $supplierId,
                'bill_number'         => $this->nextNumber($tenant),
                'supplier_name'       => $supplierName,
                'supplier_vat_number' => $supplierVat,
                'reference'           => $data['reference'] ?? null,
                'bill_date'           => $data['bill_date'],
                'due_date'            => $data['due_date'] ?? null,
                'status'              => 'draft',
                'subtotal'            => $subtotal,
                'vat_total'           => $vatTotal,
                'total'               => $total,
                'notes'               => $data['notes'] ?? null,
                'created_by'          => auth()->id(),
            ]);

            foreach ($computed as $i => $line) {
                BillLine::create(array_merge($line, ['bill_id' => $bill->id, 'line_order' => $i]));
            }

            return $bill->fresh('lines');
        });
    }

    public function updateDraft(Bill $bill, array $data): Bill
    {
        abort_if($bill->status !== 'draft', 422, 'Only draft bills can be edited.');

        return DB::transaction(function () use ($bill, $data) {
            $computed = $this->computeLines($data['lines'] ?? []);
            $subtotal = round(collect($computed)->sum('amount'), 2);
            $vatTotal = round(collect($computed)->sum('vat_amount'), 2);
            $total    = round($subtotal + $vatTotal, 2);

            [$supplierName, $supplierVat, $supplierId] = $this->resolveSupplier($data);

            $bill->update([
                'supplier_id'         => $supplierId,
                'supplier_name'       => $supplierName,
                'supplier_vat_number' => $supplierVat,
                'reference'           => $data['reference'] ?? null,
                'bill_date'           => $data['bill_date'],
                'due_date'            => $data['due_date'] ?? null,
                'subtotal'            => $subtotal,
                'vat_total'           => $vatTotal,
                'total'               => $total,
                'notes'               => $data['notes'] ?? null,
            ]);

            $bill->lines()->delete();
            foreach ($computed as $i => $line) {
                BillLine::create(array_merge($line, ['bill_id' => $bill->id, 'line_order' => $i]));
            }

            return $bill->fresh('lines');
        });
    }

    // ── Post ─────────────────────────────────────────────────────────────────

    public function post(Bill $bill): Bill
    {
        abort_if($bill->status !== 'draft', 422, 'Only draft bills can be posted.');

        return DB::transaction(function () use ($bill) {
            $tenant = $bill->tenant;

            $ap      = $this->requireAccount($tenant, 'ap');
            $expense = $this->requireAccount($tenant, 'other_expense');
            $vatRec  = $bill->vat_total > 0 ? $this->requireAccount($tenant, 'vat_recoverable') : null;

            $lines = [];

            // Debit each expense line amount
            foreach ($bill->lines as $line) {
                if ((float) $line->amount <= 0) continue;
                $lines[] = [
                    'chart_of_account_id' => $expense->id,
                    'debit'               => (float) $line->amount,
                    'credit'              => 0,
                    'description'         => $line->description,
                ];
                // Debit input VAT per line (standard treatment only)
                if ($vatRec && in_array($line->vat_treatment, ['standard', 'reverse_charge']) && (float) $line->vat_amount > 0) {
                    $lines[] = [
                        'chart_of_account_id' => $vatRec->id,
                        'debit'               => (float) $line->vat_amount,
                        'credit'              => 0,
                        'description'         => 'VAT — ' . $line->description,
                    ];
                }
            }

            // Credit AP for the total
            $lines[] = [
                'chart_of_account_id' => $ap->id,
                'debit'               => 0,
                'credit'              => (float) $bill->total,
                'description'         => $bill->supplier_name . ($bill->reference ? ' / ' . $bill->reference : ''),
            ];

            $entry = $this->ledger->post($tenant, [
                'entry_date'  => $bill->bill_date->toDateString(),
                'reference'   => $bill->bill_number,
                'source_type' => 'bill',
                'source_id'   => $bill->id,
                'created_by'  => auth()->id(),
            ], $lines);

            $bill->update([
                'status'           => 'posted',
                'journal_entry_id' => $entry->id,
                'posted_at'        => now(),
            ]);

            return $bill->fresh(['lines', 'journalEntry']);
        });
    }

    // ── Record payment ────────────────────────────────────────────────────────

    public function recordPayment(Bill $bill, array $data): BillPayment
    {
        abort_if(! in_array($bill->status, ['posted'], true), 422, 'Payments can only be recorded on posted bills.');

        return DB::transaction(function () use ($bill, $data) {
            $tenant = $bill->tenant;
            $amount = round((float) $data['amount'], 2);

            $ap   = $this->requireAccount($tenant, 'ap');
            $cash = $this->requireAccount($tenant, $data['account'] ?? 'bank');

            $entry = $this->ledger->post($tenant, [
                'entry_date'  => $data['payment_date'],
                'reference'   => $bill->bill_number,
                'source_type' => 'bill_payment',
                'source_id'   => $bill->id,
                'created_by'  => auth()->id(),
            ], [
                ['chart_of_account_id' => $ap->id,   'debit' => $amount, 'credit' => 0,      'description' => 'Payment — ' . $bill->supplier_name],
                ['chart_of_account_id' => $cash->id, 'debit' => 0,       'credit' => $amount, 'description' => 'Payment — ' . $bill->supplier_name],
            ]);

            $payment = BillPayment::create([
                'bill_id'          => $bill->id,
                'tenant_id'        => $tenant->id,
                'payment_date'     => $data['payment_date'],
                'amount'           => $amount,
                'method'           => $data['method'] ?? null,
                'reference'        => $data['reference'] ?? null,
                'journal_entry_id' => $entry->id,
                'created_by'       => auth()->id(),
            ]);

            $bill->increment('amount_paid', $amount);

            return $payment;
        });
    }

    // ── Void ─────────────────────────────────────────────────────────────────

    public function void(Bill $bill, ?string $reason = null): Bill
    {
        abort_if(! in_array($bill->status, ['draft', 'posted'], true), 422, 'This bill cannot be voided.');

        return DB::transaction(function () use ($bill, $reason) {
            if ($bill->status === 'posted' && $bill->journalEntry) {
                $this->ledger->void($bill->journalEntry, $reason);
            }

            $bill->update(['status' => 'void', 'voided_at' => now()]);

            return $bill->fresh();
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveSupplier(array $data): array
    {
        if (! empty($data['supplier_id'])) {
            $supplier = \App\Models\Supplier::find($data['supplier_id']);
            if ($supplier) {
                return [$supplier->name, $supplier->vat_number, $supplier->id];
            }
        }
        return [$data['supplier_name'] ?? '', $data['supplier_vat_number'] ?? null, null];
    }

    private function computeLines(array $lines): array
    {
        return collect($lines)->map(function (array $line) {
            $amount   = round((float) ($line['amount'] ?? 0), 2);
            $vatRate  = (float) ($line['vat_rate'] ?? 0);
            $treatment = $line['vat_treatment'] ?? 'standard';

            $vatAmount = in_array($treatment, ['standard', 'reverse_charge'])
                ? round($amount * $vatRate / 100, 2)
                : 0.0;

            return [
                'description'   => $line['description'] ?? '',
                'category'      => $line['category'] ?? 'other',
                'amount'        => $amount,
                'vat_treatment' => $treatment,
                'vat_rate'      => $vatRate,
                'vat_amount'    => $vatAmount,
                'line_total'    => round($amount + $vatAmount, 2),
            ];
        })->filter(fn ($l) => $l['amount'] > 0 || strlen($l['description']) > 0)
          ->values()->all();
    }

    private function nextNumber(Tenant $tenant): string
    {
        $last = Bill::where('tenant_id', $tenant->id)
            ->where('bill_number', 'like', 'BILL-%')
            ->orderByRaw('CAST(SUBSTRING(bill_number, 6) AS UNSIGNED) DESC')
            ->value('bill_number');

        $next = $last ? ((int) substr($last, 5)) + 1 : 1;

        return 'BILL-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    private function requireAccount(Tenant $tenant, string $subtype): ChartOfAccount
    {
        $account = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('subtype', $subtype)
            ->where('is_active', true)
            ->first();

        abort_if(! $account, 500, "Chart of accounts is missing a required '{$subtype}' account for tenant {$tenant->slug}.");

        return $account;
    }
}
