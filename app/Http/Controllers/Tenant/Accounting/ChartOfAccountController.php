<?php
// app/Http/Controllers/Tenant/Accounting/ChartOfAccountController.php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->orderBy('code')
            ->get();

        // Flatten the tree depth-first (any number of levels) so the view can render
        // a single loop instead of hardcoding parent/child — each row carries its
        // depth for indentation. Same list doubles as the "parent" dropdown options.
        $flatTree = $this->flattenTree($accounts, null, 0);

        return view('tenant.accounting.coa.index', compact('tenant', 'accounts', 'flatTree'));
    }

    private function flattenTree($accounts, ?int $parentId, int $depth): array
    {
        $rows = [];
        foreach ($accounts->where('parent_id', $parentId) as $account) {
            $rows[] = ['account' => $account, 'depth' => $depth];
            $rows = array_merge($rows, $this->flattenTree($accounts, $account->id, $depth + 1));
        }

        return $rows;
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'parent_id'      => 'required|exists:chart_of_accounts,id',
            'code'           => 'required|string|max:20',
            'name'           => 'required|string|max:255',
            'normal_balance' => 'required|in:debit,credit',
        ]);

        $parent = ChartOfAccount::findOrFail($request->parent_id);
        abort_if($parent->tenant_id !== $tenant->id, 404);

        ChartOfAccount::create([
            'tenant_id'      => $tenant->id,
            'parent_id'      => $parent->id,
            'code'           => $request->code,
            'name'           => $request->name,
            'type'           => $parent->type,
            'subtype'        => null,
            'normal_balance' => $request->normal_balance,
            'is_system'      => false,
            'is_active'      => true,
        ]);

        return back()->with('success', 'Account added.');
    }

    public function destroy(string $slug, ChartOfAccount $account)
    {
        $tenant = app('tenant');
        abort_if($account->tenant_id !== $tenant->id, 404);
        abort_if($account->is_system, 403, 'System accounts cannot be deleted.');
        abort_if(ChartOfAccount::where('parent_id', $account->id)->exists(), 422, 'Remove or reassign this account\'s sub-accounts first.');

        $account->delete();

        return back()->with('success', 'Account removed.');
    }
}
