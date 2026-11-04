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
            ->where('is_active', true)
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
            ->where('type', 'LIABILITY')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        Supplier::create([
            'tenant_id'    => $tenant->id,
            'name'         => $request->name,
            'vat_number'   => $request->trn,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'contact_name' => $request->contact_person,
            'address'      => $request->address_line1,
            'is_active'    => true,
            'notes'        => $request->notes,
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
            'name'         => $request->name,
            'vat_number'   => $request->trn,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'contact_name' => $request->contact_person,
            'address'      => $request->address_line1,
            'notes'        => $request->notes,
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

        $supplier->update(['is_active' => false]);

        return redirect()->route('tenant.accounting.suppliers.index', $tenant->slug)
            ->with('success', 'Supplier removed.');
    }
}
