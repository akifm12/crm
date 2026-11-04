<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $tenant   = app('tenant');
        $accounts = BankAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->with('chartAccount')
            ->orderBy('account_name')
            ->get();

        foreach ($accounts as $account) {
            $account->balance = $account->currentBalance();
        }

        return view('tenant.accounting.banking.index', compact('tenant', 'accounts'));
    }

    public function create()
    {
        $tenant   = app('tenant');
        $glAccounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('account_type', 'asset')
            ->orderBy('code')
            ->get();

        return view('tenant.accounting.banking.create', compact('tenant', 'glAccounts'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'account_name'       => 'required|string|max:255',
            'bank_name'          => 'required|string|max:255',
            'account_number'     => 'nullable|string|max:100',
            'currency'           => 'nullable|string|max:10',
            'chart_of_account_id'=> 'nullable|exists:chart_of_accounts,id',
        ]);

        BankAccount::create([
            'tenant_id'          => $tenant->id,
            'account_name'       => $request->account_name,
            'bank_name'          => $request->bank_name,
            'account_number'     => $request->account_number,
            'currency'           => $request->currency ?? 'AED',
            'chart_of_account_id'=> $request->chart_of_account_id,
        ]);

        return redirect()->route('tenant.accounting.banking.index', $tenant->slug)
            ->with('success', 'Bank account added.');
    }

    public function show(string $slug, BankAccount $bankAccount)
    {
        $tenant = app('tenant');
        abort_if($bankAccount->tenant_id !== $tenant->id, 404);

        $transactions = BankTransaction::where('bank_account_id', $bankAccount->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(40);

        $balance = $bankAccount->currentBalance();

        return view('tenant.accounting.banking.show', compact('tenant', 'bankAccount', 'transactions', 'balance'));
    }

    public function addTransaction(Request $request, string $slug, BankAccount $bankAccount)
    {
        $tenant = app('tenant');
        abort_if($bankAccount->tenant_id !== $tenant->id, 404);

        $request->validate([
            'transaction_date' => 'required|date',
            'description'      => 'required|string|max:500',
            'amount'           => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:debit,credit',
            'reference'        => 'nullable|string|max:255',
        ]);

        BankTransaction::create([
            'bank_account_id'  => $bankAccount->id,
            'tenant_id'        => $tenant->id,
            'transaction_date' => $request->transaction_date,
            'description'      => $request->description,
            'amount'           => $request->amount,
            'transaction_type' => $request->transaction_type,
            'reference'        => $request->reference,
        ]);

        return back()->with('success', 'Transaction recorded.');
    }
}
