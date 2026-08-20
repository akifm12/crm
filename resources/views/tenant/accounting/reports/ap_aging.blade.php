@extends('layouts.tenant')
@section('title', 'AP Aging — ' . $tenant->name)
@section('page-title', 'Accounts Payable Aging')
@section('page-subtitle', 'Outstanding bills as of ' . now()->format('d M Y'))

@section('content')

<div class="mb-4 flex items-center gap-2 justify-end">
    <a href="{{ route('tenant.accounting.reports.ap-aging.pdf', $tenant->slug) }}" target="_blank"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
    <a href="{{ route('tenant.accounting.reports.ap-aging.csv', $tenant->slug) }}"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
</div>

{{-- Bucket summary --}}
<div class="grid grid-cols-5 gap-3 mb-5">
    @php
        $buckets = [
            'current' => 'Current',
            '1_30'    => '1–30 days',
            '31_60'   => '31–60 days',
            '61_90'   => '61–90 days',
            '90_plus' => '90+ days',
        ];
    @endphp
    @foreach($buckets as $key => $label)
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center {{ $totals[$key] > 0 ? '' : 'opacity-50' }}">
        <div class="text-xs font-medium text-gray-500 mb-1">{{ $label }}</div>
        <div class="text-base font-semibold font-mono text-gray-900">AED {{ number_format($totals[$key], 2) }}</div>
    </div>
    @endforeach
</div>

@if($rows->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">
        No outstanding payables.
    </div>
@else
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-4 py-3">Bill #</th>
                <th class="px-4 py-3">Supplier</th>
                <th class="px-4 py-3">Bill Date</th>
                <th class="px-4 py-3">Due Date</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-right">Paid</th>
                <th class="px-4 py-3 text-right">Balance</th>
                <th class="px-4 py-3 text-center">Age</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($rows as $row)
            @php
                $bill = $row['bill'];
                $over = $row['days_over'];
                $ageLabel = ($over === null || $over <= 0) ? 'Current' : $over . 'd';
                $ageClass = match($row['bucket']) {
                    'current' => 'bg-green-50 text-green-700',
                    '1_30'    => 'bg-yellow-50 text-yellow-700',
                    '31_60'   => 'bg-orange-50 text-orange-700',
                    default   => 'bg-red-50 text-red-700',
                };
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-mono text-gray-900">{{ $bill->bill_number }}</td>
                <td class="px-4 py-2.5 text-gray-700">{{ $bill->supplier_name }}</td>
                <td class="px-4 py-2.5 text-gray-600">{{ $bill->bill_date->format('d M Y') }}</td>
                <td class="px-4 py-2.5 text-gray-600">{{ $bill->due_date?->format('d M Y') ?? '—' }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-gray-700">{{ number_format($bill->total, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-gray-500">{{ number_format($bill->amount_paid, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-900">{{ number_format($row['balance'], 2) }}</td>
                <td class="px-4 py-2.5 text-center">
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $ageClass }}">{{ $ageLabel }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t-2 border-gray-200 bg-gray-50 font-semibold">
                <td class="px-4 py-3" colspan="6">Total Outstanding</td>
                <td class="px-4 py-3 text-right font-mono">AED {{ number_format($grandTotal, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@endsection
