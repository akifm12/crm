@extends('layouts.tenant')
@section('title', 'VAT Summary — ' . $tenant->name)
@section('page-title', 'VAT Summary')
@section('page-subtitle', $from->format('d M Y') . ' — ' . $to->format('d M Y'))

@section('content')

<form method="GET" class="mb-4 flex items-center gap-2">
    <label class="text-xs font-medium text-gray-600">From</label>
    <input type="date" name="from" value="{{ $from->toDateString() }}" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg">
    <label class="text-xs font-medium text-gray-600">To</label>
    <input type="date" name="to" value="{{ $to->toDateString() }}" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg">
    <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Apply</button>
    <a href="{{ route('tenant.accounting.reports.vat-summary.pdf', $tenant->slug) }}?from={{ $from->toDateString() }}&to={{ $to->toDateString() }}" target="_blank"
       class="ml-auto px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
    <a href="{{ route('tenant.accounting.reports.vat-summary.csv', $tenant->slug) }}?from={{ $from->toDateString() }}&to={{ $to->toDateString() }}"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden max-w-2xl">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">VAT treatment</th>
                <th class="px-5 py-3 text-right">Subtotal (AED)</th>
                <th class="px-5 py-3 text-right">VAT (AED)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
            <tr>
                <td class="px-5 py-2.5 text-gray-700 capitalize">{{ str_replace('_', ' ', $row['vat_treatment']) }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($row['subtotal'], 2) }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($row['vat'], 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-5 py-6 text-center text-sm text-gray-400">No posted invoices in this period.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-200 text-sm font-semibold text-gray-700">
                <td class="px-5 py-3">Totals</td>
                <td class="px-5 py-3 text-right font-mono">{{ number_format($totalSubtotal, 2) }}</td>
                <td class="px-5 py-3 text-right font-mono">{{ number_format($totalVat, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection
