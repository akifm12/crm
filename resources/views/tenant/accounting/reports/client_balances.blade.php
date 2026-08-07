@extends('layouts.tenant')
@section('title', 'Client Balances — ' . $tenant->name)
@section('page-title', 'Client Balances')
@section('page-subtitle', 'Accounts Receivable and Sales Revenue, broken out per corporate client')

@section('content')

<div class="flex items-start justify-between gap-4 mb-4">
    <p class="text-xs text-gray-400 max-w-2xl">
        The Trial Balance keeps Accounts Receivable and Sales Revenue as single control accounts (standard practice —
        one row per customer would make it unreadable). This report is the subsidiary-ledger detail behind those
        control accounts: each corporate client gets its own row; individuals are combined into one row below.
    </p>
    <div class="flex gap-2 shrink-0">
        <a href="{{ route('tenant.accounting.reports.client-balances.pdf', $tenant->slug) }}" target="_blank"
           class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
        <a href="{{ route('tenant.accounting.reports.client-balances.csv', $tenant->slug) }}"
           class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Client</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3 text-right">Balance (AED)</th>
                <th class="px-5 py-3 text-right">Sales Revenue (AED)</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($corporate as $row)
            <tr>
                <td class="px-5 py-2.5 text-gray-800 font-medium">{{ $row['client']->displayName() }}</td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ str_replace('_', ' ', $row['client']->client_type) }}</td>
                <td class="px-5 py-2.5 text-right font-mono {{ $row['balance'] < 0 ? 'text-red-600' : 'text-gray-700' }}">{{ number_format($row['balance'], 2) }}</td>
                <td class="px-5 py-2.5 text-right font-mono text-gray-700">{{ number_format($row['sales_revenue'], 2) }}</td>
                <td class="px-5 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.reports.client-statement', $tenant->slug) }}?client_id={{ $row['client']->id }}" class="text-xs text-blue-600 hover:underline">Statement</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-6 text-center text-sm text-gray-400">No corporate client activity posted yet.</td></tr>
            @endforelse

            @if($individuals['balance'] != 0 || $individuals['sales_revenue'] != 0)
            <tr class="bg-gray-50">
                <td class="px-5 py-2.5 text-gray-800 font-medium">Individuals (combined)</td>
                <td class="px-5 py-2.5 text-gray-500">—</td>
                <td class="px-5 py-2.5 text-right font-mono {{ $individuals['balance'] < 0 ? 'text-red-600' : 'text-gray-700' }}">{{ number_format($individuals['balance'], 2) }}</td>
                <td class="px-5 py-2.5 text-right font-mono text-gray-700">{{ number_format($individuals['sales_revenue'], 2) }}</td>
                <td class="px-5 py-2.5"></td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-200 text-sm font-semibold text-gray-700">
                <td colspan="2" class="px-5 py-3 text-right">Totals</td>
                <td class="px-5 py-3 text-right font-mono">{{ number_format($corporate->sum('balance') + $individuals['balance'], 2) }}</td>
                <td class="px-5 py-3 text-right font-mono">{{ number_format($corporate->sum('sales_revenue') + $individuals['sales_revenue'], 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<p class="text-xs text-gray-400">
    Balance: positive means the client owes the dealer (matches the running balance on that client's Statement of
    Accounts); negative means the dealer owes the client. Sales Revenue includes making charges credited to the
    same metal's Sales Revenue account, across all posted sale/exchange invoices.
</p>

@endsection
