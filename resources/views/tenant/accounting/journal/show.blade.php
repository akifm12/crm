@extends('layouts.tenant')
@section('title', $entry->entry_number . ' — Journal')
@section('page-title', $entry->entry_number)
@section('page-subtitle', $entry->entry_date . ' · ' . str_replace('_', ' ', $entry->journal_type))

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Account</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-right w-32">Debit</th>
                        <th class="px-4 py-3 text-right w-32">Credit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($lines as $line)
                    <tr class="{{ $line->debit_amount > 0 ? '' : 'pl-8' }}">
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-500">
                            {{ $line->account->code ?? '—' }}
                            <span class="text-gray-700 font-sans ml-1">{{ $line->account->name ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 text-xs">{{ $line->description }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ $line->debit_amount > 0 ? number_format($line->debit_amount, 2) : '' }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs text-gray-500">{{ $line->credit_amount > 0 ? number_format($line->credit_amount, 2) : '' }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-semibold text-sm">
                        <td class="px-4 py-2" colspan="2">Total</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($lines->sum('debit_amount'), 2) }}</td>
                        <td class="px-4 py-2 text-right font-mono text-gray-500">{{ number_format($lines->sum('credit_amount'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Details</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt>
                    <dd><span class="px-2 py-0.5 rounded-full text-xs
                        @if($entry->status==='POSTED') bg-green-50 text-green-700
                        @elseif($entry->status==='DRAFT') bg-yellow-50 text-yellow-700
                        @else bg-gray-100 text-gray-500 @endif">{{ $entry->status }}</span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Reference</dt><dd>{{ $entry->reference ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Description</dt><dd class="text-right max-w-48">{{ $entry->description }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Currency</dt><dd>{{ $entry->currency_code }}</dd></div>
            </dl>
        </div>

        @if($entry->status !== 'VOID')
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2">
            @if($entry->status === 'DRAFT')
            <form action="{{ route('tenant.accounting.journal.void', [$tenant->slug, $entry->id]) }}" method="POST" onsubmit="return confirm('Void this entry?')">
                @csrf @method('DELETE')
                <button class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Void Entry</button>
            </form>
            @elseif($entry->status === 'POSTED')
            <form action="{{ route('tenant.accounting.journal.void', [$tenant->slug, $entry->id]) }}" method="POST" onsubmit="return confirm('Void this posted entry?')">
                @csrf @method('DELETE')
                <button class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Void Entry</button>
            </form>
            @endif
        </div>
        @endif

        <a href="{{ route('tenant.accounting.journal.index', $tenant->slug) }}" class="block text-center py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
            ← Back to Journal
        </a>
    </div>
</div>
@endsection
