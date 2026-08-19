@extends('layouts.tenant')
@section('title', 'Bills — ' . $tenant->name)
@section('page-title', 'Bills')
@section('page-subtitle', 'Company expenses and supplier invoices')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div></div>
    <a href="{{ route('tenant.accounting.bills.create', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + New bill
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Bill #</th>
                <th class="px-5 py-3">Supplier</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Due</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Total (AED)</th>
                <th class="px-5 py-3 text-right">Outstanding</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($bills as $bill)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5">
                    <a href="{{ route('tenant.accounting.bills.show', [$tenant->slug, $bill->id]) }}"
                       class="font-mono text-blue-600 hover:underline text-xs">{{ $bill->bill_number }}</a>
                </td>
                <td class="px-5 py-2.5 text-gray-800">{{ $bill->supplier_name }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ $bill->bill_date->format('d M Y') }}</td>
                <td class="px-5 py-2.5 text-gray-500">
                    @if($bill->due_date)
                        <span class="{{ $bill->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                            {{ $bill->due_date->format('d M Y') }}
                        </span>
                    @else
                        —
                    @endif
                </td>
                <td class="px-5 py-2.5">
                    @php
                        $statusClass = match($bill->status) {
                            'posted' => $bill->isFullyPaid() ? 'bg-green-50 text-green-700' : ($bill->isOverdue() ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-700'),
                            'void'   => 'bg-gray-100 text-gray-400',
                            default  => 'bg-amber-50 text-amber-700',
                        };
                        $statusLabel = match(true) {
                            $bill->status === 'draft'                   => 'Draft',
                            $bill->status === 'void'                    => 'Void',
                            $bill->status === 'posted' && $bill->isFullyPaid() => 'Paid',
                            $bill->status === 'posted' && $bill->isOverdue()   => 'Overdue',
                            default                                     => 'Posted',
                        };
                    @endphp
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($bill->total, 2) }}</td>
                <td class="px-5 py-2.5 text-right font-mono {{ $bill->outstandingBalance() > 0 && $bill->status === 'posted' ? 'text-red-600' : 'text-gray-400' }}">
                    {{ $bill->status === 'posted' ? number_format($bill->outstandingBalance(), 2) : '—' }}
                </td>
                <td class="px-5 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.bills.show', [$tenant->slug, $bill->id]) }}"
                       class="text-xs text-gray-400 hover:text-blue-600">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-400">
                    No bills yet. <a href="{{ route('tenant.accounting.bills.create', $tenant->slug) }}" class="text-blue-600 hover:underline">Record your first bill</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($bills->hasPages())
<div class="mt-4">{{ $bills->links() }}</div>
@endif

@endsection
