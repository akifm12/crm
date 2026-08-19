<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Supplier;
use App\Services\Accounting\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BillController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $bills  = Bill::where('tenant_id', $tenant->id)
            ->withCount('payments')
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->paginate(40);

        return view('tenant.accounting.bills.index', compact('tenant', 'bills'));
    }

    public function create()
    {
        $tenant    = app('tenant');
        $suppliers = Supplier::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        return view('tenant.accounting.bills.create', compact('tenant', 'suppliers'));
    }

    public function store(Request $request, BillingService $billing)
    {
        $tenant = app('tenant');
        $request->validate($this->lineRules());

        try {
            $bill = $billing->createDraft($tenant, $request->only(
                'supplier_id', 'supplier_name', 'supplier_vat_number', 'reference', 'bill_date', 'due_date', 'notes', 'lines'
            ));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill->id])
            ->with('success', 'Bill saved as draft.');
    }

    public function show(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        $bill->load('lines', 'payments', 'journalEntry');

        return view('tenant.accounting.bills.show', compact('tenant', 'bill'));
    }

    public function edit(string $slug, Bill $bill)
    {
        $tenant    = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        abort_if($bill->status !== 'draft', 403, 'Only draft bills can be edited.');
        $bill->load('lines');
        $suppliers = Supplier::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();

        return view('tenant.accounting.bills.edit', compact('tenant', 'bill', 'suppliers'));
    }

    public function update(Request $request, string $slug, Bill $bill, BillingService $billing)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        $request->validate($this->lineRules());

        try {
            $billing->updateDraft($bill, $request->only(
                'supplier_id', 'supplier_name', 'supplier_vat_number', 'reference', 'bill_date', 'due_date', 'notes', 'lines'
            ));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill->id])
            ->with('success', 'Draft bill updated.');
    }

    public function destroy(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        abort_if($bill->status !== 'draft', 403, 'Only draft bills can be deleted.');

        $bill->lines()->delete();
        $bill->delete();

        return redirect()->route('tenant.accounting.bills.index', $tenant->slug)
            ->with('success', 'Draft bill deleted.');
    }

    public function post(string $slug, Bill $bill, BillingService $billing)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        try {
            $billing->post($bill);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill->id])
            ->with('success', 'Bill posted.');
    }

    public function void(Request $request, string $slug, Bill $bill, BillingService $billing)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        try {
            $billing->void($bill, $request->input('reason'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.accounting.bills.show', [$tenant->slug, $bill->id])
            ->with('success', 'Bill voided.');
    }

    public function uploadAttachment(Request $request, string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        $request->validate([
            'attachment' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
        ]);

        if ($bill->attachment_path) {
            Storage::disk('local')->delete($bill->attachment_path);
        }

        $file = $request->file('attachment');
        $path = $file->storeAs(
            "bills/{$tenant->id}/{$bill->id}",
            $bill->bill_number . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        $bill->update([
            'attachment_path' => $path,
            'attachment_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function downloadAttachment(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        abort_if(! $bill->attachment_path, 404);
        abort_if(! Storage::disk('local')->exists($bill->attachment_path), 404);

        return Storage::disk('local')->download($bill->attachment_path, $bill->attachment_name);
    }

    public function pdf(string $slug, Bill $bill)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);
        $bill->load('lines');

        $pdf = Pdf::loadView('tenant.accounting.bills.pdf', compact('tenant', 'bill'));

        return $pdf->stream("{$bill->bill_number}.pdf");
    }

    public function storePayment(Request $request, string $slug, Bill $bill, BillingService $billing)
    {
        $tenant = app('tenant');
        abort_if($bill->tenant_id !== $tenant->id, 404);

        $request->validate([
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'nullable|string',
            'account'      => 'nullable|string',
            'reference'    => 'nullable|string|max:255',
        ]);

        try {
            $billing->recordPayment($bill, $request->only('payment_date', 'amount', 'method', 'account', 'reference'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment recorded.');
    }

    private function lineRules(): array
    {
        return [
            'supplier_id'         => 'nullable|integer',
            'supplier_name'       => 'nullable|string|max:255',
            'supplier_vat_number' => 'nullable|string|max:50',
            'reference'           => 'nullable|string|max:255',
            'bill_date'           => 'required|date',
            'due_date'            => 'nullable|date|after_or_equal:bill_date',
            'notes'               => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.category'    => ['required', Rule::in(array_keys(Bill::CATEGORIES))],
            'lines.*.amount'      => 'required|numeric|min:0.01',
            'lines.*.vat_treatment' => ['required', Rule::in(Bill::VAT_TREATMENTS)],
            'lines.*.vat_rate'    => 'required|numeric|min:0|max:100',
        ];
    }
}
