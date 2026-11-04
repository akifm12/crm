@extends('layouts.tenant')
@section('title', 'Bills — ' . $tenant->name)
@section('page-title', 'Bills')
@section('page-subtitle', 'Supplier invoices and payables')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

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
            <option value="DRAFT" @selected($status==='DRAFT')>Draft</option>
            <option value="APPROVED" @selected($status==='APPROVED')>Approved</option>
            <option value="PAID" @selected($status==='PAID')>Paid</option>
            <option value="PARTIAL" @selected($status==='PARTIAL')>Partial</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">Filter</button>
    <a href="{{ route('tenant.accounting.bills.create', $tenant->slug) }}" class="ml-auto px-4 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">+ New Bill</a>
</form>

<div class="grid grid-cols-2 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Total Billed</p>
        <p class="text-xl font-bold text-gray-800">AED {{ number_format($totalAmount, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Total Due</p>
        <p class="text-xl font-bold {{ $totalDue > 0 ? 'text-red-600' : 'text-gray-800' }}">AED {{ number_format($totalDue, 2) }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Bill #</th>
                <th class="px-4 py-3 text-left">Supplier</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Due</th>
                <th class="px-4 py-3 text-right">Amount</th>
                <th class="px-4 py-3 text-right">Due</th>
                <th class="px-4 py-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($bills as $bill)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5">
                    <a href="{{ route('tenant.accounting.bills.show', [$tenant->slug, $bill->id]) }}" class="font-mono text-blue-600 hover:underline text-xs">{{ $bill->bill_number }}</a>
                </td>
                <td class="px-4 py-2.5 text-gray-700">{{ $bill->vendor_name }}</td>
                <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $bill->bill_date }}</td>
                <td class="px-4 py-2.5 text-xs {{ $bill->due_date < date('Y-m-d') && $bill->amount_due > 0 ? 'text-red-600 font-medium' : 'text-gray-500' }}">{{ $bill->due_date }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($bill->total_amount, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs {{ $bill->amount_due > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ number_format($bill->amount_due, 2) }}</td>
                <td class="px-4 py-2.5 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        @if($bill->status==='PAID') bg-green-50 text-green-700
                        @elseif($bill->status==='APPROVED') bg-blue-50 text-blue-700
                        @elseif($bill->status==='PARTIAL') bg-yellow-50 text-yellow-700
                        @elseif($bill->status==='DRAFT') bg-gray-100 text-gray-600
                        @else bg-red-50 text-red-600 @endif">
                        {{ $bill->status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No bills found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
