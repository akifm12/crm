<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(string $slug)
    {
        $tenant   = app('tenant');
        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderByRaw("FIELD(type,'ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE')")
            ->orderBy('code')
            ->get();

        $allById   = $accounts->keyBy('id');
        $organized = [];
        foreach ($accounts as $account) {
            if (is_null($account->parent_id) || !$allById->has($account->parent_id)) {
                $account->depth = 0;
                $organized[]    = $account;
                foreach ($accounts as $child) {
                    if ($child->parent_id == $account->id) {
                        $child->depth = 1;
                        $organized[]  = $child;
                        foreach ($accounts as $grandchild) {
                            if ($grandchild->parent_id == $child->id) {
                                $grandchild->depth = 2;
                                $organized[]       = $grandchild;
                            }
                        }
                    }
                }
            }
        }

        $grouped    = collect($organized)->groupBy('account_type');
        $typeTotals = $grouped->map(fn($g) => $g->count());

        return view('tenant.accounting.chart-of-accounts.index', compact('tenant', 'grouped', 'typeTotals', 'accounts'));
    }

    public function store(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate([
            'code'           => 'required|string|max:20',
            'name'           => 'required|string|max:255',
            'account_type'   => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'required|in:DEBIT,CREDIT',
        ]);

        $exists = ChartOfAccount::where('tenant_id', $tenant->id)->where('code', $request->code)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['code' => 'Account code already exists.']);
        }

        ChartOfAccount::create([
            'tenant_id'       => $tenant->id,
            'parent_id'       => $request->parent_id ?: null,
            'code'            => strtoupper(trim($request->code)),
            'name'            => trim($request->name),
            'name_arabic'     => $request->name_arabic ?: null,
            'description'     => $request->description ?: null,
            'account_type'    => $request->account_type,
            'account_sub_type'=> $request->account_sub_type ?: null,
            'normal_balance'  => $request->normal_balance,
            'is_control'      => $request->boolean('is_control'),
            'is_bank'         => $request->boolean('is_bank'),
            'is_cash'         => $request->boolean('is_cash'),
            'is_receivable'   => $request->boolean('is_receivable'),
            'is_payable'      => $request->boolean('is_payable'),
            'is_vat_account'  => $request->boolean('is_vat_account'),
            'currency_code'   => $request->currency_code ?? 'AED',
            'is_active'       => true,
            'notes'           => $request->notes ?: null,
            'opening_balance' => $request->opening_balance ?: null,
            'opening_balance_date' => $request->opening_balance_date ?: null,
        ]);

        return redirect()->route('tenant.accounting.coa.index', $tenant->slug)
            ->with('success', 'Account created successfully.');
    }

    public function destroy(string $slug, ChartOfAccount $account)
    {
        $tenant = app('tenant');
        abort_if($account->tenant_id !== $tenant->id, 404);

        $inUse = \App\Models\JournalLine::where('chart_of_account_id', $account->id)->exists();
        if ($inUse) {
            return back()->with('error', 'Cannot delete — this account has journal entries.');
        }
        if (ChartOfAccount::where('parent_id', $account->id)->exists()) {
            return back()->with('error', 'Cannot delete — this account has sub-accounts.');
        }

        $account->delete();

        return redirect()->route('tenant.accounting.coa.index', $tenant->slug)
            ->with('success', 'Account deleted.');
    }
}
