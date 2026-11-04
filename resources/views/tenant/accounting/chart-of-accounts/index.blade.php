@extends('layouts.tenant')
@section('title', 'Chart of Accounts — ' . $tenant->name)
@section('page-title', 'Chart of Accounts')
@section('page-subtitle', 'Manage your general ledger accounts')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add account form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Add Account</h3>
        <form action="{{ route('tenant.accounting.coa.store', $tenant->slug) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Code *</label>
                <input type="text" name="code" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" placeholder="1001" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                <input type="text" name="name" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" placeholder="Cash in Hand" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type *</label>
                <select name="account_type" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                    <option value="">— select —</option>
                    <option value="ASSET">Asset</option>
                    <option value="LIABILITY">Liability</option>
                    <option value="EQUITY">Equity</option>
                    <option value="REVENUE">Revenue</option>
                    <option value="EXPENSE">Expense</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Normal Balance *</label>
                <select name="normal_balance" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                    <option value="DEBIT">Debit</option>
                    <option value="CREDIT">Credit</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Parent Account</label>
                <select name="parent_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">— none —</option>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 text-xs pt-1">
                <label class="flex items-center gap-1"><input type="checkbox" name="is_cash"> Cash</label>
                <label class="flex items-center gap-1"><input type="checkbox" name="is_bank"> Bank</label>
                <label class="flex items-center gap-1"><input type="checkbox" name="is_payable"> Payable</label>
                <label class="flex items-center gap-1"><input type="checkbox" name="is_receivable"> Receivable</label>
            </div>
            <button type="submit" class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Add Account
            </button>
        </form>
    </div>

    {{-- Account list --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        @foreach(['ASSET','LIABILITY','EQUITY','REVENUE','EXPENSE'] as $type)
        @if(isset($grouped[$type]) && $grouped[$type]->count())
        <div class="border-b border-gray-100 last:border-b-0">
            <div class="px-4 py-2 bg-gray-50 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $type }}</span>
                <span class="text-xs text-gray-400">{{ $typeTotals[$type] ?? 0 }} accounts</span>
            </div>
            @foreach($grouped[$type] as $account)
            <div class="flex items-center px-4 py-2.5 border-b border-gray-50 hover:bg-gray-50 text-sm" style="padding-left: {{ 16 + ($account->depth * 20) }}px">
                <span class="font-mono text-xs text-gray-400 w-16 shrink-0">{{ $account->code }}</span>
                <span class="flex-1 text-gray-800">{{ $account->name }}</span>
                <div class="flex items-center gap-1">
                    @if($account->is_cash) <span class="text-xs px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded">Cash</span> @endif
                    @if($account->is_bank) <span class="text-xs px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded">Bank</span> @endif
                    @if($account->is_payable) <span class="text-xs px-1.5 py-0.5 bg-orange-50 text-orange-600 rounded">AP</span> @endif
                    @if($account->is_receivable) <span class="text-xs px-1.5 py-0.5 bg-green-50 text-green-600 rounded">AR</span> @endif
                    <form action="{{ route('tenant.accounting.coa.destroy', [$tenant->slug, $account->id]) }}" method="POST" onsubmit="return confirm('Delete this account?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600 ml-2">×</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endforeach
        @if($grouped->isEmpty())
        <div class="py-12 text-center text-gray-400 text-sm">No accounts yet. Add your first account.</div>
        @endif
    </div>
</div>
@endsection
