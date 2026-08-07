@extends('layouts.tenant')
@section('title', 'Income Statement — ' . $tenant->name)
@section('page-title', 'Income Statement')
@section('page-subtitle', $from->format('d M Y') . ' — ' . $to->format('d M Y'))

@section('content')

<form method="GET" class="mb-4 flex items-center gap-2">
    <label class="text-xs font-medium text-gray-600">From</label>
    <input type="date" name="from" value="{{ $from->toDateString() }}" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg">
    <label class="text-xs font-medium text-gray-600">To</label>
    <input type="date" name="to" value="{{ $to->toDateString() }}" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg">
    <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Apply</button>
    <a href="{{ route('tenant.accounting.reports.income-statement.pdf', $tenant->slug) }}?from={{ $from->toDateString() }}&to={{ $to->toDateString() }}" target="_blank"
       class="ml-auto px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
    <a href="{{ route('tenant.accounting.reports.income-statement.csv', $tenant->slug) }}?from={{ $from->toDateString() }}&to={{ $to->toDateString() }}"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
</form>

<div class="bg-white rounded-xl border border-gray-200 p-5 max-w-2xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Income</h3>
    <table class="w-full text-sm mb-5">
        <tbody class="divide-y divide-gray-50">
            @foreach($income as $row)
            <tr>
                <td class="py-1.5 text-gray-600">{{ $row['account']->name }}</td>
                <td class="py-1.5 text-right font-mono">{{ number_format($row['balance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-200 font-semibold text-gray-800">
                <td class="pt-2">Total Income</td>
                <td class="pt-2 text-right font-mono">{{ number_format($totalIncome, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <h3 class="text-sm font-semibold text-gray-700 mb-3">Expenses</h3>
    <table class="w-full text-sm mb-5">
        <tbody class="divide-y divide-gray-50">
            @foreach($expense as $row)
            <tr>
                <td class="py-1.5 text-gray-600">{{ $row['account']->name }}</td>
                <td class="py-1.5 text-right font-mono">{{ number_format($row['balance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-200 font-semibold text-gray-800">
                <td class="pt-2">Total Expenses</td>
                <td class="pt-2 text-right font-mono">{{ number_format($totalExpense, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="flex items-center justify-between pt-3 border-t border-gray-200 text-base font-bold {{ $netIncome >= 0 ? 'text-green-700' : 'text-red-600' }}">
        <span>Net {{ $netIncome >= 0 ? 'Profit' : 'Loss' }}</span>
        <span class="font-mono">{{ number_format(abs($netIncome), 2) }} AED</span>
    </div>
</div>

@endsection
