@extends('layouts.tenant')
@section('title', 'Banking — ' . $tenant->name)
@section('page-title', 'Banking')
@section('page-subtitle', 'Bank accounts and transactions')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div></div>
    <a href="{{ route('tenant.accounting.banking.create', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + New account
    </a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    @forelse($accounts as $account)
    <a href="{{ route('tenant.accounting.banking.show', [$tenant->slug, $account->id]) }}"
       class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="font-semibold text-gray-900">{{ $account->account_name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $account->bank_name }}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full {{ $account->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $account->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <p class="text-xs text-gray-400 font-mono mb-3">{{ $account->account_number ?: '—' }}</p>
        <div class="flex items-end justify-between">
            <span class="text-xs text-gray-500">{{ $account->currency }}</span>
            <span class="text-lg font-bold {{ $account->currentBalance() >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                {{ number_format($account->currentBalance(), 2) }}
            </span>
        </div>
    </a>
    @empty
    <div class="col-span-3 py-12 text-center text-gray-400 text-sm">
        No bank accounts yet. <a href="{{ route('tenant.accounting.banking.create', $tenant->slug) }}" class="text-blue-600 hover:underline">Add one</a>.
    </div>
    @endforelse
</div>
@endsection
