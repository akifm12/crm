<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillLine;
use App\Models\Supplier;
use App\Models\ChartOfAccount;
use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $tenant   = app('tenant');
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo   = $request->get('date_to', date('Y-m-d'));
        $status   = $request->get('status', 'all');

        $query = Bill::with('supplier')
            ->where('tenant_id', $tenant->id)
            ->where('is_deleted', false)
            ->whereBetween('bill_date', [$dateFrom, $dateTo])
            ->orderByDesc('bill_date');

        if ($status !== 'all') {
            $query->where('status', strtoupper($status));
        }

        $bills       = $query->get();
        $totalAmount = $bills->sum('total_amount');
        $totalDue    = $bills->sum('amount_due');

        return view('tenant.accounting.bills.index', compact('tenant', 'bills', 'dateFrom', 'dateTo', 'status', 'totalAmount', 'totalDue'));
    }

    public function create(Request $request, string $slug)
    {
        $tenant    = app('tenant');
        $suppliers = Supplier::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get();

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderByRaw("FIELD(account_type,'ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE')")
            ->orderBy('code')
            ->get()
            ->groupBy('account_type');

        $selectedSupplier = null;
        if ($request->get('supplier_id')) {
            $selectedSupplier = Supplier::find($request->get('supplier_id'));
        }

        return view('tenant.accounting.bills.create', compact('tenant', 'suppliers', 'accounts', 'selectedSupplier'));
    }

    public function store(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate([
            'supplier_id' => 'required|integer',
            'bill_date'   => 'required|date',
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);
        abort_if($supplier->tenant_id !== $tenant->id, 403);

        $subtotal = 0;
        $totalVat = 0;
        $lines    = [];

        foreach ($request->accounts ?? [] as $i => $accountId) {
            if (!$accountId) continue;
            $qty        = floatval($request->quantities[$i] ?? 1);
            $price      = floatval($request->unit_prices[$i] ?? 0);
            $vatRate    = floatval($request->vat_rates[$i] ?? 0);
            $lineAmount = $qty * $price;
            $vatAmount  = $lineAmount * ($vatRate / 100);
            $subtotal  += $lineAmount;
            $totalVat  += $vatAmount;
            $lines[]    = [
                'account_id'  => $accountId,
                'description' => $request->descriptions[$i] ?? '',
                'quantity'    => $qty,
                'unit_price'  => $price,
                'line_amount' => $lineAmount,
                'vat_rate'    => $vatRate,
                'vat_amount'  => $vatAmount,
                'total_amount'=> $lineAmount + $vatAmount,
            ];
        }

        $totalAmount = $subtotal + $totalVat;
        $dueDate     = $request->due_date ?? date('Y-m-d', strtotime('+' . ($supplier->payment_terms_days ?? 30) . ' days', strtotime($request->bill_date)));

        $period = AccountingPeriod::where('tenant_id', $tenant->id)
            ->where('start_date', '<=', $request->bill_date)
            ->where('end_date', '>=', $request->bill_date)
            ->first();

        if (!$period) {
            return back()->withInput()->with('error', 'No accounting period found for this date. Please create one first.');
        }

        $count      = Bill::where('tenant_id', $tenant->id)->count() + 1;
        $billNumber = 'BILL-' . date('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($tenant, $request, $supplier, $lines, $subtotal, $totalVat, $totalAmount, $dueDate, $billNumber, $period) {
            $bill = Bill::create([
                'tenant_id'       => $tenant->id,
                'supplier_id'     => $request->supplier_id,
                'period_id'       => $period->id,
                'bill_number'     => $billNumber,
                'vendor_ref'      => $request->vendor_ref,
                'bill_type'       => 'TAX_INVOICE',
                'bill_date'       => $request->bill_date,
                'due_date'        => $dueDate,
                'status'          => 'DRAFT',
                'currency_code'   => 'AED',
                'exchange_rate'   => 1.0,
                'vendor_name'     => $supplier->name,
                'vendor_trn'      => $supplier->trn,
                'subtotal'        => $subtotal,
                'discount_amount' => 0,
                'total_vat_amount'=> $totalVat,
                'total_amount'    => $totalAmount,
                'amount_paid'     => 0,
                'amount_due'      => $totalAmount,
                'is_deleted'      => false,
                'notes'           => $request->notes,
            ]);

            foreach ($lines as $i => $line) {
                BillLine::create(array_merge($line, [
                    'tenant_id'        => $tenant->id,
                    'bill_id'          => $bill->id,
                    'line_number'      => $i + 1,
                    'is_reverse_charge'=> false,
                ]));
            }
        });

        return redirect()->route('tenant.accounting.bills.index', $tenant->slug)
            ->with('success', "Bill {$billNumber} created.");
    }

    public function show(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        $lines = $bill->lines()->with('account')->orderBy('line_number')->get();

        return view('tenant.accounting.bills.show', compact('tenant', 'bill', 'lines'));
    }

    public function edit(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        if ($bill->status !== 'DRAFT') {
            return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill])
                ->with('error', 'Only draft bills can be edited.');
        }

        $suppliers = Supplier::where('tenant_id', $tenant->id)->where('is_active', true)->where('is_deleted', false)->orderBy('name')->get();
        $accounts  = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)
            ->orderByRaw("FIELD(account_type,'ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE')")->orderBy('code')->get()->groupBy('account_type');
        $lines     = $bill->lines()->orderBy('line_number')->get();

        return view('tenant.accounting.bills.edit', compact('tenant', 'bill', 'suppliers', 'accounts', 'lines'));
    }

    public function update(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        if ($bill->status !== 'DRAFT') {
            return back()->with('error', 'Only draft bills can be edited.');
        }

        $supplier = Supplier::findOrFail($request->supplier_id);
        $subtotal = 0;
        $totalVat = 0;
        $lines    = [];

        foreach ($request->accounts ?? [] as $i => $accountId) {
            if (!$accountId) continue;
            $qty        = floatval($request->quantities[$i] ?? 1);
            $price      = floatval($request->unit_prices[$i] ?? 0);
            $vatRate    = floatval($request->vat_rates[$i] ?? 0);
            $lineAmount = $qty * $price;
            $vatAmount  = $lineAmount * ($vatRate / 100);
            $subtotal  += $lineAmount;
            $totalVat  += $vatAmount;
            $lines[]    = ['account_id' => $accountId, 'description' => $request->descriptions[$i] ?? '', 'quantity' => $qty, 'unit_price' => $price, 'line_amount' => $lineAmount, 'vat_rate' => $vatRate, 'vat_amount' => $vatAmount, 'total_amount' => $lineAmount + $vatAmount];
        }

        $totalAmount = $subtotal + $totalVat;
        $dueDate     = $request->due_date ?? date('Y-m-d', strtotime('+' . ($supplier->payment_terms_days ?? 30) . ' days', strtotime($request->bill_date)));

        DB::transaction(function () use ($tenant, $bill, $request, $supplier, $lines, $subtotal, $totalVat, $totalAmount, $dueDate) {
            $bill->update(['supplier_id' => $request->supplier_id, 'vendor_name' => $supplier->name, 'vendor_trn' => $supplier->trn, 'vendor_ref' => $request->vendor_ref, 'bill_date' => $request->bill_date, 'due_date' => $dueDate, 'subtotal' => $subtotal, 'total_vat_amount' => $totalVat, 'total_amount' => $totalAmount, 'amount_due' => $totalAmount, 'notes' => $request->notes]);
            $bill->lines()->delete();
            foreach ($lines as $i => $line) {
                BillLine::create(array_merge($line, ['tenant_id' => $tenant->id, 'bill_id' => $bill->id, 'line_number' => $i + 1, 'is_reverse_charge' => false]));
            }
        });

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill])
            ->with('success', 'Bill updated.');
    }

    public function post(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        $apAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_payable', true)->first();
        if (!$apAccount) {
            return back()->with('error', 'No Accounts Payable account found. Create one in Chart of Accounts first.');
        }

        $period = AccountingPeriod::where('tenant_id', $tenant->id)
            ->where('start_date', '<=', $bill->bill_date)
            ->where('end_date', '>=', $bill->bill_date)
            ->first();

        if (!$period) {
            return back()->with('error', 'No accounting period found for this bill date.');
        }

        DB::transaction(function () use ($tenant, $bill, $period, $apAccount) {
            $count    = JournalEntry::where('tenant_id', $tenant->id)->whereYear('entry_date', date('Y', strtotime($bill->bill_date)))->count() + 1;
            $jeNumber = 'JE-' . date('Y', strtotime($bill->bill_date)) . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

            $je = JournalEntry::create([
                'tenant_id'     => $tenant->id,
                'period_id'     => $period->id,
                'entry_number'  => $jeNumber,
                'entry_date'    => $bill->bill_date,
                'journal_type'  => 'GENERAL',
                'status'        => 'POSTED',
                'description'   => "Bill {$bill->bill_number} - {$bill->vendor_name}",
                'reference'     => $bill->bill_number,
                'total_debit'   => $bill->total_amount,
                'total_credit'  => $bill->total_amount,
                'currency_code' => 'AED',
                'exchange_rate' => 1.0,
                'is_reversal'   => false,
                'is_recurring'  => false,
                'is_deleted'    => false,
                'created_by_id' => auth()->id(),
            ]);

            $lineNum = 1;
            foreach ($bill->lines as $line) {
                if (!$line->account_id) continue;
                JournalLine::create(['tenant_id' => $tenant->id, 'journal_entry_id' => $je->id, 'account_id' => $line->account_id, 'line_number' => $lineNum++, 'description' => $line->description, 'debit_amount' => $line->line_amount, 'credit_amount' => 0, 'currency_code' => 'AED', 'exchange_rate' => 1.0, 'is_reconciled' => false]);
            }

            JournalLine::create(['tenant_id' => $tenant->id, 'journal_entry_id' => $je->id, 'account_id' => $apAccount->id, 'line_number' => $lineNum, 'description' => "AP - {$bill->vendor_name}", 'debit_amount' => 0, 'credit_amount' => $bill->total_amount, 'currency_code' => 'AED', 'exchange_rate' => 1.0, 'is_reconciled' => false]);

            $bill->update(['status' => 'APPROVED', 'journal_entry_id' => $je->id]);
        });

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill])
            ->with('success', 'Bill approved and journal entry created.');
    }

    public function void(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        $bill->update(['status' => 'VOID', 'is_deleted' => true]);

        return redirect()->route('tenant.accounting.bills.index', $tenant->slug)
            ->with('success', 'Bill voided.');
    }

    public function storePayment(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        $request->validate(['payment_date' => 'required|date', 'amount' => 'required|numeric|min:0.01', 'gl_account_id' => 'required|integer']);

        $apAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_payable', true)->first();
        if (!$apAccount) {
            return back()->with('error', 'No Accounts Payable account found.');
        }

        $period = AccountingPeriod::where('tenant_id', $tenant->id)
            ->where('start_date', '<=', $request->payment_date)
            ->where('end_date', '>=', $request->payment_date)
            ->first();

        if (!$period) {
            return back()->with('error', 'No accounting period found for the payment date.');
        }

        $amount = floatval($request->amount);

        DB::transaction(function () use ($tenant, $bill, $amount, $request, $period, $apAccount) {
            $count    = JournalEntry::where('tenant_id', $tenant->id)->whereYear('entry_date', date('Y', strtotime($request->payment_date)))->count() + 1;
            $jeNumber = 'JE-' . date('Y', strtotime($request->payment_date)) . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

            $je = JournalEntry::create(['tenant_id' => $tenant->id, 'period_id' => $period->id, 'entry_number' => $jeNumber, 'entry_date' => $request->payment_date, 'journal_type' => 'BANK_PAYMENT', 'status' => 'POSTED', 'description' => "Payment to {$bill->vendor_name} - {$bill->bill_number}", 'reference' => $bill->bill_number, 'total_debit' => $amount, 'total_credit' => $amount, 'currency_code' => 'AED', 'exchange_rate' => 1.0, 'is_reversal' => false, 'is_recurring' => false, 'is_deleted' => false, 'created_by_id' => auth()->id()]);
            JournalLine::create(['tenant_id' => $tenant->id, 'journal_entry_id' => $je->id, 'account_id' => $apAccount->id, 'line_number' => 1, 'description' => "AP cleared - {$bill->vendor_name}", 'debit_amount' => $amount, 'credit_amount' => 0, 'currency_code' => 'AED', 'exchange_rate' => 1.0, 'is_reconciled' => false]);
            JournalLine::create(['tenant_id' => $tenant->id, 'journal_entry_id' => $je->id, 'account_id' => $request->gl_account_id, 'line_number' => 2, 'description' => "Payment to {$bill->vendor_name}", 'debit_amount' => 0, 'credit_amount' => $amount, 'currency_code' => 'AED', 'exchange_rate' => 1.0, 'is_reconciled' => false]);

            $newPaid = $bill->amount_paid + $amount;
            $newDue  = round($bill->total_amount - $newPaid, 2);
            $bill->update(['amount_paid' => $newPaid, 'amount_due' => max(0, $newDue), 'status' => $newDue <= 0 ? 'PAID' : 'PARTIAL']);
        });

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill])
            ->with('success', 'Payment recorded.');
    }

    public function pdf(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        $lines = $bill->lines()->with('account')->orderBy('line_number')->get();
        return view('tenant.accounting.bills.pdf', compact('tenant', 'bill', 'lines'));
    }

    public function uploadAttachment(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        $request->validate(['attachment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);
        $path = $request->file('attachment')->store("bills/{$tenant->id}", 'private');
        $bill->update(['attachment_path' => $path]);
        return back()->with('success', 'Attachment uploaded.');
    }

    public function downloadAttachment(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        abort_if(!$bill->attachment_path, 404);
        return response()->download(storage_path('app/private/' . $bill->attachment_path));
    }

    public function destroy(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        if ($bill->status !== 'DRAFT') {
            return back()->with('error', 'Only draft bills can be deleted.');
        }
        $bill->update(['is_deleted' => true, 'status' => 'VOID']);
        return redirect()->route('tenant.accounting.bills.index', $tenant->slug)->with('success', 'Bill deleted.');
    }
}
