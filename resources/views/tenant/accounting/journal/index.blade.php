@extends('layouts.tenant')
@section('title', 'Journal — ' . $tenant->name)
@section('page-title', 'Journal Entries')
@section('page-subtitle', 'General ledger journal')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs text-gray-500 mb-1">From</label>
        <input type="date" name="date_from" value="{{ $dateFrom }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">To</label>
        <input type="date" name="date_to" value="{{ $dateTo }}" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Status</label>
        <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
            <option value="all" @selected($status==='all')>All</option>
            <option value="DRAFT" @selected($status==='DRAFT')>Draft</option>
            <option value="POSTED" @selected($status==='POSTED')>Posted</option>
            <option value="VOID" @selected($status==='VOID')>Void</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Reference</label>
        <input type="text" name="reference" value="{{ $reference ?? '' }}" placeholder="Search…" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
    </div>
    <button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">Filter</button>
    <a href="{{ route('tenant.accounting.journal.index', $tenant->slug) }}" class="px-4 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Reset</a>
    <div class="ml-auto">
        <a href="#new-entry" class="px-4 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">+ New Entry</a>
    </div>
</form>

{{-- Summary --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Entries</p>
        <p class="text-xl font-bold text-gray-800">{{ $journals->count() }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Total Debit</p>
        <p class="text-xl font-bold text-gray-800">{{ number_format($totalDebit, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Total Credit</p>
        <p class="text-xl font-bold text-gray-800">{{ number_format($totalCredit, 2) }}</p>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-8">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Entry #</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Description</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-right">Debit</th>
                <th class="px-4 py-3 text-right">Credit</th>
                <th class="px-4 py-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($journals as $j)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5">
                    <a href="{{ route('tenant.accounting.journal.show', [$tenant->slug, $j->id]) }}" class="font-mono text-blue-600 hover:underline text-xs">{{ $j->entry_number }}</a>
                </td>
                <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $j->entry_date }}</td>
                <td class="px-4 py-2.5 text-gray-700">{{ Str::limit($j->description, 60) }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ str_replace('_', ' ', $j->journal_type) }}</td>
                @php $jt = $lineRows[$j->id] ?? null; @endphp
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($jt->dr ?? 0, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($jt->cr ?? 0, 2) }}</td>
                <td class="px-4 py-2.5 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        @if($j->status==='POSTED') bg-green-50 text-green-700
                        @elseif($j->status==='DRAFT') bg-yellow-50 text-yellow-700
                        @else bg-gray-100 text-gray-500 @endif">
                        {{ $j->status }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No entries found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- New Entry Form --}}
<div id="new-entry" class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-800 mb-5">New Journal Entry</h3>
    <form action="{{ route('tenant.accounting.journal.store', $tenant->slug) }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date *</label>
                <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="journal_type" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="GENERAL">General</option>
                    <option value="CASH_PAYMENT">Cash Payment</option>
                    <option value="BANK_PAYMENT">Bank Payment</option>
                    <option value="CASH_RECEIPT">Cash Receipt</option>
                    <option value="BANK_RECEIPT">Bank Receipt</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Reference</label>
                <input type="text" name="reference" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" placeholder="PV-001 / INV-123">
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-xs font-medium text-gray-600 mb-1">Description *</label>
            <input type="text" name="description" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
        </div>

        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm" id="je-lines">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Account</th>
                        <th class="px-3 py-2 text-left">Description</th>
                        <th class="px-3 py-2 text-right w-32">Debit (AED)</th>
                        <th class="px-3 py-2 text-right w-32">Credit (AED)</th>
                        <th class="px-3 py-2 w-8"></th>
                    </tr>
                </thead>
                <tbody id="je-lines-body">
                    @for($i = 0; $i < 3; $i++)
                    <tr class="border-t border-gray-100">
                        <td class="px-2 py-1">
                            <select name="accounts[]" class="w-full text-xs border border-gray-200 rounded px-2 py-1">
                                <option value="">— select —</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-2 py-1"><input type="text" name="line_descriptions[]" class="w-full text-xs border border-gray-200 rounded px-2 py-1"></td>
                        <td class="px-2 py-1"><input type="number" name="debits[]" step="0.01" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right"></td>
                        <td class="px-2 py-1"><input type="number" name="credits[]" step="0.01" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right"></td>
                        <td class="px-2 py-1 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 text-xs">×</button></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between">
            <button type="button" onclick="addJeLine()" class="text-sm text-blue-600 hover:underline">+ Add line</button>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="auto_post" value="1"> Post immediately
                </label>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Save Entry
                </button>
            </div>
        </div>
    </form>
</div>

<template id="je-line-template">
    <tr class="border-t border-gray-100">
        <td class="px-2 py-1">
            <select name="accounts[]" class="w-full text-xs border border-gray-200 rounded px-2 py-1">
                <option value="">— select —</option>
                @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                @endforeach
            </select>
        </td>
        <td class="px-2 py-1"><input type="text" name="line_descriptions[]" class="w-full text-xs border border-gray-200 rounded px-2 py-1"></td>
        <td class="px-2 py-1"><input type="number" name="debits[]" step="0.01" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right"></td>
        <td class="px-2 py-1"><input type="number" name="credits[]" step="0.01" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right"></td>
        <td class="px-2 py-1 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-600 text-xs">×</button></td>
    </tr>
</template>

<script>
function addJeLine() {
    const tpl = document.getElementById('je-line-template');
    const clone = tpl.content.cloneNode(true);
    document.getElementById('je-lines-body').appendChild(clone);
}
</script>
@endsection
