<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $tenant    = app('tenant');
        $suppliers = Supplier::where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->withCount('bills')
            ->get();

        return view('tenant.accounting.suppliers.index', compact('tenant', 'suppliers'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');
        $data   = $request->validate([
            'name'         => 'required|string|max:255',
            'vat_number'   => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:500',
            'notes'        => 'nullable|string',
        ]);

        $supplier = Supplier::create(array_merge($data, ['tenant_id' => $tenant->id]));

        if ($request->wantsJson()) {
            return response()->json(['id' => $supplier->id, 'name' => $supplier->name, 'vat_number' => $supplier->vat_number]);
        }

        return back()->with('success', 'Supplier added.');
    }

    public function update(Request $request, string $slug, Supplier $supplier)
    {
        $tenant = app('tenant');
        abort_if($supplier->tenant_id !== $tenant->id, 404);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'vat_number'   => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:500',
            'notes'        => 'nullable|string',
        ]);

        $supplier->update($data);

        return back()->with('success', 'Supplier updated.');
    }

    public function destroy(string $slug, Supplier $supplier)
    {
        $tenant = app('tenant');
        abort_if($supplier->tenant_id !== $tenant->id, 404);
        abort_if($supplier->bills()->exists(), 422, 'Cannot delete a supplier with existing bills.');

        $supplier->delete();

        return back()->with('success', 'Supplier deleted.');
    }
}
