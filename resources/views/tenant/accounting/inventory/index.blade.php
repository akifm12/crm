@extends('layouts.tenant')
@section('title', 'Inventory — ' . $tenant->name)
@section('page-title', 'Inventory')
@section('page-subtitle', 'Stock on hand, valued at weighted-average cost')

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif

<div class="flex justify-end mb-4">
    <a href="{{ route('tenant.accounting.inventory.create', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + New item
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Item</th>
                <th class="px-5 py-3">Metal</th>
                <th class="px-5 py-3">Size</th>
                <th class="px-5 py-3">Purity</th>
                <th class="px-5 py-3 text-right">Pieces</th>
                <th class="px-5 py-3 text-right">Qty (g)</th>
                <th class="px-5 py-3 text-right">Avg cost/g (AED)</th>
                <th class="px-5 py-3 text-right">Value (AED)</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($items as $item)
            <tr>
                <td class="px-5 py-2.5 text-gray-800 font-medium">{{ $item->name }}</td>
                <td class="px-5 py-2.5">
                    <span @class([
                        'px-2 py-0.5 rounded-full text-xs font-medium capitalize',
                        'bg-amber-50 text-amber-700' => $item->metal_type === 'gold',
                        'bg-gray-100 text-gray-600' => $item->metal_type === 'silver',
                        'bg-sky-50 text-sky-700' => $item->metal_type === 'platinum',
                        'bg-purple-50 text-purple-700' => $item->metal_type === 'palladium',
                    ])>{{ $item->metal_type }}</span>
                </td>
                <td class="px-5 py-2.5 text-gray-500">{{ \App\Support\BarWeightPresets::labelFor($item->nominal_weight_grams ? (float) $item->nominal_weight_grams : null) ?: '—' }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ $item->purity ? number_format($item->purity, 3) : '—' }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ $item->nominal_weight_grams ? number_format($item->balance->pieces ?? 0) : '—' }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($item->balance->quantity_grams ?? 0, 3) }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($item->balance->weighted_avg_cost ?? 0, 4) }}</td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($item->balance->total_value ?? 0, 2) }}</td>
                <td class="px-5 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.inventory.show', [$tenant->slug, $item->id]) }}" class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-5 py-6 text-center text-sm text-gray-400">No inventory items yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
