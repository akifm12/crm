<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DataController extends Controller
{
    public function index(string $slug)
    {
        $tenant = app('tenant');
        return view('tenant.accounting.data.index', compact('tenant'));
    }

    public function exportJournals(string $slug)
    {
        $tenant  = app('tenant');
        $entries = JournalEntry::where('tenant_id', $tenant->id)
            ->with('lines.account')
            ->orderBy('entry_date')
            ->get();

        $rows   = [['Date', 'Reference', 'Memo', 'Account Code', 'Account Name', 'Debit', 'Credit']];
        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                $rows[] = [
                    $entry->entry_date,
                    $entry->reference,
                    $entry->description,
                    $line->account?->code,
                    $line->account?->name,
                    $line->debit_amount,
                    $line->credit_amount,
                ];
            }
        }

        return $this->csvResponse($rows, "journals_{$tenant->slug}.csv");
    }

    public function exportAccounts(string $slug)
    {
        $tenant   = app('tenant');
        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->orderBy('code')
            ->get(['code', 'name', 'account_type', 'is_active']);

        $rows = [['Code', 'Name', 'Type', 'Active']];
        foreach ($accounts as $a) {
            $rows[] = [$a->code, $a->name, $a->account_type, $a->is_active ? 'Yes' : 'No'];
        }

        return $this->csvResponse($rows, "accounts_{$tenant->slug}.csv");
    }

    public function exportSuppliers(string $slug)
    {
        $tenant    = app('tenant');
        $suppliers = Supplier::where('tenant_id', $tenant->id)->orderBy('name')->get();

        $rows = [['Name', 'TRN', 'Email', 'Phone', 'Active']];
        foreach ($suppliers as $s) {
            $rows[] = [$s->name, $s->trn ?? '', $s->email ?? '', $s->phone ?? '', $s->is_active ? 'Yes' : 'No'];
        }

        return $this->csvResponse($rows, "suppliers_{$tenant->slug}.csv");
    }

    public function exportBills(string $slug)
    {
        $tenant = app('tenant');
        $bills  = Bill::where('tenant_id', $tenant->id)
            ->orderBy('bill_date')
            ->get(['bill_number', 'vendor_name', 'bill_date', 'due_date', 'total_amount', 'status']);

        $rows = [['Bill #', 'Supplier', 'Date', 'Due', 'Total', 'Status']];
        foreach ($bills as $b) {
            $rows[] = [$b->bill_number, $b->vendor_name, $b->bill_date, $b->due_date, $b->total_amount, $b->status];
        }

        return $this->csvResponse($rows, "bills_{$tenant->slug}.csv");
    }

    private function csvResponse(array $rows, string $filename)
    {
        $output = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return Response::make($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
