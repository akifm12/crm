@extends('layouts.tenant')
@section('title', 'Trial Balance — ' . $tenant->name)
@section('page-title', 'Trial Balance')
@section('page-subtitle', 'Posted transactions from ' . $dateFrom . ' to ' . $dateTo)

@section('content')
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">From</label>
        <input type="date" name="date_from" value="{{ $dateFrom }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">To</label>
        <input type="date" name="date_to" value="{{ $dateTo }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
    </div>
    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Run</button>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Code</th>
                <th class="px-4 py-3 text-left">Account</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-right w-36">Total Debit</th>
                <th class="px-4 py-3 text-right w-36">Total Credit</th>
                <th class="px-4 py-3 text-right w-36">Balance</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php $sumDr = 0; $sumCr = 0; @endphp
            @forelse($accounts as $acc)
            @php
                $b = $balances[$acc->id] ?? null;
                $dr = $b ? $b->total_dr : 0;
                $cr = $b ? $b->total_cr : 0;
                if ($dr == 0 && $cr == 0) continue;
                $bal = $dr - $cr;
                $sumDr += $dr; $sumCr += $cr;
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-mono text-xs text-gray-400">{{ $acc->code }}</td>
                <td class="px-4 py-2.5 text-gray-800">{{ $acc->name }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $acc->account_type }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($dr, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($cr, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs {{ $bal < 0 ? 'text-red-600' : 'text-gray-800' }}">{{ number_format(abs($bal), 2) }} {{ $bal < 0 ? 'Cr' : 'Dr' }}</td>
            </tr>
            @empty
            @endforelse
        </tbody>
        <tfoot class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
            <tr>
                <td colspan="3" class="px-4 py-3">Total</td>
                <td class="px-4 py-3 text-right font-mono">{{ number_format($sumDr, 2) }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ number_format($sumCr, 2) }}</td>
                <td class="px-4 py-3 text-right font-mono {{ abs($sumDr - $sumCr) > 0.01 ? 'text-red-600' : 'text-green-700' }}">
                    {{ abs($sumDr - $sumCr) <= 0.01 ? '✓ Balanced' : 'OUT OF BALANCE: ' . number_format(abs($sumDr - $sumCr), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
