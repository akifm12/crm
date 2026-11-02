<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InventoryItem;
use App\Models\BullionClient;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $tenant   = app('tenant');
        $dateFrom = $request->get('date_from', date('Y-01-01'));
        $dateTo   = $request->get('date_to', date('Y-m-d'));
        $status   = $request->get('status', 'all');
        $search   = $request->get('search');

        $query = Invoice::where('tenant_id', $tenant->id)
            ->where('is_deleted', false)
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->orderByDesc('invoice_date')->orderByDesc('id');

        if ($status !== 'all') {
            $query->where('status', strtolower($status));
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $invoices    = $query->get();
        $totalAmount = $invoices->sum('total_amount');
        $totalDue    = $invoices->sum('amount_due');

        return view('tenant.accounting.invoice.index', compact('tenant', 'invoices', 'dateFrom', 'dateTo', 'status', 'search', 'totalAmount', 'totalDue'));
    }

    public function create(Request $request, string $slug)
    {
        $tenant  = app('tenant');
        $clients = BullionClient::where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('company_name')->orderBy('full_name')->get();
        $items   = InventoryItem::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)->whereIn('type', ['REVENUE', 'ASSET'])->orderBy('code')->get();

        $selectedClient = null;
        if ($cid = $request->get('client_id')) {
            $selectedClient = BullionClient::find($cid);
        }

        return view('tenant.accounting.invoice.create', compact('tenant', 'clients', 'items', 'accounts', 'selectedClient'));
    }

    public function store(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate([
            'client_id'    => 'required|integer',
            'invoice_date' => 'required|date',
        ]);

        $client = BullionClient::findOrFail($request->client_id);

        [$lines, $subtotal, $totalVat] = $this->buildLines($request);

        if (empty($lines)) {
            return back()->withInput()->with('error', 'At least one line item is required.');
        }

        $totalAmount  = $subtotal - floatval($request->discount_amount ?? 0) + $totalVat;
        $invoiceNumber = Invoice::nextNumber($tenant->id, $request->invoice_date);
        $dueDate       = $request->due_date ?: date('Y-m-d', strtotime('+30 days', strtotime($request->invoice_date)));

        DB::transaction(function () use ($tenant, $request, $client, $lines, $subtotal, $totalVat, $totalAmount, $invoiceNumber, $dueDate) {
            $invoice = Invoice::create([
                'tenant_id'       => $tenant->id,
                'client_id'       => $client->id,
                'invoice_number'  => $invoiceNumber,
                'invoice_date'    => $request->invoice_date,
                'due_date'        => $dueDate,
                'status'          => 'draft',
                'customer_name'   => $client->displayName(),
                'customer_trn'    => $client->trn_number,
                'customer_address'=> $client->registered_address ?? $client->operating_address,
                'subtotal'        => $subtotal,
                'discount_amount' => floatval($request->discount_amount ?? 0),
                'vat_amount'      => $totalVat,
                'total_amount'    => $totalAmount,
                'amount_due'      => $totalAmount,
                'currency_code'   => 'AED',
                'exchange_rate'   => 1.0,
                'po_number'       => $request->po_number,
                'notes'           => $request->notes,
                'created_by'      => auth()->id(),
            ]);

            foreach ($lines as $i => $line) {
                InvoiceLine::create(array_merge($line, ['invoice_id' => $invoice->id, 'line_order' => $i + 1]));
            }
        });

        return redirect()->route('tenant.accounting.invoices.index', $tenant->slug)
            ->with('success', "Invoice {$invoiceNumber} created.");
    }

    public function show(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id || $invoice->is_deleted, 404);
        $lines = $invoice->lines()->with('item', 'account')->get();
        return view('tenant.accounting.invoice.show', compact('tenant', 'invoice', 'lines'));
    }

    public function edit(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        if ($invoice->STATUS !== 'DRAFT') {
            return redirect()->route('tenant.accounting.invoices.show', [$tenant->slug, $invoice])->with('error', 'Only draft invoices can be edited.');
        }
        $clients  = BullionClient::where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('company_name')->orderBy('full_name')->get();
        $items    = InventoryItem::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)->whereIn('type', ['REVENUE', 'ASSET'])->orderBy('code')->get();
        $lines    = $invoice->lines()->get();
        return view('tenant.accounting.invoice.edit', compact('tenant', 'invoice', 'clients', 'items', 'accounts', 'lines'));
    }

    public function update(Request $request, string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        if ($invoice->STATUS !== 'DRAFT') {
            return back()->with('error', 'Only draft invoices can be edited.');
        }

        $client = BullionClient::findOrFail($request->client_id);
        [$lines, $subtotal, $totalVat] = $this->buildLines($request);
        $totalAmount = $subtotal - floatval($request->discount_amount ?? 0) + $totalVat;

        DB::transaction(function () use ($invoice, $request, $client, $lines, $subtotal, $totalVat, $totalAmount) {
            $invoice->update([
                'client_id'       => $client->id,
                'invoice_date'    => $request->invoice_date,
                'due_date'        => $request->due_date ?: $invoice->due_date,
                'customer_name'   => $client->displayName(),
                'customer_trn'    => $client->trn_number,
                'customer_address'=> $client->registered_address ?? $client->operating_address,
                'subtotal'        => $subtotal,
                'discount_amount' => floatval($request->discount_amount ?? 0),
                'vat_amount'      => $totalVat,
                'total_amount'    => $totalAmount,
                'amount_due'      => $totalAmount,
                'po_number'       => $request->po_number,
                'notes'           => $request->notes,
            ]);

            $invoice->lines()->delete();
            foreach ($lines as $i => $line) {
                InvoiceLine::create(array_merge($line, ['invoice_id' => $invoice->id, 'line_order' => $i + 1]));
            }
        });

        return redirect()->route('tenant.accounting.invoices.show', [app('tenant')->slug, $invoice])
            ->with('success', 'Invoice updated.');
    }

    public function destroy(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $invoice->update(['is_deleted' => true]);
        return redirect()->route('tenant.accounting.invoices.index', $tenant->slug)->with('success', 'Invoice deleted.');
    }

    public function post(Request $request, string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        // AR account: prefer ASSET type with "receivable" in name, fall back to first ASSET
        $arAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)
            ->where('type', 'ASSET')
            ->where(function ($q) {
                $q->where('name', 'like', '%Receivable%')->orWhere('name', 'like', '%AR%');
            })->first()
            ?? ChartOfAccount::where('tenant_id', $tenant->id)->where('type', 'ASSET')->where('is_active', true)->first();

        if (!$arAccount) {
            return back()->with('error', 'No Accounts Receivable account found.');
        }

        // VAT Output Tax account
        $vatAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%VAT%Output%')
                  ->orWhere('name', 'like', '%Output%VAT%')
                  ->orWhere('name', 'like', '%VAT%Payable%');
            })->first();

        // Default revenue account fallback
        $defaultRevenue = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('type', 'REVENUE')->where('is_active', true)->orderBy('code')->first();

        DB::transaction(function () use ($tenant, $invoice, $arAccount, $vatAccount, $defaultRevenue) {
            $je = JournalEntry::create([
                'tenant_id'     => $tenant->id,
                'entry_date'    => $invoice->invoice_date,
                'journal_type'  => 'GENERAL',
                'status'        => 'posted',
                'memo'          => "Invoice {$invoice->invoice_number} - {$invoice->customer_name}",
                'reference'     => $invoice->invoice_number,
                'currency_code' => 'AED',
                'exchange_rate' => 1.0,
                'created_by'    => auth()->id(),
            ]);

            $lineNum = 1;

            // DR Accounts Receivable (full amount incl. VAT)
            JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $arAccount->id, 'debit' => $invoice->total_amount, 'credit' => 0, 'description' => "AR - {$invoice->customer_name}", 'line_order' => $lineNum++]);

            // CR Revenue per line (net amount)
            foreach ($invoice->lines as $line) {
                $revenueAccount = null;
                if ($line->chart_of_account_id) {
                    $revenueAccount = ChartOfAccount::find($line->chart_of_account_id);
                }
                if (!$revenueAccount && $line->inventory_item_id) {
                    $item = InventoryItem::find($line->inventory_item_id);
                    if ($item?->sales_account_id) {
                        $revenueAccount = ChartOfAccount::find($item->sales_account_id);
                    }
                }
                $revenueAccount = $revenueAccount ?? $defaultRevenue;

                if ($revenueAccount) {
                    JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $revenueAccount->id, 'debit' => 0, 'credit' => $line->line_amount, 'description' => $line->description ?: $invoice->invoice_number, 'line_order' => $lineNum++]);
                }
            }

            // CR VAT Output Tax (if any)
            if ($invoice->vat_amount > 0 && $vatAccount) {
                JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $vatAccount->id, 'debit' => 0, 'credit' => $invoice->vat_amount, 'description' => 'Output VAT', 'line_order' => $lineNum]);
            }

            $invoice->update(['status' => 'approved', 'journal_entry_id' => $je->id]);
        });

        return redirect()->route('tenant.accounting.invoices.show', [$tenant->slug, $invoice])
            ->with('success', 'Invoice approved and journal entry created.');
    }

    public function fixPriceForm(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $lines = $invoice->lines()->with('item')->get();
        return view('tenant.accounting.invoice.fix_price', compact('tenant', 'invoice', 'lines'));
    }

    public function fixPrice(Request $request, string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);

        DB::transaction(function () use ($request, $invoice) {
            $subtotal = 0;
            $totalVat = 0;

            foreach ($invoice->lines as $i => $line) {
                $newUnitPrice = floatval($request->unit_prices[$line->id] ?? $line->unit_price);
                $lineAmount   = round($line->quantity * $newUnitPrice, 2);
                $vatAmount    = round($lineAmount * ($line->vat_rate / 100), 2);
                $totalLine    = $lineAmount + $vatAmount;
                $goldWeight   = $line->item ? round($line->quantity * ($line->item->weight_per_unit_grams ?? 0), 4) : $line->gold_weight_grams;

                $line->update([
                    'unit_price'        => $newUnitPrice,
                    'line_amount'       => $lineAmount,
                    'vat_amount'        => $vatAmount,
                    'total_amount'      => $totalLine,
                    'gold_weight_grams' => $goldWeight,
                ]);

                $subtotal += $lineAmount;
                $totalVat += $vatAmount;
            }

            $totalAmount = $subtotal - $invoice->discount_amount + $totalVat;
            $invoice->update([
                'subtotal'     => $subtotal,
                'vat_amount'   => $totalVat,
                'total_amount' => $totalAmount,
                'amount_due'   => max(0, $totalAmount - $invoice->amount_paid),
            ]);
        });

        return redirect()->route('tenant.accounting.invoices.show', [app('tenant')->slug, $invoice])
            ->with('success', 'Invoice prices updated.');
    }

    public function void(Request $request, string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $invoice->update(['status' => 'void']);
        return redirect()->route('tenant.accounting.invoices.index', $tenant->slug)->with('success', 'Invoice voided.');
    }

    public function pdf(string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $lines = $invoice->lines()->with('item')->get();
        return view('tenant.accounting.invoice.pdf', compact('tenant', 'invoice', 'lines'));
    }

    public function storePayment(Request $request, string $slug, Invoice $invoice)
    {
        $tenant = app('tenant');
        abort_if($invoice->tenant_id !== $tenant->id, 404);
        $request->validate(['payment_date' => 'required|date', 'amount' => 'required|numeric|min:0.01', 'gl_account_id' => 'required|integer']);

        $amount = floatval($request->amount);

        $arAccount = ChartOfAccount::where('tenant_id', $tenant->id)->where('type', 'ASSET')
            ->where(function ($q) { $q->where('name', 'like', '%Receivable%')->orWhere('name', 'like', '%AR%'); })->first()
            ?? ChartOfAccount::where('tenant_id', $tenant->id)->where('type', 'ASSET')->where('is_active', true)->first();

        DB::transaction(function () use ($tenant, $invoice, $amount, $request, $arAccount) {
            $je = JournalEntry::create([
                'tenant_id'     => $tenant->id,
                'entry_date'    => $request->payment_date,
                'journal_type'  => 'BANK_RECEIPT',
                'status'        => 'posted',
                'memo'          => "Receipt - {$invoice->invoice_number} - {$invoice->customer_name}",
                'reference'     => $invoice->invoice_number,
                'currency_code' => 'AED',
                'exchange_rate' => 1.0,
                'created_by'    => auth()->id(),
            ]);

            // DR Cash/Bank
            JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $request->gl_account_id, 'debit' => $amount, 'credit' => 0, 'description' => "Receipt - {$invoice->invoice_number}", 'line_order' => 1]);

            // CR AR
            if ($arAccount) {
                JournalLine::create(['journal_entry_id' => $je->id, 'chart_of_account_id' => $arAccount->id, 'debit' => 0, 'credit' => $amount, 'description' => "AR cleared - {$invoice->customer_name}", 'line_order' => 2]);
            }

            $newPaid = $invoice->amount_paid + $amount;
            $newDue  = round($invoice->total_amount - $newPaid, 2);
            $invoice->update([
                'amount_paid' => $newPaid,
                'amount_due'  => max(0, $newDue),
                'status'      => $newDue <= 0 ? 'paid' : 'partial',
            ]);
        });

        return redirect()->route('tenant.accounting.invoices.show', [$tenant->slug, $invoice])
            ->with('success', 'Payment recorded.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildLines(Request $request): array
    {
        $lines    = [];
        $subtotal = 0;
        $totalVat = 0;

        $descriptions = $request->descriptions ?? [];
        $quantities   = $request->quantities ?? [];
        $unitPrices   = $request->unit_prices ?? [];
        $vatRates     = $request->vat_rates ?? [];
        $itemIds      = $request->item_ids ?? [];
        $accountIds   = $request->account_ids ?? [];
        $units        = $request->units ?? [];

        foreach ($descriptions as $i => $desc) {
            $qty      = floatval($quantities[$i] ?? 1);
            $price    = floatval($unitPrices[$i] ?? 0);
            $vatRate  = floatval($vatRates[$i] ?? 0);
            $itemId   = $itemIds[$i] ?? null;
            $accountId= $accountIds[$i] ?? null;

            if ($qty == 0 && $price == 0) continue;

            $lineAmount  = round($qty * $price, 2);
            $vatAmount   = round($lineAmount * ($vatRate / 100), 2);
            $totalLine   = $lineAmount + $vatAmount;
            $subtotal   += $lineAmount;
            $totalVat   += $vatAmount;

            $goldWeight = null;
            if ($itemId) {
                $item = InventoryItem::find($itemId);
                if ($item && $item->weight_per_unit_grams) {
                    $goldWeight = round($qty * $item->weight_per_unit_grams, 4);
                }
            }

            $lines[] = [
                'inventory_item_id'  => $itemId ?: null,
                'chart_of_account_id'=> $accountId ?: null,
                'description'        => $desc,
                'quantity'           => $qty,
                'unit'               => $units[$i] ?? 'pcs',
                'unit_price'         => $price,
                'line_amount'        => $lineAmount,
                'vat_rate'           => $vatRate,
                'vat_amount'         => $vatAmount,
                'total_amount'       => $totalLine,
                'gold_weight_grams'  => $goldWeight,
            ];
        }

        return [$lines, $subtotal, $totalVat];
    }
}
