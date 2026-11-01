<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(string $slug)
    {
        $tenant    = app('tenant');
        $suppliers = Supplier::where('tenant_id', $tenant->id)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get();

        return view('tenant.accounting.suppliers.index', compact('tenant', 'suppliers'));
    }

    public function store(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30',
        ]);

        $liabilityAccounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('account_type', 'LIABILITY')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        Supplier::create([
            'tenant_id'          => $tenant->id,
            'code'               => $request->code,
            'name'               => $request->name,
            'name_arabic'        => $request->name_arabic,
            'trn'                => $request->trn,
            'is_vat_registered'  => $request->boolean('is_vat_registered'),
            'is_foreign_vendor'  => $request->boolean('is_foreign_vendor'),
            'email'              => $request->email,
            'phone'              => $request->phone,
            'contact_person'     => $request->contact_person,
            'address_line1'      => $request->address_line1,
            'city'               => $request->city,
            'emirate'            => $request->emirate,
            'country'            => $request->country ?? 'UAE',
            'payment_terms_days' => $request->payment_terms_days ?? 30,
            'currency_code'      => 'AED',
            'ap_account_id'      => $request->ap_account_id ?: null,
            'bank_name'          => $request->bank_name,
            'bank_account_no'    => $request->bank_account_no,
            'iban'               => $request->iban,
            'swift_code'         => $request->swift_code,
            'is_active'          => true,
            'is_deleted'         => false,
            'notes'              => $request->notes,
        ]);

        return redirect()->route('tenant.accounting.suppliers.index', $tenant->slug)
            ->with('success', 'Supplier created.');
    }

    public function update(Request $request, string $slug, Supplier $supplier)
    {
        $tenant = app('tenant');
        abort_if($supplier->tenant_id !== $tenant->id, 404);

        $request->validate(['name' => 'required|string|max:255']);

        $supplier->update([
            'name'               => $request->name,
            'name_arabic'        => $request->name_arabic,
            'trn'                => $request->trn,
            'is_vat_registered'  => $request->boolean('is_vat_registered'),
            'is_foreign_vendor'  => $request->boolean('is_foreign_vendor'),
            'email'              => $request->email,
            'phone'              => $request->phone,
            'contact_person'     => $request->contact_person,
            'address_line1'      => $request->address_line1,
            'city'               => $request->city,
            'emirate'            => $request->emirate,
            'country'            => $request->country ?? 'UAE',
            'payment_terms_days' => $request->payment_terms_days ?? 30,
            'ap_account_id'      => $request->ap_account_id ?: null,
            'bank_name'          => $request->bank_name,
            'bank_account_no'    => $request->bank_account_no,
            'iban'               => $request->iban,
            'swift_code'         => $request->swift_code,
            'notes'              => $request->notes,
        ]);

        return redirect()->route('tenant.accounting.suppliers.index', $tenant->slug)
            ->with('success', 'Supplier updated.');
    }

    public function destroy(string $slug, Supplier $supplier)
    {
        $tenant = app('tenant');
        abort_if($supplier->tenant_id !== $tenant->id, 404);

        $hasBills = \App\Models\Bill::where('supplier_id', $supplier->id)->exists();
        if ($hasBills) {
            return back()->with('error', 'Cannot delete supplier with existing bills.');
        }

        $supplier->update(['is_deleted' => true]);

        return redirect()->route('tenant.accounting.suppliers.index', $tenant->slug)
            ->with('success', 'Supplier removed.');
    }
}
