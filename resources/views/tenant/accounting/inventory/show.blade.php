@extends('layouts.tenant')
@section('title', $item->name . ' — Inventory')
@section('page-title', $item->name)
@section('page-subtitle', $item->categoryLabel() . ($item->gold_purity ? ' · ' . $item->gold_purity . '‰' : ''))

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Movements --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Stock Movements</h3>
                <span class="text-xs text-gray-400">Last 50</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Type</th>
                        <th class="px-4 py-2 text-right">Qty</th>
                        <th class="px-4 py-2 text-right">Unit Price</th>
                        <th class="px-4 py-2 text-left">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $mv)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $mv->movement_date }}</td>
                        <td class="px-4 py-2 text-xs">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $mv->quantity > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                {{ str_replace('_', ' ', ucfirst($mv->movement_type)) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-xs {{ $mv->quantity > 0 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $mv->quantity > 0 ? '+' : '' }}{{ number_format($mv->quantity, 4) }}
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-500">{{ $mv->unit_price ? number_format($mv->unit_price, 4) : '—' }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $mv->reference ?: '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No movements recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Item Details</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Code</dt><dd class="font-mono text-xs">{{ $item->code ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd>{{ $item->categoryLabel() }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Unit</dt><dd>{{ $item->unit }}</dd></div>
                @if($item->gold_purity)
                <div class="flex justify-between"><dt class="text-gray-500">Gold Purity</dt><dd class="font-mono text-xs">{{ $item->gold_purity }}‰</dd></div>
                @endif
                @if($item->weight_per_unit_grams)
                <div class="flex justify-between"><dt class="text-gray-500">Weight/unit</dt><dd class="font-mono text-xs">{{ $item->weight_per_unit_grams }}g</dd></div>
                @endif
                <div class="flex justify-between border-t pt-2 mt-2"><dt class="text-gray-500">Current Stock</dt>
                    <dd class="font-semibold {{ $item->stock_qty <= 0 ? 'text-red-600' : 'text-gray-800' }}">{{ number_format($item->stock_qty, 4) }} {{ $item->unit }}</dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Sell Price</dt><dd class="font-mono text-xs">AED {{ number_format($item->current_price, 4) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">VAT</dt><dd>{{ $item->vatLabel() }}</dd></div>
            </dl>
        </div>

        {{-- Adjust stock --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Adjust Stock</h4>
            <form action="{{ route('tenant.accounting.inventory.adjust', [$tenant->slug, $item->id]) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="movement_type" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5">
                        <option value="purchase_in">Purchase In</option>
                        <option value="adjustment_in">Adjustment In</option>
                        <option value="adjustment_out">Adjustment Out</option>
                        <option value="opening">Opening Balance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                    <input type="date" name="movement_date" value="{{ date('Y-m-d') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label>
                    <input type="number" name="quantity" step="0.0001" min="0.0001" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 font-mono" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Unit Price (AED)</label>
                    <input type="number" name="unit_price" step="0.0001" min="0" value="{{ $item->current_price }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 font-mono">
                </div>
                <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                    <input type="checkbox" name="update_price" value="1"> Update current price from this movement
                </label>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reference</label>
                    <input type="text" name="reference" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5" placeholder="PO / receipt #">
                </div>
                <button type="submit" class="w-full py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Record Movement</button>
            </form>
        </div>

        <a href="{{ route('tenant.accounting.inventory.index', $tenant->slug) }}" class="block text-center py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">← Back to Inventory</a>
    </div>
</div>
@endsection
