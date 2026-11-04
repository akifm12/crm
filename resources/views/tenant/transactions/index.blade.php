@extends('layouts.tenant')
@section('title', 'Transactions — ' . $tenant->name)
@section('page-title', 'Transactions')
@section('page-subtitle', 'All client transactions')

@section('content')
@php $typeLabels = $sector['transaction_types'] ?? ['buy'=>'Buy','sell'=>'Sell','exchange'=>'Exchange','other'=>'Other']; @endphp
<div style="padding:24px" class="space-y-4">
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">Client, invoice no. or note</label>
            <input type="text" name="q" value="{{ request('q') }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
            <select name="type" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="">All</option>
                @foreach($types as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ $typeLabels[$t] ?? ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Min amount</label>
                <input type="number" step="0.01" name="min" value="{{ request('min') }}" class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Max amount</label>
                <input type="number" step="0.01" name="max" value="{{ request('max') }}" class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
        </div>
        <div class="col-span-2 md:col-span-6 flex items-center gap-3">
            <button class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Filter</button>
            <a href="{{ route('tenant.transactions.index', $tenant->slug) }}" class="text-sm text-gray-500 hover:underline">Reset</a>
            <a href="{{ route('tenant.transactions.index', $tenant->slug) }}?{{ http_build_query(array_merge(request()->query(), ['export' => 'csv', 'page' => null])) }}" class="ml-auto text-sm text-blue-600 hover:underline">Download CSV</a>
        </div>
    </form>

    <p class="text-sm text-gray-600">{{ number_format($count) }} transaction{{ $count === 1 ? '' : 's' }} &middot; total <span class="font-semibold">{{ number_format($total, 2) }}</span></p>

    <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-4 py-3">Date</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Invoice no.</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($transactions as $t)
                <tr>
                    <td class="px-4 py-2.5 whitespace-nowrap">{{ $t->visit_date?->format('d M Y') }}</td>
                    <td class="px-4 py-2.5">
                        @if($t->client)
                        <a href="{{ route('tenant.clients.show', [$tenant->slug, $t->client->id]) }}?tab=transactions" class="text-blue-600 hover:underline">{{ $t->client->displayName() }}</a>
                        @if($t->client->trashed()) <span class="text-xs text-gray-400">(archived)</span> @endif
                        @else — @endif
                    </td>
                    <td class="px-4 py-2.5">{{ $t->transaction_type ? ($typeLabels[$t->transaction_type] ?? ucfirst($t->transaction_type)) : '—' }}</td>
                    <td class="px-4 py-2.5">{{ $t->invoice_number ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ $t->invoice_amount !== null ? number_format($t->invoice_amount, 2) : '—' }}</td>
                    <td class="px-4 py-2.5 text-gray-500 max-w-xs truncate">{{ $t->notes }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No transactions match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $transactions->links() }}
</div>
@endsection
