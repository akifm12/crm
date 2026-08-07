@extends('layouts.tenant')
@section('title', $item->name . ' — ' . $tenant->name)
@section('page-title', $item->name)
@section('page-subtitle', ucfirst($item->metal_type) . ' · Purity ' . ($item->purity ?? '—') . ' · ' . ucfirst($item->form ?? '—'))

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-2 {{ $item->nominal_weight_grams ? 'sm:grid-cols-4' : 'sm:grid-cols-3' }} gap-4 mb-5">
    @if($item->nominal_weight_grams)
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Pieces on hand</p>
        <p class="text-lg font-semibold text-gray-800">{{ number_format($item->balance->pieces ?? 0) }} pcs</p>
    </div>
    @endif
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Quantity on hand</p>
        <p class="text-lg font-semibold text-gray-800">{{ number_format($item->balance->quantity_grams ?? 0, 3) }} g</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Weighted-avg cost</p>
        <p class="text-lg font-semibold text-gray-800">{{ number_format($item->balance->weighted_avg_cost ?? 0, 4) }} AED/g</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Total value</p>
        <p class="text-lg font-semibold text-gray-800">{{ number_format($item->balance->total_value ?? 0, 2) }} AED</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5 max-w-xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Manual stock adjustment</h3>
    <form method="POST" action="{{ route('tenant.accounting.inventory.adjust', [$tenant->slug, $item->id]) }}"
          x-data="{ direction: 'in', pieces: '', qty: '', nominalWeight: {{ $item->nominal_weight_grams ?? 'null' }} }" class="space-y-3">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Reason type</label>
            <select name="reason_type" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="correction">Stock-count correction (affects income statement)</option>
                <option value="capital_contribution">Owner/shareholder capital contribution or draw (affects equity only)</option>
            </select>
        </div>
        <div class="grid grid-cols-2 {{ $item->nominal_weight_grams ? 'sm:grid-cols-4' : 'sm:grid-cols-3' }} gap-3">
            <select name="direction" x-model="direction" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="in">Stock in</option>
                <option value="out">Stock out</option>
            </select>
            @if($item->nominal_weight_grams)
            <input type="number" step="1" min="1" x-model.number="pieces"
                   @input="qty = pieces ? (pieces * nominalWeight).toFixed(3) : qty" placeholder="Pieces"
                   class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
            <input type="hidden" name="pieces" :value="pieces">
            @endif
            <input type="number" step="0.001" min="0.001" name="quantity_grams" x-model="qty" placeholder="Quantity (g)" required
                   class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
            <input type="number" step="0.0001" min="0" name="unit_cost" placeholder="Cost/g (AED)" x-show="direction === 'in'"
                   class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        @if($item->nominal_weight_grams)
        <p class="text-xs text-gray-400 -mt-1">Enter pieces to auto-fill weight (nominal {{ rtrim(rtrim(number_format($item->nominal_weight_grams, 4), '0'), '.') }}g each), or edit weight directly for real-world tolerance.</p>
        @endif
        <input type="text" name="reason" placeholder="Reason (required)" required
               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Record adjustment
        </button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3 text-right">Qty (g)</th>
                <th class="px-5 py-3 text-right">Unit cost</th>
                <th class="px-5 py-3 text-right">Total cost</th>
                <th class="px-5 py-3">Notes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($movements as $m)
            <tr>
                <td class="px-5 py-2.5 text-gray-700">{{ $m->moved_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ str_replace('_', ' ', $m->movement_type) }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($m->quantity_grams, 3) }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($m->unit_cost, 4) }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($m->total_cost, 2) }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ $m->notes }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-6 text-center text-sm text-gray-400">No movements yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($movements->hasPages())
<div class="mt-4">{{ $movements->links() }}</div>
@endif

@endsection
