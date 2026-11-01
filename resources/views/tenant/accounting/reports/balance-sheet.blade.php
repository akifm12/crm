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

@foreach(['ASSET','LIABILITY','EQUITY'] as $type)
@php $typeAccounts = $accounts->where('account_type', $type); @endphp
@if($typeAccounts->isNotEmpty())
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
                <td class="px-4 py-2.5 text-right font-mono text-sm">{{ number_format($bal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t-2 border-gray-200 bg-gray-50 font-semibold">
            <tr><td class="px-4 py-2">Total {{ $type }}</td><td class="px-4 py-2 text-right font-mono">{{ number_format($typeTotal, 2) }}</td></tr>
        </tfoot>
    </table>
</div>
@endif
@endforeach
@endsection
