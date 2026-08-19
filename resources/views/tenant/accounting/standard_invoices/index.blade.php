@extends('layouts.tenant')

@php
    $isRE        = $moduleType === 'real_estate';
    $label       = $isRE ? 'Rent Invoices' : 'Invoices';
    $prefix      = $isRE ? 'RENT' : 'INV';
    $rp          = $isRE ? 'tenant.accounting.re.invoices.' : 'tenant.accounting.general.invoices.';
    $createRoute = route($rp . 'create', $slug);
@endphp

@section('title', $label)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $label }}</h1>
        <a href="{{ $createRoute }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Invoice
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Number</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Client</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Due</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Total</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Balance</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $inv)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono text-blue-700">
                        <a href="{{ route($rp . 'show', [$slug, $inv->id]) }}" class="hover:underline">
                            {{ $inv->invoice_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-900">{{ $inv->client_name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $inv->invoice_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        @if($inv->due_date)
                            <span class="{{ $inv->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                {{ $inv->due_date->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-mono">{{ number_format($inv->total, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono {{ $inv->outstandingBalance() > 0 && $inv->status === 'posted' ? 'text-orange-600 font-semibold' : 'text-gray-500' }}">
                        {{ $inv->status === 'posted' ? number_format($inv->outstandingBalance(), 2) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($inv->status === 'draft')
                            <span class="inline-block bg-gray-100 text-gray-600 text-xs font-medium px-2 py-0.5 rounded-full">Draft</span>
                        @elseif($inv->status === 'posted')
                            @if($inv->isFullyPaid())
                                <span class="inline-block bg-green-100 text-green-700 text-xs font-medium px-2 py-0.5 rounded-full">Paid</span>
                            @elseif($inv->isOverdue())
                                <span class="inline-block bg-red-100 text-red-700 text-xs font-medium px-2 py-0.5 rounded-full">Overdue</span>
                            @else
                                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full">Posted</span>
                            @endif
                        @else
                            <span class="inline-block bg-red-50 text-red-500 text-xs font-medium px-2 py-0.5 rounded-full">Void</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route($rp . 'show', [$slug, $inv->id]) }}"
                           class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400 text-sm">
                        No invoices yet.
                        <a href="{{ $createRoute }}" class="text-blue-600 hover:underline ml-1">Create the first one.</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif

</div>
@endsection
