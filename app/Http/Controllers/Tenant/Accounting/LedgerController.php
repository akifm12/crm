<?php

// app/Http/Controllers/Tenant/Accounting/LedgerController.php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Http\Controllers\Controller;
use App\Models\BullionClient;
use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Services\Accounting\LedgerService;
use App\Services\Accounting\ReportingService;
use App\Support\CsvExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LedgerController extends Controller
{
    public function journalIndex()
    {
        $tenant = app('tenant');

        $entries = JournalEntry::where('tenant_id', $tenant->id)
            ->with('lines.account', 'client')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(25);

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('tenant.accounting.journal.index', compact('tenant', 'entries', 'accounts'));
    }

    public function journalShow(string $slug, JournalEntry $entry)
    {
        $tenant = app('tenant');
        abort_if($entry->tenant_id !== $tenant->id, 404);

        $entry->load('lines.account', 'creator', 'client');

        return view('tenant.accounting.journal.show', compact('tenant', 'entry'));
    }

    public function journalStore(Request $request, LedgerService $ledger)
    {
        $tenant = app('tenant');

        $request->validate([
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'memo' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        $lines = collect($request->lines)
            ->map(fn ($line) => [
                'chart_of_account_id' => $line['chart_of_account_id'],
                'debit' => (float) ($line['debit'] ?? 0),
                'credit' => (float) ($line['credit'] ?? 0),
                'description' => $line['description'] ?? null,
            ])
            ->all();

        try {
            $entry = $ledger->post($tenant, [
                'entry_date' => $request->entry_date,
                'reference' => $request->reference,
                'source_type' => 'manual',
                'memo' => $request->memo,
                'created_by' => auth()->id(),
            ], $lines);
        } catch (UnbalancedJournalEntryException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.accounting.journal.show', [$tenant->slug, $entry->id])
            ->with('success', 'Journal entry posted.');
    }

    public function journalVoid(Request $request, string $slug, JournalEntry $entry, LedgerService $ledger)
    {
        $tenant = app('tenant');
        abort_if($entry->tenant_id !== $tenant->id, 404);
        abort_if($entry->status !== 'posted', 422, 'Only posted entries can be voided.');

        $ledger->void($entry, $request->input('reason'));

        return redirect()->route('tenant.accounting.journal.index', $tenant->slug)
            ->with('success', 'Journal entry voided.');
    }

    public function trialBalance(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();

        $rows = $reporting->trialBalance($tenant, $asOf);
        $metalSummary = $reporting->metalInventorySummary($tenant);

        $totalDebit = round($rows->sum('debit'), 2);
        $totalCredit = round($rows->sum('credit'), 2);

        return view('tenant.accounting.reports.trial_balance', compact('tenant', 'rows', 'asOf', 'totalDebit', 'totalCredit', 'metalSummary'));
    }

    public function trialBalancePdf(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();

        $rows = $reporting->trialBalance($tenant, $asOf);
        $metalSummary = $reporting->metalInventorySummary($tenant);
        $totalDebit = round($rows->sum('debit'), 2);
        $totalCredit = round($rows->sum('credit'), 2);

        $pdf = Pdf::loadView('tenant.accounting.reports.pdf.trial_balance', compact('tenant', 'rows', 'asOf', 'totalDebit', 'totalCredit', 'metalSummary'));

        return $pdf->stream('trial-balance-'.$asOf->toDateString().'.pdf');
    }

    public function trialBalanceCsv(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();

        $rows = $reporting->trialBalance($tenant, $asOf)->filter(fn ($row) => $row['debit'] != 0 || $row['credit'] != 0);

        $csvRows = $rows->map(fn ($row) => [$row['account']->code, $row['account']->name, $row['debit'], $row['credit']])->all();
        $csvRows[] = ['', 'Totals', round($rows->sum('debit'), 2), round($rows->sum('credit'), 2)];

        return CsvExport::download('trial-balance-'.$asOf->toDateString().'.csv', ['Code', 'Account', 'Debit (AED)', 'Credit (AED)'], $csvRows);
    }

    public function balanceSheet(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();

        $report = $reporting->balanceSheet($tenant, $asOf);
        $metalSummary = $reporting->metalInventorySummary($tenant);

        return view('tenant.accounting.reports.balance_sheet', array_merge(compact('tenant', 'asOf', 'metalSummary'), $report));
    }

    public function balanceSheetPdf(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();

        $report = $reporting->balanceSheet($tenant, $asOf);
        $metalSummary = $reporting->metalInventorySummary($tenant);

        $pdf = Pdf::loadView('tenant.accounting.reports.pdf.balance_sheet', array_merge(compact('tenant', 'asOf', 'metalSummary'), $report));

        return $pdf->stream('balance-sheet-'.$asOf->toDateString().'.pdf');
    }

    public function balanceSheetCsv(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();

        $report = $reporting->balanceSheet($tenant, $asOf);

        $rows = [];
        foreach ($report['assets'] as $row) {
            $rows[] = ['Assets', $row['account']->name, $row['balance']];
        }
        $rows[] = ['Assets', 'Total Assets', $report['totalAssets']];
        foreach ($report['liabilities'] as $row) {
            $rows[] = ['Liabilities', $row['account']->name, $row['balance']];
        }
        $rows[] = ['Liabilities', 'Total Liabilities', $report['totalLiabilities']];
        foreach ($report['equity'] as $row) {
            $rows[] = ['Equity', $row['account']->name, $row['balance']];
        }
        $rows[] = ['Equity', 'Current Period Earnings', $report['netIncome']];
        $rows[] = ['Equity', 'Total Equity', round($report['totalEquity'] + $report['netIncome'], 2)];

        return CsvExport::download('balance-sheet-'.$asOf->toDateString().'.csv', ['Section', 'Account', 'Balance (AED)'], $rows);
    }

    public function incomeStatement(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfYear();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::today();

        $report = $reporting->incomeStatement($tenant, $from, $to);

        return view('tenant.accounting.reports.income_statement', array_merge(compact('tenant', 'from', 'to'), $report));
    }

    public function incomeStatementPdf(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfYear();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::today();

        $report = $reporting->incomeStatement($tenant, $from, $to);

        $pdf = Pdf::loadView('tenant.accounting.reports.pdf.income_statement', array_merge(compact('tenant', 'from', 'to'), $report));

        return $pdf->stream('income-statement-'.$from->toDateString().'-to-'.$to->toDateString().'.pdf');
    }

    public function incomeStatementCsv(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfYear();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::today();

        $report = $reporting->incomeStatement($tenant, $from, $to);

        $rows = [];
        foreach ($report['income'] as $row) {
            $rows[] = ['Income', $row['account']->name, $row['balance']];
        }
        $rows[] = ['Income', 'Total Income', $report['totalIncome']];
        foreach ($report['expense'] as $row) {
            $rows[] = ['Expense', $row['account']->name, $row['balance']];
        }
        $rows[] = ['Expense', 'Total Expenses', $report['totalExpense']];
        $rows[] = ['', 'Net '.($report['netIncome'] >= 0 ? 'Profit' : 'Loss'), abs($report['netIncome'])];

        return CsvExport::download('income-statement-'.$from->toDateString().'-to-'.$to->toDateString().'.csv', ['Section', 'Account', 'Amount (AED)'], $rows);
    }

    public function vatSummary(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfQuarter();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::today();

        $rows = $reporting->vatSummary($tenant, $from, $to);
        $totalSubtotal = round($rows->sum('subtotal'), 2);
        $totalVat = round($rows->sum('vat'), 2);

        return view('tenant.accounting.reports.vat_summary', compact('tenant', 'from', 'to', 'rows', 'totalSubtotal', 'totalVat'));
    }

    public function vatSummaryPdf(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfQuarter();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::today();

        $rows = $reporting->vatSummary($tenant, $from, $to);
        $totalSubtotal = round($rows->sum('subtotal'), 2);
        $totalVat = round($rows->sum('vat'), 2);

        $pdf = Pdf::loadView('tenant.accounting.reports.pdf.vat_summary', compact('tenant', 'from', 'to', 'rows', 'totalSubtotal', 'totalVat'));

        return $pdf->stream('vat-summary-'.$from->toDateString().'-to-'.$to->toDateString().'.pdf');
    }

    public function vatSummaryCsv(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::now()->startOfQuarter();
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::today();

        $rows = $reporting->vatSummary($tenant, $from, $to);

        $csvRows = $rows->map(fn ($row) => [ucfirst(str_replace('_', ' ', $row['vat_treatment'])), $row['subtotal'], $row['vat']])->all();
        $csvRows[] = ['Totals', round($rows->sum('subtotal'), 2), round($rows->sum('vat'), 2)];

        return CsvExport::download('vat-summary-'.$from->toDateString().'-to-'.$to->toDateString().'.csv', ['VAT Treatment', 'Subtotal (AED)', 'VAT (AED)'], $csvRows);
    }

    public function clientStatement(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');

        $clients = BullionClient::where('tenant_id', $tenant->id)->orderBy('company_name')->orderBy('full_name')->get();

        $client = null;
        $statement = null;
        $clientInvoices = collect();
        $unlinkedDeposits = collect();
        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();

        $metalStatement = null;

        if ($request->filled('client_id')) {
            $client = BullionClient::findOrFail($request->input('client_id'));
            abort_if($client->tenant_id !== $tenant->id, 404);
            $statement = $reporting->clientStatement($tenant, $client, $asOf);
            $metalStatement = $reporting->clientMetalStatement($tenant, $client, $asOf);
            $clientInvoices = Invoice::where('tenant_id', $tenant->id)
                ->where('bullion_client_id', $client->id)
                ->where('status', '!=', 'void')
                ->orderByDesc('invoice_date')
                ->get();
            $unlinkedDeposits = InvoicePayment::where('tenant_id', $tenant->id)
                ->where('bullion_client_id', $client->id)
                ->whereNull('invoice_id')
                ->orderByDesc('payment_date')
                ->get();
        }

        return view('tenant.accounting.reports.client_statement', compact('tenant', 'clients', 'client', 'statement', 'metalStatement', 'asOf', 'clientInvoices', 'unlinkedDeposits'));
    }

    public function clientStatementPdf(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $client = BullionClient::findOrFail($request->input('client_id'));
        abort_if($client->tenant_id !== $tenant->id, 404);

        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();
        $statement = $reporting->clientStatement($tenant, $client, $asOf);
        $metalStatement = $reporting->clientMetalStatement($tenant, $client, $asOf);

        $pdf = Pdf::loadView('tenant.accounting.reports.pdf.client_statement', compact('tenant', 'client', 'statement', 'metalStatement', 'asOf'));

        return $pdf->stream('statement-'.str($client->displayName())->slug().'-'.$asOf->toDateString().'.pdf');
    }

    public function clientStatementCsv(Request $request, ReportingService $reporting)
    {
        $tenant = app('tenant');
        $client = BullionClient::findOrFail($request->input('client_id'));
        abort_if($client->tenant_id !== $tenant->id, 404);

        $asOf = $request->filled('as_of') ? Carbon::parse($request->input('as_of')) : Carbon::today();
        $statement = $reporting->clientStatement($tenant, $client, $asOf);
        $metalStatement = $reporting->clientMetalStatement($tenant, $client, $asOf);

        // Build preamble: company identity + client details header block
        $preamble = [[$tenant->name]];
        if ($tenant->trn_number) {
            $preamble[] = ['TRN: '.$tenant->trn_number];
        }
        if ($tenant->address) {
            $preamble[] = [$tenant->address];
        }
        $preamble[] = [];
        $preamble[] = ['STATEMENT OF ACCOUNTS'];
        $preamble[] = ['Client', $client->displayName()];
        if ($client->trn_number) {
            $preamble[] = ['Client TRN', $client->trn_number];
        }
        $preamble[] = ['As of', $asOf->format('d M Y')];
        $balanceLabel = $statement['balance'] > 0 ? 'Client owes the business'
            : ($statement['balance'] < 0 ? 'Business owes the client' : 'Settled');
        $preamble[] = ['Net Balance (AED)', number_format(abs($statement['balance']), 2), $balanceLabel];
        $preamble[] = ['Generated', now()->format('d M Y H:i')];
        $preamble[] = [];

        // Cash movement rows
        $rows = collect($statement['rows'])->map(fn ($row) => [
            $row['date']->format('d M Y'),
            $row['description'],
            number_format($row['amount'], 2),
            number_format($row['balance'], 2),
        ])->all();

        // Append metal movement section if present
        if ($metalStatement['rows']->isNotEmpty()) {
            $rows[] = [];
            $rows[] = ['METAL MOVEMENT LEDGER'];
            $rows[] = ['Date', 'Invoice', 'Description', 'Metal', 'Purity', 'In (g)', 'Out (g)', 'Balance (g)'];
            foreach ($metalStatement['rows'] as $mr) {
                $rows[] = [
                    $mr['date']->format('d M Y'),
                    $mr['invoice_number'],
                    $mr['description'] ?: ucfirst($mr['invoice_type']),
                    strtoupper($mr['metal_type']),
                    $mr['purity'] ?: '',
                    $mr['grams_in'] !== null ? number_format($mr['grams_in'], 3) : '',
                    $mr['grams_out'] !== null ? number_format($mr['grams_out'], 3) : '',
                    number_format($mr['balance'], 3),
                ];
            }
            foreach ($metalStatement['metals'] as $metal) {
                $rows[] = [
                    '', '', '', '', ucfirst($metal).' balance held on account',
                    '', '', number_format($metalStatement['balances'][$metal] ?? 0, 3).' g',
                ];
            }
        }

        return CsvExport::download(
            'statement-'.str($client->displayName())->slug().'-'.$asOf->toDateString().'.csv',
            ['Date', 'Description', 'Amount (AED)', 'Balance (AED)'],
            $rows,
            $preamble
        );
    }

    public function clientBalances(ReportingService $reporting)
    {
        $tenant = app('tenant');

        $summary = $reporting->clientBalancesSummary($tenant);

        return view('tenant.accounting.reports.client_balances', array_merge(compact('tenant'), $summary));
    }

    public function clientBalancesPdf(ReportingService $reporting)
    {
        $tenant = app('tenant');

        $summary = $reporting->clientBalancesSummary($tenant);

        $pdf = Pdf::loadView('tenant.accounting.reports.pdf.client_balances', array_merge(compact('tenant'), $summary));

        return $pdf->stream('client-balances-'.now()->toDateString().'.pdf');
    }

    public function clientBalancesCsv(ReportingService $reporting)
    {
        $tenant = app('tenant');

        $summary = $reporting->clientBalancesSummary($tenant);

        $rows = $summary['corporate']->map(fn ($row) => [
            $row['client']->displayName(), ucfirst(str_replace('_', ' ', $row['client']->client_type)), $row['balance'], $row['sales_revenue'],
        ])->all();
        if ($summary['individuals']['balance'] != 0 || $summary['individuals']['sales_revenue'] != 0) {
            $rows[] = ['Individuals (combined)', '', $summary['individuals']['balance'], $summary['individuals']['sales_revenue']];
        }
        $rows[] = ['Totals', '', round($summary['corporate']->sum('balance') + $summary['individuals']['balance'], 2), round($summary['corporate']->sum('sales_revenue') + $summary['individuals']['sales_revenue'], 2)];

        return CsvExport::download('client-balances-'.now()->toDateString().'.csv', ['Client', 'Type', 'Balance (AED)', 'Sales Revenue (AED)'], $rows);
    }
}
