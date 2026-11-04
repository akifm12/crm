@extends('layouts.tenant')
@section('title', 'VAT Filings — ' . $tenant->name)
@section('page-title', 'VAT Filings')
@section('page-subtitle', 'UAE VAT return history')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div></div>
    <a href="{{ route('tenant.accounting.vat-filings.create', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + New filing
    </a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Period</th>
                <th class="px-5 py-3">Due Date</th>
                <th class="px-5 py-3 text-right">VAT on Sales</th>
                <th class="px-5 py-3 text-right">VAT on Purchases</th>
                <th class="px-5 py-3 text-right">Net VAT</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($filings as $filing)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5 text-gray-800 font-medium">
                    {{ $filing->period_from->format('d M Y') }} — {{ $filing->period_to->format('d M Y') }}
                </td>
                <td class="px-5 py-2.5 text-gray-500">
                    {{ $filing->due_date ? $filing->due_date->format('d M Y') : '—' }}
                </td>
                <td class="px-5 py-2.5 text-right">{{ number_format($filing->vat_on_sales, 2) }}</td>
                <td class="px-5 py-2.5 text-right">{{ number_format($filing->vat_on_purchases, 2) }}</td>
                <td class="px-5 py-2.5 text-right font-semibold {{ $filing->net_vat_payable >= 0 ? 'text-red-600' : 'text-green-700' }}">
                    {{ number_format($filing->net_vat_payable, 2) }}
                </td>
                <td class="px-5 py-2.5">
                    @php $sc = match($filing->status) {'submitted'=>'bg-blue-50 text-blue-700','paid'=>'bg-green-50 text-green-700',default=>'bg-amber-50 text-amber-700'}; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $sc }}">{{ ucfirst($filing->status) }}</span>
                </td>
                <td class="px-5 py-2.5">
                    <a href="{{ route('tenant.accounting.vat-filings.show', [$tenant->slug, $filing->id]) }}"
                       class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No VAT filings yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
