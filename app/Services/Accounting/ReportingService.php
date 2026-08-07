<?php

// app/Services/Accounting/ReportingService.php

namespace App\Services\Accounting;

use App\Models\BullionClient;
use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportingService
{
    /**
     * Per-account totals (base currency, AED), optionally as-of a date and/or bounded to a
     * period start. Returns one row per account, zero-filled.
     *
     * Deliberately does NOT filter out status='void' entries: a voided entry's original
     * posting and its reversing entry are both real, dated ledger history (per double-entry
     * / reversing-entry convention) — together they net to zero. Excluding the void'd
     * original while keeping its reversal would leave only one side of the pair counted,
     * permanently skewing every balance by the reversal's amount.
     */
    public function trialBalance(Tenant $tenant, ?Carbon $asOf = null, ?Carbon $from = null): Collection
    {
        $sums = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenant->id)
            ->when($from, fn ($q) => $q->where('journal_entries.entry_date', '>=', $from->toDateString()))
            ->when($asOf, fn ($q) => $q->where('journal_entries.entry_date', '<=', $asOf->toDateString()))
            ->groupBy('journal_entry_lines.chart_of_account_id')
            ->selectRaw('journal_entry_lines.chart_of_account_id as account_id, SUM(journal_entry_lines.base_debit) as total_debit, SUM(journal_entry_lines.base_credit) as total_credit')
            ->get()
            ->keyBy('account_id');

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return $accounts->map(function (ChartOfAccount $account) use ($sums) {
            $row = $sums->get($account->id);

            return [
                'account' => $account,
                'debit' => (float) ($row->total_debit ?? 0),
                'credit' => (float) ($row->total_credit ?? 0),
            ];
        });
    }

    /**
     * Balance sheet as-of a date, with the current period's net income (income - expense,
     * since inception) folded into equity as "Current Period Earnings" — standard presentation
     * since there is no formal period-close/roll-to-retained-earnings step in v1.
     */
    public function balanceSheet(Tenant $tenant, ?Carbon $asOf = null): array
    {
        $trialBalance = $this->trialBalance($tenant, $asOf);

        $signedBalance = fn (array $row) => $row['account']->normal_balance === 'debit'
            ? $row['debit'] - $row['credit']
            : $row['credit'] - $row['debit'];

        $group = fn (string $type) => $trialBalance
            ->filter(fn ($row) => $row['account']->type === $type)
            ->map(fn ($row) => ['account' => $row['account'], 'balance' => $signedBalance($row)])
            ->filter(fn ($row) => abs($row['balance']) > 0.001)
            ->values();

        $assets = $group('asset');
        $liabilities = $group('liability');
        $equity = $group('equity');
        $income = $group('income');
        $expense = $group('expense');

        $totalAssets = round($assets->sum('balance'), 2);
        $totalLiabilities = round($liabilities->sum('balance'), 2);
        $totalEquity = round($equity->sum('balance'), 2);
        $netIncome = round($income->sum('balance') - $expense->sum('balance'), 2);

        $balanced = abs($totalAssets - ($totalLiabilities + $totalEquity + $netIncome)) < 0.01;

        return compact(
            'assets', 'liabilities', 'equity',
            'totalAssets', 'totalLiabilities', 'totalEquity', 'netIncome', 'balanced'
        );
    }

    public function incomeStatement(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $trialBalance = $this->trialBalance($tenant, $to, $from);

        $signedBalance = fn (array $row) => $row['account']->normal_balance === 'debit'
            ? $row['debit'] - $row['credit']
            : $row['credit'] - $row['debit'];

        $group = fn (string $type) => $trialBalance
            ->filter(fn ($row) => $row['account']->type === $type)
            ->map(fn ($row) => ['account' => $row['account'], 'balance' => $signedBalance($row)])
            ->filter(fn ($row) => abs($row['balance']) > 0.001)
            ->values();

        $income = $group('income');
        $expense = $group('expense');

        $totalIncome = round($income->sum('balance'), 2);
        $totalExpense = round($expense->sum('balance'), 2);

        return [
            'income' => $income,
            'expense' => $expense,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netIncome' => round($totalIncome - $totalExpense, 2),
        ];
    }

    /**
     * VAT summary grouped by treatment (standard/zero_rated/reverse_charge/exempt), sourced
     * from posted invoices' line items — near-free from the same aggregation approach,
     * and what a tenant will want at FTA filing time. Metal and making-charge portions can
     * carry different treatments on the same line, so both are bucketed independently.
     */
    public function vatSummary(Tenant $tenant, Carbon $from, Carbon $to): Collection
    {
        $lines = InvoiceLine::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_lines.invoice_id')
            ->where('invoices.tenant_id', $tenant->id)
            ->where('invoices.status', 'posted')
            ->whereBetween('invoices.invoice_date', [$from->toDateString(), $to->toDateString()])
            ->select('invoice_lines.*')
            ->get();

        $buckets = [];

        $addTo = function (string $treatment, float $taxable, float $tax) use (&$buckets) {
            $buckets[$treatment] ??= ['vat_treatment' => $treatment, 'subtotal' => 0.0, 'vat' => 0.0];
            $buckets[$treatment]['subtotal'] += $taxable;
            $buckets[$treatment]['vat'] += $tax;
        };

        foreach ($lines as $line) {
            $addTo($line->metal_vat_treatment, (float) $line->line_subtotal, (float) $line->metal_vat_amount);

            if ((float) $line->making_charge_amount > 0) {
                $addTo($line->making_vat_treatment, (float) $line->making_charge_amount, (float) $line->making_vat_amount);
            }
        }

        return collect(array_values($buckets));
    }

    /**
     * Statement of accounts for a single client: a chronological ledger of every posted
     * invoice, payment, and deposit that moved their balance, with a running total.
     * Positive balance = client owes the dealer; negative = dealer owes client. A held
     * customer deposit counts against the balance the moment it's received (even before
     * the underlying invoice is priced), and a held supplier deposit/advance counts for it —
     * both symmetric with how AR/AP already work.
     *
     * Derived directly from the AR/AP/Deposits journal postings (not re-summed from invoice
     * totals) so it always matches the real ledger, including voids/reversals. Deposit
     * reclass entries (Deposits -> AR/AP once an invoice is priced) net to zero across these
     * four accounts by construction, so they're automatically skipped as zero-amount rows.
     */
    public function clientStatement(Tenant $tenant, BullionClient $client, ?Carbon $asOf = null): array
    {
        $arAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('subtype', 'ar')->first();
        $apAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('subtype', 'ap')->first();
        $customerDepositsAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('subtype', 'customer_deposits')->first();
        $supplierDepositsAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('subtype', 'supplier_deposits')->first();
        $accountIds = array_filter([$arAccount?->id, $apAccount?->id, $customerDepositsAccount?->id, $supplierDepositsAccount?->id]);

        $invoiceIds = Invoice::where('tenant_id', $tenant->id)
            ->where('bullion_client_id', $client->id)
            ->pluck('id');

        // Payments (including unlinked margin/deposits) are found via their own
        // journal_entry_id, not via source_id — an unlinked deposit has no invoice at all.
        $paymentJournalIds = InvoicePayment::where('tenant_id', $tenant->id)
            ->where('bullion_client_id', $client->id)
            ->whereNotNull('journal_entry_id')
            ->pluck('journal_entry_id');

        if (($invoiceIds->isEmpty() && $paymentJournalIds->isEmpty()) || empty($accountIds)) {
            return ['rows' => collect(), 'balance' => 0.0];
        }

        $invoicesById = Invoice::whereIn('id', $invoiceIds)->get()->keyBy('id');

        $entries = JournalEntry::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($invoiceIds, $paymentJournalIds) {
                $q->where(fn ($q2) => $q2->whereIn('source_type', ['invoice', 'deposit_reclass'])->whereIn('source_id', $invoiceIds))
                    ->orWhereIn('id', $paymentJournalIds);
            })
            ->when($asOf, fn ($q) => $q->where('entry_date', '<=', $asOf->toDateString()))
            ->with(['lines' => fn ($q) => $q->whereIn('chart_of_account_id', $accountIds)])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $rows = collect();
        $running = 0.0;

        foreach ($entries as $entry) {
            $sum = fn (?int $accountId, string $column) => $accountId
                ? (float) $entry->lines->where('chart_of_account_id', $accountId)->sum($column)
                : 0.0;

            $net = round(
                ($sum($arAccount?->id, 'base_debit') - $sum($arAccount?->id, 'base_credit'))
                - ($sum($apAccount?->id, 'base_credit') - $sum($apAccount?->id, 'base_debit'))
                - ($sum($customerDepositsAccount?->id, 'base_credit') - $sum($customerDepositsAccount?->id, 'base_debit'))
                + ($sum($supplierDepositsAccount?->id, 'base_debit') - $sum($supplierDepositsAccount?->id, 'base_credit')),
                2
            );

            if (abs($net) < 0.001) {
                continue;
            }

            $running = round($running + $net, 2);
            $invoice = $invoicesById->get($entry->source_id);

            $description = match (true) {
                $entry->source_type === 'invoice' => ($invoice->invoice_number ?? $entry->reference).' ('.ucfirst($invoice->invoice_type ?? '').')',
                $entry->source_type === 'invoice_payment' && $invoice => 'Payment — '.$invoice->invoice_number,
                $entry->source_type === 'invoice_payment' => 'Deposit / margin payment',
                default => $entry->reference ?? ucfirst(str_replace('_', ' ', $entry->source_type)),
            };

            $rows->push([
                'date' => $entry->entry_date,
                'description' => $description,
                'invoice' => $invoice,
                'amount' => $net,
                'balance' => $running,
            ]);
        }

        return ['rows' => $rows, 'balance' => $running];
    }

    /**
     * Per-client breakdown of AR/AP-based balance and total sales revenue — the subsidiary-
     * ledger detail behind the Trial Balance's single, deliberately-lumped "Accounts
     * Receivable"/"Sales Revenue — {metal}" control accounts (a trial balance listing one
     * row per customer wouldn't stay readable, and isn't standard practice). Corporate
     * clients get their own row; individuals are combined into one row, since the dealer's
     * volume is concentrated in its corporate accounts.
     *
     * Unlike clientStatement() (which reconstructs client identity per-invoice via source_id
     * joins), this uses journal_entries.bullion_client_id directly — a single grouped query
     * across every client at once rather than one query per client.
     */
    public function clientBalancesSummary(Tenant $tenant): array
    {
        $arAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('subtype', 'ar')->first();
        $apAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('subtype', 'ap')->first();
        $customerDepositsAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('subtype', 'customer_deposits')->first();
        $supplierDepositsAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('subtype', 'supplier_deposits')->first();

        $salesAccountIds = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('type', 'income')
            ->where(fn ($q) => $q->where('subtype', 'like', 'sales_%'))
            ->pluck('id');

        $sums = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenant->id)
            ->whereNotNull('journal_entries.bullion_client_id')
            ->groupBy('journal_entries.bullion_client_id', 'journal_entry_lines.chart_of_account_id')
            ->selectRaw('journal_entries.bullion_client_id as client_id, journal_entry_lines.chart_of_account_id as account_id, SUM(journal_entry_lines.base_debit) as total_debit, SUM(journal_entry_lines.base_credit) as total_credit')
            ->get()
            ->groupBy('client_id');

        $clients = BullionClient::where('tenant_id', $tenant->id)->whereIn('id', $sums->keys())->get()->keyBy('id');

        $corporate = collect();
        $individualsBalance = 0.0;
        $individualsSales = 0.0;

        foreach ($sums as $clientId => $accountSums) {
            $client = $clients->get($clientId);
            if (! $client) {
                continue;
            }

            $byAccount = $accountSums->keyBy('account_id');
            $get = fn (?int $id, string $column) => $id && $byAccount->has($id) ? (float) $byAccount->get($id)->$column : 0.0;

            $balance = round(
                ($get($arAccount?->id, 'total_debit') - $get($arAccount?->id, 'total_credit'))
                - ($get($apAccount?->id, 'total_credit') - $get($apAccount?->id, 'total_debit'))
                - ($get($customerDepositsAccount?->id, 'total_credit') - $get($customerDepositsAccount?->id, 'total_debit'))
                + ($get($supplierDepositsAccount?->id, 'total_debit') - $get($supplierDepositsAccount?->id, 'total_credit')),
                2
            );

            $salesRevenue = round($salesAccountIds->sum(fn ($id) => $get($id, 'total_credit') - $get($id, 'total_debit')), 2);

            if ($client->client_type === 'individual') {
                $individualsBalance += $balance;
                $individualsSales += $salesRevenue;

                continue;
            }

            $corporate->push([
                'client' => $client,
                'balance' => $balance,
                'sales_revenue' => $salesRevenue,
            ]);
        }

        return [
            'corporate' => $corporate->sortByDesc('sales_revenue')->values(),
            'individuals' => [
                'balance' => round($individualsBalance, 2),
                'sales_revenue' => round($individualsSales, 2),
            ],
        ];
    }
}
