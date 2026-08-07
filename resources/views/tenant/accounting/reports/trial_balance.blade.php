@extends('layouts.tenant')
@section('title', 'Trial Balance — ' . $tenant->name)
@section('page-title', 'Trial Balance')
@section('page-subtitle', 'As of ' . $asOf->format('d M Y'))

@section('content')

<form method="GET" class="mb-4 flex items-center gap-2">
    <label class="text-xs font-medium text-gray-600">As of</label>
    <input type="date" name="as_of" value="{{ $asOf->toDateString() }}"
           class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg" onchange="this.form.submit()">
    <a href="{{ route('tenant.accounting.reports.trial-balance.pdf', $tenant->slug) }}?as_of={{ $asOf->toDateString() }}" target="_blank"
       class="ml-auto px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
    <a href="{{ route('tenant.accounting.reports.trial-balance.csv', $tenant->slug) }}?as_of={{ $asOf->toDateString() }}"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Code</th>
                <th class="px-5 py-3">Account</th>
                <th class="px-5 py-3 text-right">Debit</th>
                <th class="px-5 py-3 text-right">Credit</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($rows as $row)
            @continue($row['debit'] == 0 && $row['credit'] == 0)
            <tr>
                <td class="px-5 py-2.5 font-mono text-xs text-gray-500">{{ $row['account']->code }}</td>
                <td class="px-5 py-2.5 text-gray-700">{{ $row['account']->name }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-200 text-sm font-semibold text-gray-700">
                <td colspan="2" class="px-5 py-3 text-right">Totals</td>
                <td class="px-5 py-3 text-right font-mono">{{ number_format($totalDebit, 2) }}</td>
                <td class="px-5 py-3 text-right font-mono">{{ number_format($totalCredit, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

@if(abs($totalDebit - $totalCredit) > 0.01)
<div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
    Trial balance does not balance — debits and credits differ by {{ number_format(abs($totalDebit - $totalCredit), 2) }} AED. This should not happen; please review recent journal entries.
</div>
@endif

@endsection
