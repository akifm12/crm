@extends('layouts.tenant')
@section('title', 'VAT Summary — ' . $tenant->name)
@section('page-title', 'VAT Summary')
@section('page-subtitle', $dateFrom . ' to ' . $dateTo)

@section('content')
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div><label class="block text-xs text-gray-500 mb-1">From</label><input type="date" name="date_from" value="{{ $dateFrom }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5"></div>
    <div><label class="block text-xs text-gray-500 mb-1">To</label><input type="date" name="date_to" value="{{ $dateTo }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5"></div>
    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Run</button>
</form>

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400 mb-1">Input VAT (Purchases)</p>
        <p class="text-2xl font-bold text-gray-800">AED {{ number_format($inputVat, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400 mb-1">Output VAT (Sales)</p>
        <p class="text-2xl font-bold text-gray-800">AED {{ number_format($outputVat, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400 mb-1">Net VAT {{ $outputVat >= $inputVat ? 'Payable' : 'Refundable' }}</p>
        <p class="text-2xl font-bold {{ $outputVat >= $inputVat ? 'text-red-600' : 'text-green-700' }}">AED {{ number_format(abs($outputVat - $inputVat), 2) }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 font-semibold text-sm text-gray-700">Purchase Bills with VAT</div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Bill #</th>
                <th class="px-4 py-3 text-left">Supplier</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-right">Net</th>
                <th class="px-4 py-3 text-right">VAT</th>
                <th class="px-4 py-3 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($bills->where('total_vat_amount','>', 0) as $bill)
            <tr>
                <td class="px-4 py-2.5 font-mono text-xs">{{ $bill->bill_number }}</td>
                <td class="px-4 py-2.5">{{ $bill->supplier_name }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $bill->bill_date }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($bill->subtotal, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($bill->total_vat_amount, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($bill->total_amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No VAT transactions found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
