@extends('layouts.tenant')
@section('title', 'AP Aging — ' . $tenant->name)
@section('page-title', 'AP Aging Report')
@section('page-subtitle', 'Outstanding bills as of ' . $asOfDate)

@section('content')
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div><label class="block text-xs text-gray-500 mb-1">As of Date</label><input type="date" name="as_of" value="{{ $asOfDate }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5"></div>
    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Run</button>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Bill #</th>
                <th class="px-4 py-3 text-left">Supplier</th>
                <th class="px-4 py-3 text-left">Bill Date</th>
                <th class="px-4 py-3 text-left">Due Date</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-right">Due</th>
                <th class="px-4 py-3 text-right">Days Overdue</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php $totalDue = 0; @endphp
            @forelse($bills as $bill)
            @php
                $daysOverdue = (int) \Carbon\Carbon::parse($bill->due_date)->diffInDays(\Carbon\Carbon::parse($asOfDate), false) * -1;
                $totalDue += $bill->amount_due;
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-mono text-xs">
                    <a href="{{ route('tenant.accounting.bills.show', [$tenant->slug, $bill->id]) }}" class="text-blue-600 hover:underline">{{ $bill->bill_number }}</a>
                </td>
                <td class="px-4 py-2.5">{{ $bill->supplier_name }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $bill->bill_date }}</td>
                <td class="px-4 py-2.5 text-xs text-red-600">{{ $bill->due_date }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($bill->total_amount ?: $bill->total, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs text-red-600">{{ number_format($bill->amount_due, 2) }}</td>
                <td class="px-4 py-2.5 text-right text-xs {{ $daysOverdue > 60 ? 'text-red-600 font-semibold' : ($daysOverdue > 30 ? 'text-orange-500' : 'text-gray-500') }}">
                    {{ $daysOverdue }} days
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No overdue bills.</td></tr>
            @endforelse
        </tbody>
        @if($bills->isNotEmpty())
        <tfoot class="border-t-2 border-gray-200 bg-gray-50 font-semibold">
            <tr>
                <td colspan="5" class="px-4 py-3">Total Due</td>
                <td class="px-4 py-3 text-right font-mono text-red-600">{{ number_format($totalDue, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@endsection
