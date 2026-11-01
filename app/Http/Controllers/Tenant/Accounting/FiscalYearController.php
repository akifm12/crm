<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingFiscalYear;
use Illuminate\Http\Request;

class FiscalYearController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $years  = AccountingFiscalYear::where('tenant_id', $tenant->id)
            ->orderByDesc('start_date')
            ->get();

        return view('tenant.accounting.settings.fiscal_years', compact('tenant', 'years'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        AccountingFiscalYear::create([
            'tenant_id'  => $tenant->id,
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return back()->with('success', 'Fiscal year created.');
    }

    public function close(string $slug, AccountingFiscalYear $fiscalYear)
    {
        $tenant = app('tenant');
        abort_if($fiscalYear->tenant_id !== $tenant->id, 404);
        abort_if(! $fiscalYear->isOpen(), 422, 'Already closed.');

        $fiscalYear->update(['status' => 'closed', 'closed_at' => now()]);

        return back()->with('success', "Fiscal year '{$fiscalYear->name}' closed.");
    }

    public function reopen(string $slug, AccountingFiscalYear $fiscalYear)
    {
        $tenant = app('tenant');
        abort_if($fiscalYear->tenant_id !== $tenant->id, 404);

        $fiscalYear->update(['status' => 'open', 'closed_at' => null]);

        return back()->with('success', "Fiscal year '{$fiscalYear->name}' reopened.");
    }
}
