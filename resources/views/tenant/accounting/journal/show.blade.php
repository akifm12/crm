@extends('layouts.tenant')
@section('title', 'Journal Entry #' . $entry->id . ' — ' . $tenant->name)
@section('page-title', 'Journal Entry #' . $entry->id)
@section('page-subtitle', $entry->reference ?: ucfirst($entry->source_type))

@section('content')

<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5 max-w-3xl">
    <div class="grid grid-cols-5 gap-4 text-sm mb-4">
        <div><p class="text-xs text-gray-400 mb-0.5">Date</p><p class="text-gray-800">{{ $entry->entry_date->format('d M Y') }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Client</p><p class="text-gray-800">{{ $entry->client?->displayName() ?? '—' }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Status</p>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $entry->status === 'posted' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ ucfirst($entry->status) }}
            </span>
        </div>
        <div><p class="text-xs text-gray-400 mb-0.5">Currency</p><p class="text-gray-800">{{ $entry->currency_code }} @ {{ $entry->exchange_rate }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Posted by</p><p class="text-gray-800">{{ $entry->creator->name ?? '—' }}</p></div>
    </div>

    <table class="w-full text-sm mb-2">
        <thead>
            <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                <th class="py-2">Account</th>
                <th class="py-2">Description</th>
                <th class="py-2 text-right">Debit</th>
                <th class="py-2 text-right">Credit</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($entry->lines as $line)
            <tr>
                <td class="py-2">{{ $line->account->code }} — {{ $line->account->name }}</td>
                <td class="py-2 text-gray-500">{{ $line->description ?: '—' }}</td>
                <td class="py-2 text-right font-mono">{{ $line->base_debit > 0 ? number_format($line->base_debit, 2) : '' }}</td>
                <td class="py-2 text-right font-mono">{{ $line->base_credit > 0 ? number_format($line->base_credit, 2) : '' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="text-xs font-semibold text-gray-600 border-t border-gray-100">
                <td colspan="2" class="pt-2 text-right pr-2">Totals</td>
                <td class="pt-2 text-right font-mono">{{ number_format($entry->lines->sum('base_debit'), 2) }}</td>
                <td class="pt-2 text-right font-mono">{{ number_format($entry->lines->sum('base_credit'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($entry->memo)
    <p class="text-xs text-gray-500 mt-3">{{ $entry->memo }}</p>
    @endif
</div>

@if($entry->status === 'posted')
<form method="POST" action="{{ route('tenant.accounting.journal.void', [$tenant->slug, $entry->id]) }}"
      onsubmit="return confirm('Void this entry? A reversing entry will be posted; the original is kept for audit.')" class="max-w-3xl">
    @csrf @method('DELETE')
    <input type="text" name="reason" placeholder="Reason (optional)" class="px-3 py-2 text-sm border border-gray-200 rounded-lg mr-2">
    <button type="submit" class="px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50">
        Void entry
    </button>
</form>
@endif

@endsection
