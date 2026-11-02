@extends('layouts.tenant')
@section('title', 'Inventory — ' . $tenant->name)
@section('page-title', 'Inventory')
@section('page-subtitle', 'Gold, silver and precious metal items')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

{{-- Filters + New --}}
<div class="flex gap-3 mb-5 items-center">
    <form method="GET" class="flex gap-2">
        <select name="category" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
            <option value="">All Categories</option>
            @foreach(['gold_bar' => 'Gold Bar', 'gold_coin' => 'Gold Coin', 'jewelry' => 'Jewelry', 'silver_bar' => 'Silver Bar', 'silver_coin' => 'Silver Coin', 'other' => 'Other'] as $val => $lbl)
            <option value="{{ $val }}" @selected($category === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <button class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Filter</button>
    </form>
    <a href="{{ route('tenant.accounting.inventory.create', $tenant->slug) }}" class="ml-auto px-4 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">+ New Item</a>
</div>

{{-- Summary --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Items</p>
        <p class="text-xl font-bold text-gray-800">{{ $totalItems }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Inventory Value (AED)</p>
        <p class="text-xl font-bold text-gray-800">{{ number_format($totalValue, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-xs text-gray-400">Zero / Low Stock</p>
        <p class="text-xl font-bold {{ $lowStockItems > 0 ? 'text-amber-600' : 'text-gray-800' }}">{{ $lowStockItems }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Code</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-right">Purity</th>
                <th class="px-4 py-3 text-right">Wt/unit (g)</th>
                <th class="px-4 py-3 text-right">Stock</th>
                <th class="px-4 py-3 text-right">Price (AED)</th>
                <th class="px-4 py-3 text-center">VAT</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($items as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $item->code ?: '—' }}</td>
                <td class="px-4 py-2.5">
                    <a href="{{ route('tenant.accounting.inventory.show', [$tenant->slug, $item->id]) }}" class="text-blue-600 hover:underline font-medium">{{ $item->name }}</a>
                    @if($item->name_arabic) <span class="text-xs text-gray-400 ml-1">{{ $item->name_arabic }}</span> @endif
                </td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $item->categoryLabel() }}</td>
                <td class="px-4 py-2.5 text-right text-xs text-gray-500">{{ $item->gold_purity ? $item->gold_purity . '‰' : '—' }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ $item->weight_per_unit_grams ? number_format($item->weight_per_unit_grams, 2) : '—' }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs {{ $item->stock_qty <= 0 ? 'text-red-500 font-semibold' : '' }}">
                    {{ number_format($item->stock_qty, 2) }} {{ $item->unit }}
                </td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($item->current_price, 4) }}</td>
                <td class="px-4 py-2.5 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $item->vat_rate > 0 ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700' }}">
                        {{ $item->vatLabel() }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No items yet. <a href="{{ route('tenant.accounting.inventory.create', $tenant->slug) }}" class="text-blue-600 hover:underline">Add your first item</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
