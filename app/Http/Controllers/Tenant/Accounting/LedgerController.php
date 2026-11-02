<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    // ── Journal ──────────────────────────────────────────────────────────────

    public function journalIndex(Request $request, string $slug)
    {
        $tenant      = app('tenant');
        $dateFrom    = $request->get('date_from', date('Y-01-01'));
        $dateTo      = $request->get('date_to', date('Y-m-d'));
        $status      = $request->get('status', 'all');
        $journalType = $request->get('journal_type');
        $reference   = $request->get('reference');

        $query = JournalEntry::where('tenant_id', $tenant->id)
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        if ($status !== 'all') {
            $query->where('status', strtolower($status));
        }
        if ($reference) {
            $query->where('reference', 'like', "%{$reference}%");
        }

        $journals    = $query->get();

        // Compute totals from actual journal lines (total_debit/credit don't exist in DB)
        $jeIds       = $journals->pluck('id');
        $lineTotals  = DB::table('journal_entry_lines')
            ->whereIn('journal_entry_id', $jeIds)
            ->selectRaw('SUM(debit) as total_dr, SUM(credit) as total_cr')
            ->first();
        $totalDebit  = (float)($lineTotals->total_dr ?? 0);
        $totalCredit = (float)($lineTotals->total_cr ?? 0);

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('code')->get();

        return view('tenant.accounting.journal.index', compact('tenant', 'journals', 'dateFrom', 'dateTo', 'status', 'totalDebit', 'totalCredit', 'accounts', 'journalType', 'reference'));
    }

    public function journalStore(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate([
            'entry_date'  => 'required|date',
            'description' => 'required|string',
            'accounts'    => 'required|array|min:2',
        ]);

        $lines       = [];
        $totalDebit  = 0;
        $totalCredit = 0;

        foreach ($request->accounts as $i => $accountId) {
            if (!$accountId) continue;
            $debit   = floatval($request->debits[$i] ?? 0);
            $credit  = floatval($request->credits[$i] ?? 0);
            if ($debit == 0 && $credit == 0) continue;
            $totalDebit  += $debit;
            $totalCredit += $credit;
            $lines[] = ['account_id' => $accountId, 'debit_amount' => $debit, 'credit_amount' => $credit, 'description' => $request->line_descriptions[$i] ?? $request->description, 'line_number' => $i + 1];
        }

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            return back()->withInput()->with('error', 'Debits must equal credits.');
        }
        if (count($lines) < 2) {
            return back()->withInput()->with('error', 'At least 2 lines required.');
        }

        $period = AccountingPeriod::where('tenant_id', $tenant->id)
            ->where('start_date', '<=', $request->entry_date)
            ->where('end_date', '>=', $request->entry_date)
            ->first();

        $jeYear      = date('Y', strtotime($request->entry_date));
        $count       = JournalEntry::where('tenant_id', $tenant->id)->whereYear('entry_date', $jeYear)->count() + 1;
        $entryNumber = 'JE-' . $jeYear . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        $status      = $request->boolean('auto_post') ? 'POSTED' : 'DRAFT';

        DB::transaction(function () use ($tenant, $request, $lines, $totalDebit, $totalCredit, $period, $entryNumber, $status) {
            $je = JournalEntry::create(['tenant_id' => $tenant->id, 'entry_date' => $request->entry_date, 'source_type' => $request->journal_type ?? 'GENERAL', 'status' => strtolower($status), 'memo' => $request->description, 'reference' => $request->reference ?: null, 'currency_code' => 'AED', 'exchange_rate' => 1.0, 'created_by' => auth()->id()]);
            foreach ($lines as $line) {
                JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $line['account_id'], 'debit' => $line['debit_amount'], 'credit' => $line['credit_amount'], 'description' => $line['description'], 'line_order' => $line['line_number']]);
            }
        });

        return redirect()->route('tenant.accounting.journal.index', app('tenant')->slug)
            ->with('success', "Journal {$entryNumber} saved.");
    }

    public function journalShow(string $slug, JournalEntry $entry)
    {
        $tenant = app('tenant');
        abort_if($entry->tenant_id !== $tenant->id, 404);
        $lines = $entry->lines()->with('account')->orderByRaw('CASE WHEN debit > 0 THEN 0 ELSE 1 END')->orderBy('line_order')->get();
        return view('tenant.accounting.journal.show', compact('tenant', 'entry', 'lines'));
    }

    public function journalVoid(string $slug, JournalEntry $entry)
    {
        $tenant = app('tenant');
        abort_if($entry->tenant_id !== $tenant->id, 404);
        $entry->update(['status' => 'voided']);
        return redirect()->route('tenant.accounting.journal.index', $tenant->slug)->with('success', 'Journal entry voided.');
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function trialBalance(Request $request, string $slug)
    {
        $tenant   = app('tenant');
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo   = $request->get('date_to', date('Y-m-d'));

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)
            ->orderByRaw("FIELD(type,'ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE')")->orderBy('code')->get();

        $balances = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenant->id)
            ->whereIn('journal_entries.status', ['posted', 'POSTED'])
            ->whereBetween('journal_entries.entry_date', [$dateFrom, $dateTo])
            ->select('journal_entry_lines.chart_of_account_id as account_id', DB::raw('SUM(debit) as total_dr'), DB::raw('SUM(credit) as total_cr'))
            ->groupBy('journal_entry_lines.chart_of_account_id')
            ->get()
            ->keyBy('account_id');

        return view('tenant.accounting.reports.trial-balance', compact('tenant', 'accounts', 'balances', 'dateFrom', 'dateTo'));
    }

    public function trialBalancePdf(Request $request, string $slug) { return $this->trialBalance($request, $slug); }
    public function trialBalanceCsv(Request $request, string $slug) { return $this->trialBalance($request, $slug); }

    public function balanceSheet(Request $request, string $slug)
    {
        $tenant  = app('tenant');
        $asOfDate = $request->get('as_of', date('Y-m-d'));

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)
            ->whereIn('type', ['ASSET','LIABILITY','EQUITY'])->orderBy('type')->orderBy('code')->get();

        $balances = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenant->id)
            ->whereIn('journal_entries.status', ['posted', 'POSTED'])
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->select('journal_entry_lines.chart_of_account_id as account_id', DB::raw('SUM(debit) as total_dr'), DB::raw('SUM(credit) as total_cr'))
            ->groupBy('journal_entry_lines.chart_of_account_id')
            ->get()->keyBy('account_id');

        // Compute YTD net income (Revenue - Expenses) to include in equity
        $incomeAccounts = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)
            ->whereIn('type', ['REVENUE','EXPENSE'])->pluck('id');
        $incomeBalances = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenant->id)
            ->whereIn('journal_entries.status', ['posted', 'POSTED'])
            ->where('journal_entries.entry_date', '<=', $asOfDate)
            ->whereIn('journal_entry_lines.chart_of_account_id', $incomeAccounts)
            ->join('chart_of_accounts', 'chart_of_accounts.id', '=', 'journal_entry_lines.chart_of_account_id')
            ->select('chart_of_accounts.type', DB::raw('SUM(debit) as dr'), DB::raw('SUM(credit) as cr'))
            ->groupBy('chart_of_accounts.type')
            ->get()->keyBy('type');
        $revenue  = isset($incomeBalances['REVENUE'])  ? ($incomeBalances['REVENUE']->cr  - $incomeBalances['REVENUE']->dr)  : 0;
        $expenses = isset($incomeBalances['EXPENSE'])  ? ($incomeBalances['EXPENSE']->dr  - $incomeBalances['EXPENSE']->cr)  : 0;
        $netIncome = $revenue - $expenses;

        return view('tenant.accounting.reports.balance-sheet', compact('tenant', 'accounts', 'balances', 'asOfDate', 'netIncome'));
    }

    public function balanceSheetPdf(Request $request, string $slug) { return $this->balanceSheet($request, $slug); }
    public function balanceSheetCsv(Request $request, string $slug) { return $this->balanceSheet($request, $slug); }

    public function incomeStatement(Request $request, string $slug)
    {
        $tenant   = app('tenant');
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo   = $request->get('date_to', date('Y-m-d'));

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)
            ->whereIn('type', ['REVENUE','EXPENSE'])->orderBy('type')->orderBy('code')->get();

        $balances = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenant->id)
            ->whereIn('journal_entries.status', ['posted', 'POSTED'])
            ->whereBetween('journal_entries.entry_date', [$dateFrom, $dateTo])
            ->select('journal_entry_lines.chart_of_account_id as account_id', DB::raw('SUM(debit) as total_dr'), DB::raw('SUM(credit) as total_cr'))
            ->groupBy('journal_entry_lines.chart_of_account_id')
            ->get()->keyBy('account_id');

        return view('tenant.accounting.reports.income-statement', compact('tenant', 'accounts', 'balances', 'dateFrom', 'dateTo'));
    }

    public function incomeStatementPdf(Request $request, string $slug) { return $this->incomeStatement($request, $slug); }
    public function incomeStatementCsv(Request $request, string $slug) { return $this->incomeStatement($request, $slug); }

    public function vatSummary(Request $request, string $slug)
    {
        $tenant   = app('tenant');
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo   = $request->get('date_to', date('Y-m-d'));

        $bills = \App\Models\Bill::where('tenant_id', $tenant->id)
            ->whereIn('status', ['approved','paid','partial'])
            ->whereBetween('bill_date', [$dateFrom, $dateTo])
            ->get();

        $inputVat  = $bills->sum('vat_total');
        $outputVat = 0;

        return view('tenant.accounting.reports.vat-summary', compact('tenant', 'bills', 'inputVat', 'outputVat', 'dateFrom', 'dateTo'));
    }

    public function vatSummaryPdf(Request $request, string $slug) { return $this->vatSummary($request, $slug); }
    public function vatSummaryCsv(Request $request, string $slug) { return $this->vatSummary($request, $slug); }

    public function vatReturn(Request $request, string $slug) { return $this->vatSummary($request, $slug); }

    public function apAging(Request $request, string $slug)
    {
        $tenant  = app('tenant');
        $asOfDate = $request->get('as_of', date('Y-m-d'));

        $bills = \App\Models\Bill::with('supplier')
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['approved','partial'])
            ->where('due_date', '<=', $asOfDate)
            ->orderBy('due_date')
            ->get();

        return view('tenant.accounting.reports.ap-aging', compact('tenant', 'bills', 'asOfDate'));
    }

    public function apAgingPdf(Request $request, string $slug) { return $this->apAging($request, $slug); }
    public function apAgingCsv(Request $request, string $slug) { return $this->apAging($request, $slug); }

    public function arAging(Request $request, string $slug)
    {
        $tenant = app('tenant');
        return view('tenant.accounting.reports.ar-aging', compact('tenant'));
    }

    public function arAgingPdf(Request $request, string $slug) { return $this->arAging($request, $slug); }
    public function arAgingCsv(Request $request, string $slug) { return $this->arAging($request, $slug); }

    // ── Bullion / Client reports ──────────────────────────────────────────────

    public function clientBalances(Request $request, string $slug)
    {
        $tenant = app('tenant');
        return view('tenant.accounting.reports.client-balances', compact('tenant'));
    }

    public function clientBalancesPdf(Request $request, string $slug) { return $this->clientBalances($request, $slug); }
    public function clientBalancesCsv(Request $request, string $slug) { return $this->clientBalances($request, $slug); }

    public function clientStatement(Request $request, string $slug)
    {
        $tenant = app('tenant');
        return view('tenant.accounting.reports.client-statement', compact('tenant'));
    }

    public function clientStatementPdf(Request $request, string $slug) { return $this->clientStatement($request, $slug); }
    public function clientStatementCsv(Request $request, string $slug) { return $this->clientStatement($request, $slug); }
}
