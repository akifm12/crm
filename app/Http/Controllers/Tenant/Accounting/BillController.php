<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillLine;
use App\Models\Supplier;
use App\Models\ChartOfAccount;
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
            ->whereBetween('bill_date', [$dateFrom, $dateTo])
            ->orderByDesc('bill_date');

        if ($status !== 'all') {
            $query->where('status', strtolower($status));
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
            ->orderBy('name')
            ->get();

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderByRaw("FIELD(type,'ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE')")
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
        $dueDate     = $request->due_date ?? date('Y-m-d', strtotime('+30 days', strtotime($request->bill_date)));

        $count      = Bill::where('tenant_id', $tenant->id)->count() + 1;
        $billNumber = 'BILL-' . date('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($tenant, $request, $supplier, $lines, $subtotal, $totalVat, $totalAmount, $dueDate, $billNumber, $period) {
            $bill = Bill::create([
                'tenant_id'       => $tenant->id,
                'supplier_id'     => $request->supplier_id,
                'bill_number'     => $billNumber,
                'reference'       => $request->vendor_ref,
                'bill_date'       => $request->bill_date,
                'due_date'        => $dueDate,
                'status'          => 'draft',
                'supplier_name'   => $supplier->name,
                'supplier_vat_number' => $supplier->vat_number,
                'subtotal'        => $subtotal,
                'vat_total'       => $totalVat,
                'total'           => $totalAmount,
                'amount_paid'     => 0,
                'notes'           => $request->notes,
            ]);

            foreach ($lines as $i => $line) {
                BillLine::create([
                    'bill_id'     => $bill->id,
                    'description' => $line['description'],
                    'amount'      => $line['line_amount'],
                    'vat_rate'    => $line['vat_rate'],
                    'vat_amount'  => $line['vat_amount'],
                    'line_total'  => $line['total_amount'],
                    'line_order'  => $i + 1,
                ]);
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

        $suppliers = Supplier::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $accounts  = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)
            ->orderByRaw("FIELD(type,'ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE')")->orderBy('code')->get()->groupBy('account_type');
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
            $bill->update(['supplier_id' => $request->supplier_id, 'supplier_name' => $supplier->name, 'supplier_vat_number' => $supplier->vat_number, 'reference' => $request->vendor_ref, 'bill_date' => $request->bill_date, 'due_date' => $dueDate, 'subtotal' => $subtotal, 'vat_total' => $totalVat, 'total' => $totalAmount, 'notes' => $request->notes]);
            $bill->lines()->delete();
            foreach ($lines as $i => $line) {
                BillLine::create(['bill_id' => $bill->id, 'description' => $line['description'], 'amount' => $line['line_amount'], 'vat_rate' => $line['vat_rate'], 'vat_amount' => $line['vat_amount'], 'line_total' => $line['total_amount'], 'line_order' => $i + 1]);
            }
        });

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill])
            ->with('success', 'Bill updated.');
    }

    public function post(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        // Look for an AP account; fall back to cash (code 1050) if none exists
        $apAccount = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('type', 'LIABILITY')->where('is_active', true)->first()
            ?? ChartOfAccount::where('tenant_id', $tenant->id)->where('code', '1050')->first();
        if (!$apAccount) {
            return back()->with('error', 'No liability or cash account found. Please check Chart of Accounts.');
        }

        DB::transaction(function () use ($tenant, $bill, $apAccount) {
            $count    = JournalEntry::where('tenant_id', $tenant->id)->whereYear('entry_date', date('Y', strtotime($bill->bill_date)))->count() + 1;

            $je = JournalEntry::create([
                'tenant_id'     => $tenant->id,
                'entry_date'    => $bill->bill_date,
                'journal_type'  => 'GENERAL',
                'status'        => 'posted',
                'memo'          => "Bill {$bill->bill_number} - {$bill->supplier_name}",
                'reference'     => $bill->bill_number,
                'currency_code' => 'AED',
                'exchange_rate' => 1.0,
                'created_by'    => auth()->id(),
            ]);

            $lineNum = 1;
            foreach ($bill->lines as $line) {
                JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $apAccount->id, 'debit' => $line->getTotalAmountAttribute(), 'credit' => 0, 'description' => $line->description ?: $bill->bill_number, 'line_order' => $lineNum++]);
            }

            JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $apAccount->id, 'debit' => 0, 'credit' => $bill->total_amount, 'description' => "Payment - {$bill->supplier_name}", 'line_order' => $lineNum]);

            $bill->update(['status' => 'approved', 'journal_entry_id' => $je->id]);
        });

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill])
            ->with('success', 'Bill approved and journal entry created.');
    }

    public function void(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        $bill->update(['status' => 'void']);

        return redirect()->route('tenant.accounting.bills.index', $tenant->slug)
            ->with('success', 'Bill voided.');
    }

    public function storePayment(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        $request->validate(['payment_date' => 'required|date', 'amount' => 'required|numeric|min:0.01', 'gl_account_id' => 'required|integer']);

        $amount = floatval($request->amount);

        DB::transaction(function () use ($tenant, $bill, $amount, $request) {
            $je = JournalEntry::create([
                'tenant_id'     => $tenant->id,
                'entry_date'    => $request->payment_date,
                'journal_type'  => 'BANK_PAYMENT',
                'status'        => 'posted',
                'memo'          => "Payment to {$bill->supplier_name} - {$bill->bill_number}",
                'reference'     => $bill->bill_number,
                'currency_code' => 'AED',
                'exchange_rate' => 1.0,
                'created_by'    => auth()->id(),
            ]);
            JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $request->gl_account_id, 'debit' => $amount, 'credit' => 0, 'description' => "Payment to {$bill->supplier_name}", 'line_order' => 1]);
            JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $request->gl_account_id, 'debit' => 0, 'credit' => $amount, 'description' => "Bill cleared - {$bill->bill_number}", 'line_order' => 2]);

            $newPaid = $bill->amount_paid + $amount;
            $newDue  = round($bill->total_amount - $newPaid, 2);
            $newStatus = $newDue <= 0 ? 'paid' : 'partial';
            $bill->update(['amount_paid' => $newPaid, 'status' => $newStatus]);
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
        $bill->update(['status' => 'void']);
        return redirect()->route('tenant.accounting.bills.index', $tenant->slug)->with('success', 'Bill deleted.');
    }
}
