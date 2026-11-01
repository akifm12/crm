<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\StandardInvoice;
use App\Models\VatFiling;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VatFilingController extends Controller
{
    public function index()
    {
        $tenant  = app('tenant');
        $filings = VatFiling::where('tenant_id', $tenant->id)
            ->orderByDesc('period_from')
            ->get();

        return view('tenant.accounting.vat-filings.index', compact('tenant', 'filings'));
    }

    public function create()
    {
        $tenant = app('tenant');
        return view('tenant.accounting.vat-filings.create', compact('tenant'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'period_from' => 'required|date',
            'period_to'   => 'required|date|after_or_equal:period_from',
        ]);

        $from = Carbon::parse($request->period_from)->startOfDay();
        $to   = Carbon::parse($request->period_to)->endOfDay();

        // Auto-calculate from invoices and bills
        $vatOnSales = $this->calcVatOnSales($tenant->id, $from, $to);
        $salesBase  = $this->calcSalesBase($tenant->id, $from, $to);
        $vatOnPurch = $this->calcVatOnPurchases($tenant->id, $from, $to);
        $purchBase  = $this->calcPurchasesBase($tenant->id, $from, $to);
        $netVat     = $vatOnSales - $vatOnPurch;

        $filing = VatFiling::create([
            'tenant_id'                 => $tenant->id,
            'period_from'               => $request->period_from,
            'period_to'                 => $request->period_to,
            'due_date'                  => Carbon::parse($request->period_to)->addDays(28)->toDateString(),
            'standard_rated_sales'      => $salesBase,
            'vat_on_sales'              => $vatOnSales,
            'standard_rated_purchases'  => $purchBase,
            'vat_on_purchases'          => $vatOnPurch,
            'net_vat_payable'           => $netVat,
        ]);

        return redirect()->route('tenant.accounting.vat-filings.show', [$tenant->slug, $filing->id])
            ->with('success', 'VAT filing created.');
    }

    public function show(string $slug, VatFiling $vatFiling)
    {
        $tenant = app('tenant');
        abort_if($vatFiling->tenant_id !== $tenant->id, 404);

        return view('tenant.accounting.vat-filings.show', compact('tenant', 'vatFiling'));
    }

    public function submit(Request $request, string $slug, VatFiling $vatFiling)
    {
        $tenant = app('tenant');
        abort_if($vatFiling->tenant_id !== $tenant->id, 404);
        abort_if($vatFiling->isSubmitted(), 422, 'Already submitted.');

        $vatFiling->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
            'reference'    => $request->reference,
        ]);

        return back()->with('success', 'VAT return marked as submitted.');
    }

    public function pay(Request $request, string $slug, VatFiling $vatFiling)
    {
        $tenant = app('tenant');
        abort_if($vatFiling->tenant_id !== $tenant->id, 404);
        abort_if($vatFiling->isPaid(), 422, 'Already paid.');

        $vatFiling->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'VAT return marked as paid.');
    }

    private function calcVatOnSales(int $tenantId, Carbon $from, Carbon $to): float
    {
        $bullion  = Invoice::where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('vat_amount') ?? 0;

        $standard = StandardInvoice::where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('vat_amount') ?? 0;

        return (float) $bullion + (float) $standard;
    }

    private function calcSalesBase(int $tenantId, Carbon $from, Carbon $to): float
    {
        $bullion  = Invoice::where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('subtotal') ?? 0;

        $standard = StandardInvoice::where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('subtotal') ?? 0;

        return (float) $bullion + (float) $standard;
    }

    private function calcVatOnPurchases(int $tenantId, Carbon $from, Carbon $to): float
    {
        return (float) Bill::where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->whereBetween('bill_date', [$from, $to])
            ->sum('total_vat') ?? 0;
    }

    private function calcPurchasesBase(int $tenantId, Carbon $from, Carbon $to): float
    {
        return (float) Bill::where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->whereBetween('bill_date', [$from, $to])
            ->sum('subtotal') ?? 0;
    }
}
