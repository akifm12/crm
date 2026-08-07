@extends('layouts.tenant')
@section('title', 'Journal — ' . $tenant->name)
@section('page-title', 'Journal')
@section('page-subtitle', 'Posted entries — corrections are made via a reversing entry, never edited')

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Reference</th>
                <th class="px-5 py-3">Client</th>
                <th class="px-5 py-3">Source</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Total (AED)</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($entries as $entry)
            <tr>
                <td class="px-5 py-2.5 text-gray-700">{{ $entry->entry_date->format('d M Y') }}</td>
                <td class="px-5 py-2.5 text-gray-700">{{ $entry->reference ?: '—' }}</td>
                <td class="px-5 py-2.5 text-gray-700">{{ $entry->client?->displayName() ?? '—' }}</td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ $entry->source_type }}</td>
                <td class="px-5 py-2.5">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $entry->status === 'posted' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst($entry->status) }}
                    </span>
                </td>
                <td class="px-5 py-2.5 text-right font-mono text-gray-700">{{ number_format($entry->lines->sum('base_debit'), 2) }}</td>
                <td class="px-5 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.journal.show', [$tenant->slug, $entry->id]) }}" class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-6 text-center text-sm text-gray-400">No journal entries yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($entries->hasPages())
<div class="mb-6">{{ $entries->links() }}</div>
@endif

{{-- Manual journal entry --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 max-w-3xl"
     x-data="journalForm()">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Post manual journal entry</h3>

    <form method="POST" action="{{ route('tenant.accounting.journal.store', $tenant->slug) }}" @submit="return balanced">
        @csrf
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Entry date</label>
                <input type="date" name="entry_date" required value="{{ old('entry_date', now()->toDateString()) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Reference</label>
                <input type="text" name="reference" value="{{ old('reference') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
        </div>

        <table class="w-full text-sm mb-3">
            <thead>
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                    <th class="pb-2">Account</th>
                    <th class="pb-2">Description</th>
                    <th class="pb-2 w-28">Debit</th>
                    <th class="pb-2 w-28">Credit</th>
                    <th class="pb-2 w-8"></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(line, i) in lines" :key="i">
                    <tr>
                        <td class="pr-2 py-1">
                            <select :name="'lines['+i+'][chart_of_account_id]'" x-model="line.account_id" required
                                    class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                                <option value="">Select...</option>
                                @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} — {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="pr-2 py-1">
                            <input type="text" :name="'lines['+i+'][description]'" x-model="line.description"
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                        </td>
                        <td class="pr-2 py-1">
                            <input type="number" step="0.01" min="0" :name="'lines['+i+'][debit]'" x-model.number="line.debit"
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                        </td>
                        <td class="pr-2 py-1">
                            <input type="number" step="0.01" min="0" :name="'lines['+i+'][credit]'" x-model.number="line.credit"
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                        </td>
                        <td class="py-1">
                            <button type="button" @click="removeLine(i)" x-show="lines.length > 2" class="text-red-400 hover:text-red-600 text-xs">✕</button>
                        </td>
                    </tr>
                </template>
            </tbody>
            <tfoot>
                <tr class="text-xs font-semibold text-gray-600">
                    <td colspan="2" class="pt-2 text-right pr-2">Totals</td>
                    <td class="pt-2 text-right pr-2" x-text="totalDebit.toFixed(2)"></td>
                    <td class="pt-2 text-right pr-2" x-text="totalCredit.toFixed(2)"></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <button type="button" @click="addLine()" class="text-xs text-blue-600 hover:underline mb-4">+ Add line</button>

        <div class="flex items-center justify-between">
            <p class="text-xs" :class="balanced ? 'text-green-600' : 'text-red-500'" x-text="balanced ? 'Balanced' : 'Debits and credits must be equal'"></p>
            <button type="submit" :disabled="!balanced"
                    class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed">
                Post entry
            </button>
        </div>
    </form>
</div>

<script>
function journalForm() {
    return {
        lines: [
            { account_id: '', description: '', debit: null, credit: null },
            { account_id: '', description: '', debit: null, credit: null },
        ],
        addLine() { this.lines.push({ account_id: '', description: '', debit: null, credit: null }); },
        removeLine(i) { this.lines.splice(i, 1); },
        get totalDebit() { return this.lines.reduce((sum, l) => sum + (parseFloat(l.debit) || 0), 0); },
        get totalCredit() { return this.lines.reduce((sum, l) => sum + (parseFloat(l.credit) || 0), 0); },
        get balanced() { return this.totalDebit > 0 && Math.abs(this.totalDebit - this.totalCredit) < 0.01; },
    };
}
</script>

@endsection
