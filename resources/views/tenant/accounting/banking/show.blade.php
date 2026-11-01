@extends('layouts.tenant')
@section('title', $account->account_name . ' — ' . $tenant->name)
@section('page-title', $account->account_name)
@section('page-subtitle', $account->bank_name . ' · ' . $account->currency)

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 mb-1">Current Balance</p>
        <p class="text-2xl font-bold {{ $account->currentBalance() >= 0 ? 'text-gray-900' : 'text-red-600' }}">
            {{ $account->currency }} {{ number_format($account->currentBalance(), 2) }}
        </p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 mb-1">Account Number</p>
        <p class="font-mono text-gray-800 text-sm">{{ $account->account_number ?: '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 mb-1">Transactions</p>
        <p class="text-2xl font-bold text-gray-900">{{ $transactions->total() }}</p>
    </div>
</div>

{{-- Add transaction --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Add Transaction</h3>
    <form method="POST" action="{{ route('tenant.accounting.banking.transaction', [$tenant->slug, $account->id]) }}"
          class="flex flex-wrap gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs text-gray-500 mb-1">Date</label>
            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-gray-500 mb-1">Description</label>
            <input type="text" name="description" required placeholder="Transaction description"
                   class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Type</label>
            <select name="transaction_type" required class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                <option value="debit">Debit (in)</option>
                <option value="credit">Credit (out)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Amount</label>
            <input type="number" name="amount" step="0.01" min="0.01" required
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-32 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Reference</label>
            <input type="text" name="reference" placeholder="Optional"
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit" class="px-4 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Add
        </button>
    </form>
</div>

{{-- Transactions table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Description</th>
                <th class="px-5 py-3">Reference</th>
                <th class="px-5 py-3 text-right">Debit</th>
                <th class="px-5 py-3 text-right">Credit</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($transactions as $tx)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5 text-gray-500">{{ $tx->transaction_date->format('d M Y') }}</td>
                <td class="px-5 py-2.5 text-gray-800">{{ $tx->description }}</td>
                <td class="px-5 py-2.5 font-mono text-xs text-gray-500">{{ $tx->reference ?: '—' }}</td>
                <td class="px-5 py-2.5 text-right text-green-700">
                    {{ $tx->transaction_type === 'debit' ? number_format($tx->amount, 2) : '' }}
                </td>
                <td class="px-5 py-2.5 text-right text-red-600">
                    {{ $tx->transaction_type === 'credit' ? number_format($tx->amount, 2) : '' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No transactions yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $transactions->links() }}
@endsection
