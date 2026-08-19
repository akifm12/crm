@extends('layouts.tenant')
@section('title', 'Real Estate — ' . $tenant->name)
@section('page-title', 'Real Estate Accounting')
@section('page-subtitle', $tenant->name)

@section('content')
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Outstanding Payables</p>
        <p class="text-2xl font-bold text-gray-900">AED {{ number_format($outstandingBills, 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">Unpaid posted bills</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 {{ $overdueBills > 0 ? 'border-red-200' : '' }}">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Overdue Bills</p>
        <p class="text-2xl font-bold {{ $overdueBills > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $overdueBills }}</p>
        <p class="text-xs text-gray-400 mt-1">Past due date</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Quick Links</p>
        <div class="flex flex-col gap-1 mt-1">
            <a href="{{ route('tenant.accounting.bills.create', $tenant->slug) }}" class="text-xs text-blue-600 hover:underline">+ New expense bill</a>
            <a href="{{ route('tenant.accounting.suppliers.index', $tenant->slug) }}" class="text-xs text-blue-600 hover:underline">Manage suppliers</a>
            <a href="{{ route('tenant.accounting.reports.vat-return', $tenant->slug) }}" class="text-xs text-blue-600 hover:underline">VAT Return</a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5 max-w-2xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Recent Bills</h3>
    @forelse($recentBills as $bill)
    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
        <div>
            <a href="{{ route('tenant.accounting.bills.show', [$tenant->slug, $bill->id]) }}"
               class="text-sm font-medium text-blue-600 hover:underline">{{ $bill->bill_number }}</a>
            <p class="text-xs text-gray-400">{{ $bill->supplier_name }} · {{ $bill->bill_date->format('d M Y') }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-mono">AED {{ number_format($bill->total, 2) }}</p>
            @php
                $cls = match(true) {
                    $bill->status === 'draft' => 'bg-amber-50 text-amber-700',
                    $bill->isFullyPaid()      => 'bg-green-50 text-green-700',
                    $bill->isOverdue()        => 'bg-red-50 text-red-600',
                    $bill->status === 'void'  => 'bg-gray-100 text-gray-400',
                    default                   => 'bg-blue-50 text-blue-700',
                };
                $lbl = match(true) {
                    $bill->status === 'draft'  => 'Draft',
                    $bill->status === 'void'   => 'Void',
                    $bill->isFullyPaid()       => 'Paid',
                    $bill->isOverdue()         => 'Overdue',
                    default                    => 'Posted',
                };
            @endphp
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $cls }}">{{ $lbl }}</span>
        </div>
    </div>
    @empty
    <p class="text-sm text-gray-400">No bills yet. <a href="{{ route('tenant.accounting.bills.create', $tenant->slug) }}" class="text-blue-600 hover:underline">Record your first expense.</a></p>
    @endforelse
    @if($recentBills->count())
    <a href="{{ route('tenant.accounting.bills.index', $tenant->slug) }}"
       class="text-xs text-blue-600 hover:underline mt-3 block">View all bills →</a>
    @endif
</div>
@endsection
