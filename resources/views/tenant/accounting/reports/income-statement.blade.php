@extends('layouts.tenant')
@section('title', 'Income Statement — ' . $tenant->name)
@section('page-title', 'Income Statement')
@section('page-subtitle', $dateFrom . ' to ' . $dateTo)

@section('content')
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div><label class="block text-xs text-gray-500 mb-1">From</label><input type="date" name="date_from" value="{{ $dateFrom }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5"></div>
    <div><label class="block text-xs text-gray-500 mb-1">To</label><input type="date" name="date_to" value="{{ $dateTo }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5"></div>
    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Run</button>
</form>

@php $revenue = 0; $expense = 0; @endphp
@foreach(['REVENUE','EXPENSE'] as $type)
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
                if ($type === 'REVENUE') $revenue += $bal; else $expense += $bal;
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

<div class="bg-white rounded-xl border border-gray-200 p-4">
    <div class="flex justify-between text-sm font-bold {{ ($revenue - $expense) >= 0 ? 'text-green-700' : 'text-red-600' }}">
        <span>Net {{ ($revenue - $expense) >= 0 ? 'Profit' : 'Loss' }}</span>
        <span class="font-mono">AED {{ number_format(abs($revenue - $expense), 2) }}</span>
    </div>
</div>
@endsection
