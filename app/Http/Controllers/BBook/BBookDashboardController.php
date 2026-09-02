<?php
// app/Http/Controllers/BBook/BBookDashboardController.php

namespace App\Http\Controllers\BBook;

use App\Http\Controllers\Controller;
use App\Models\OtcDeal;
use App\Models\OtcChartOfAccount;
use App\Models\OtcJournalEntryLine;

class BBookDashboardController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        $recentDeals = OtcDeal::where('tenant_id', $tenant->id)
            ->orderByDesc('deal_date')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $stats = [
            'deals_total'    => OtcDeal::where('tenant_id', $tenant->id)->count(),
            'deals_draft'    => OtcDeal::where('tenant_id', $tenant->id)->where('status', 'draft')->count(),
            'deals_posted'   => OtcDeal::where('tenant_id', $tenant->id)->where('status', 'posted')->count(),
            'otc_volume_aed' => OtcDeal::where('tenant_id', $tenant->id)->where('status', 'posted')->sum('total'),
        ];

        // Quick B-Book net income (OTC-only revenue - expense), same math as the
        // existing ReportingService::incomeStatement, over the separate OTC ledger.
        $accounts = OtcChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $sums = OtcJournalEntryLine::query()
            ->join('otc_journal_entries', 'otc_journal_entries.id', '=', 'otc_journal_entry_lines.otc_journal_entry_id')
            ->where('otc_journal_entries.tenant_id', $tenant->id)
            ->where('otc_journal_entries.status', 'posted')
            ->groupBy('otc_journal_entry_lines.otc_chart_of_account_id')
            ->selectRaw('otc_journal_entry_lines.otc_chart_of_account_id as account_id, SUM(base_debit) as d, SUM(base_credit) as c')
            ->get()->keyBy('account_id');

        $otcIncome = 0.0;
        $otcExpense = 0.0;
        foreach ($accounts as $acct) {
            $row = $sums->get($acct->id);
            if (! $row) continue;
            $balance = $acct->normal_balance === 'debit' ? $row->d - $row->c : $row->c - $row->d;
            if ($acct->type === 'income') $otcIncome += $balance;
            if ($acct->type === 'expense') $otcExpense += $balance;
        }

        $stats['otc_net_income'] = round($otcIncome - $otcExpense, 2);

        return view('bbook.dashboard', compact('tenant', 'recentDeals', 'stats'));
    }
}
