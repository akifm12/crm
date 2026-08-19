@extends('layouts.tenant')
@section('title', 'VAT Return — ' . $tenant->name)
@section('page-title', 'VAT Return')
@section('page-subtitle', 'Output VAT (sales) minus Input VAT (bills) for the period')

@section('content')

<form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
        <input type="date" name="from" value="{{ $from->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
        <input type="date" name="to" value="{{ $to->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
    </div>
    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Apply</button>
</form>

<p class="text-xs text-gray-500 mb-5">Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</p>

<div class="grid grid-cols-3 gap-4 mb-6 max-w-3xl">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 mb-1">Output VAT (sales)</p>
        <p class="text-2xl font-bold font-mono text-gray-800">{{ number_format($outputVat, 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">on {{ number_format($outputSubtotal, 2) }} taxable sales</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 mb-1">Input VAT (bills)</p>
        <p class="text-2xl font-bold font-mono text-green-700">{{ number_format($inputVat, 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">on {{ number_format($inputSubtotal, 2) }} expenses</p>
    </div>
    <div class="bg-white rounded-xl border border-{{ $netVat >= 0 ? 'red' : 'green' }}-200 p-5 {{ $netVat >= 0 ? 'bg-red-50' : 'bg-green-50' }}">
        <p class="text-xs font-medium text-gray-500 mb-1">Net VAT {{ $netVat >= 0 ? 'payable' : 'refundable' }}</p>
        <p class="text-2xl font-bold font-mono {{ $netVat >= 0 ? 'text-red-700' : 'text-green-700' }}">{{ number_format(abs($netVat), 2) }}</p>
        <p class="text-xs text-gray-400 mt-1">Output − Input</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden max-w-3xl">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Box</th>
                <th class="px-5 py-3 text-right">Taxable (AED)</th>
                <th class="px-5 py-3 text-right">VAT (AED)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <tr>
                <td class="px-5 py-3">
                    <p class="font-medium text-gray-800">Output VAT</p>
                    <p class="text-xs text-gray-400">Standard-rated sales and making charges</p>
                </td>
                <td class="px-5 py-3 text-right font-mono">{{ number_format($outputSubtotal, 2) }}</td>
                <td class="px-5 py-3 text-right font-mono font-semibold">{{ number_format($outputVat, 2) }}</td>
            </tr>
            <tr class="bg-gray-50">
                <td class="px-5 py-3">
                    <p class="font-medium text-gray-800">Input VAT (recoverable)</p>
                    <p class="text-xs text-gray-400">Standard-rated purchases and expenses</p>
                </td>
                <td class="px-5 py-3 text-right font-mono">{{ number_format($inputSubtotal, 2) }}</td>
                <td class="px-5 py-3 text-right font-mono font-semibold text-green-700">{{ number_format($inputVat, 2) }}</td>
            </tr>
            <tr class="border-t-2 border-gray-200 font-semibold">
                <td class="px-5 py-3 text-gray-800">Net VAT {{ $netVat >= 0 ? 'payable to FTA' : 'refundable from FTA' }}</td>
                <td class="px-5 py-3"></td>
                <td class="px-5 py-3 text-right font-mono {{ $netVat >= 0 ? 'text-red-700' : 'text-green-700' }}">
                    {{ number_format(abs($netVat), 2) }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

<p class="text-xs text-gray-400 mt-4 max-w-3xl">
    This report covers posted invoices and posted bills in the selected period only.
    Zero-rated, exempt, and reverse-charge lines are excluded from both output and input VAT totals.
    Always reconcile against the FTA portal before filing.
</p>

@endsection
