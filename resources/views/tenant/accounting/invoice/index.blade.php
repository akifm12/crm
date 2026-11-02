@extends('layouts.tenant')
@section('title', 'Invoices — ' . $tenant->name)
@section('page-title', 'Invoices')
@section('page-subtitle', 'Accounts receivable')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">From</label>
        <input type="date" name="date_from" value="{{ $dateFrom }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">To</label>
        <input type="date" name="date_to" value="{{ $dateTo }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Status</label>
        <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
            <option value="all" @selected($status==='all')>All</option>
            <option value="draft" @selected($status==='draft')>Draft</option>
            <option value="sent" @selected($status==='sent')>Sent</option>
            <option value="approved" @selected($status==='approved')>Approved</option>
            <option value="paid" @selected($status==='paid')>Paid</option>
            <option value="partial" @selected($status==='partial')>Partial</option>
            <option value="void" @selected($status==='void')>Void</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Search</label>
        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Invoice # or client…" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 w-48">
    </div>
    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">Filter</button>
    <a href="{{ route('tenant.accounting.invoices.index', $tenant->slug) }}" class="px-4 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Reset</a>
    <div class="ml-auto">
        <a href="{{ route('tenant.accounting.invoices.create', $tenant->slug) }}" class="px-4 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">+ New Invoice</a>
    </div>
</form>

{{-- Summary --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Invoices</p>
        <p class="text-xl font-bold text-gray-800">{{ $invoices->count() }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Total Invoiced</p>
        <p class="text-xl font-bold text-gray-800">AED {{ number_format($totalAmount, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Total Outstanding</p>
        <p class="text-xl font-bold {{ $totalDue > 0 ? 'text-amber-600' : 'text-gray-800' }}">AED {{ number_format($totalDue, 2) }}</p>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Invoice #</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Client</th>
                <th class="px-4 py-3 text-right">Amount</th>
                <th class="px-4 py-3 text-right">Outstanding</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($invoices as $inv)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5">
                    <a href="{{ route('tenant.accounting.invoices.show', [$tenant->slug, $inv->id]) }}" class="font-mono text-blue-600 hover:underline text-xs">{{ $inv->invoice_number }}</a>
                </td>
                <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $inv->invoice_date }}</td>
                <td class="px-4 py-2.5 text-gray-700">{{ Str::limit($inv->customer_name, 40) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($inv->total_amount, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs {{ $inv->amount_due > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
                    {{ $inv->amount_due > 0 ? number_format($inv->amount_due, 2) : '—' }}
                </td>
                <td class="px-4 py-2.5 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $inv->statusBadge() }}">{{ $inv->STATUS }}</span>
                </td>
                <td class="px-4 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.invoices.pdf', [$tenant->slug, $inv->id]) }}" class="text-xs text-gray-400 hover:text-gray-600" target="_blank">PDF</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No invoices found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
