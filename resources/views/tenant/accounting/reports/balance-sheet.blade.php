@extends('layouts.tenant')
@section('title', 'Balance Sheet — ' . $tenant->name)
@section('page-title', 'Balance Sheet')
@section('page-subtitle', 'As of ' . $asOfDate)

@section('content')
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">As of Date</label>
        <input type="date" name="as_of" value="{{ $asOfDate }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
    </div>
    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Run</button>
</form>

@php
    $grandTotals = [];
@endphp

@foreach(['ASSET','LIABILITY','EQUITY'] as $type)
@php $typeAccounts = $accounts->where('account_type', $type); @endphp
@if($typeAccounts->isNotEmpty() || $type === 'EQUITY')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
    <div class="px-4 py-2.5 bg-gray-50 font-semibold text-sm text-gray-700 uppercase">{{ $type }}</div>
    <table class="w-full text-sm">
        <tbody class="divide-y divide-gray-100">
            @php $typeTotal = 0; @endphp
            @foreach($typeAccounts as $acc)
            @php
                $b = $balances[$acc->id] ?? null;
                $dr = $b ? $b->total_dr : 0;
                $cr = $b ? $b->total_cr : 0;
                $bal = $acc->normal_balance === 'DEBIT' ? ($dr - $cr) : ($cr - $dr);
                $typeTotal += $bal;
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5"><span class="font-mono text-xs text-gray-400 mr-2">{{ $acc->code }}</span>{{ $acc->name }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-sm {{ $bal < 0 ? 'text-red-600' : '' }}">{{ number_format($bal, 2) }}</td>
            </tr>
            @endforeach
            @if($type === 'EQUITY')
            {{-- Current period net income flows to equity --}}
            <tr class="hover:bg-gray-50 italic text-gray-500">
                <td class="px-4 py-2.5 pl-8">Current Period Net Income / (Loss)</td>
                <td class="px-4 py-2.5 text-right font-mono text-sm {{ $netIncome < 0 ? 'text-red-600' : 'text-green-700' }}">{{ number_format($netIncome, 2) }}</td>
            </tr>
            @php $typeTotal += $netIncome; @endphp
            @endif
        </tbody>
        <tfoot class="border-t-2 border-gray-200 bg-gray-50 font-semibold">
            <tr>
                <td class="px-4 py-2">Total {{ $type }}</td>
                <td class="px-4 py-2 text-right font-mono {{ $typeTotal < 0 ? 'text-red-600' : '' }}">{{ number_format($typeTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@php $grandTotals[$type] = $typeTotal; @endphp
@endif
@endforeach

@php
    $totalAssets = $grandTotals['ASSET'] ?? 0;
    $totalLiabEquity = ($grandTotals['LIABILITY'] ?? 0) + ($grandTotals['EQUITY'] ?? 0);
    $diff = round($totalAssets - $totalLiabEquity, 2);
@endphp
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
    <table class="w-full text-sm font-semibold">
        <tbody class="divide-y divide-gray-100">
            <tr class="bg-gray-50">
                <td class="px-4 py-2.5">Total Assets</td>
                <td class="px-4 py-2.5 text-right font-mono {{ $totalAssets < 0 ? 'text-red-600' : '' }}">{{ number_format($totalAssets, 2) }}</td>
            </tr>
            <tr class="bg-gray-50">
                <td class="px-4 py-2.5">Total Liabilities + Equity</td>
                <td class="px-4 py-2.5 text-right font-mono">{{ number_format($totalLiabEquity, 2) }}</td>
            </tr>
            @if(abs($diff) > 0.01)
            <tr class="bg-red-50">
                <td class="px-4 py-2.5 text-red-700">⚠ Out of Balance by</td>
                <td class="px-4 py-2.5 text-right font-mono text-red-700">{{ number_format(abs($diff), 2) }}</td>
            </tr>
            @else
            <tr class="bg-green-50">
                <td class="px-4 py-2.5 text-green-700">✓ Balanced</td>
                <td class="px-4 py-2.5 text-right font-mono text-green-700">0.00</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

@if(!$accounts->where('account_type','LIABILITY')->count())
<p class="text-xs text-gray-400 mt-1">No liability accounts exist in the chart of accounts — all purchases were cash on delivery.</p>
@endif
@endsection
