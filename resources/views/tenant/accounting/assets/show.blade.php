@extends('layouts.tenant')
@section('title', $asset->name . ' — ' . $tenant->name)
@section('page-title', $asset->name)
@section('page-subtitle', $asset->asset_number . ' · ' . ucfirst(str_replace('_',' ',$asset->status)))

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Purchase Cost</p>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($asset->purchase_cost, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Accumulated Dep.</p>
        <p class="text-xl font-bold text-red-600 mt-1">{{ number_format($asset->accumulated_depreciation, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Net Book Value</p>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($asset->net_book_value, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Monthly Dep.</p>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($asset->monthlyDepreciation(), 2) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Asset Details</h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd>{{ $asset->category?->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Purchase Date</dt><dd>{{ $asset->purchase_date->format('d M Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Useful Life</dt><dd>{{ $asset->useful_life_months }} months</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Method</dt><dd>{{ ucfirst(str_replace('_',' ',$asset->depreciation_method)) }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Salvage Value</dt><dd>{{ number_format($asset->salvage_value, 2) }}</dd></div>
            @if($asset->last_depreciation_date)
            <div class="flex justify-between"><dt class="text-gray-500">Last Dep. Run</dt><dd>{{ $asset->last_depreciation_date->format('d M Y') }}</dd></div>
            @endif
        </dl>
    </div>

    @if($asset->status === 'active' && $asset->depreciation_method !== 'none')
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Run Depreciation</h3>
        <form method="POST" action="{{ route('tenant.accounting.assets.depreciate', [$tenant->slug, $asset->id]) }}">
            @csrf
            <div class="mb-3">
                <label class="block text-xs text-gray-500 mb-1">Periods to depreciate</label>
                <input type="number" name="periods" value="1" min="1" max="120"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-28 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                Run depreciation
            </button>
        </form>

        <div class="mt-4 pt-4 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Dispose Asset</h3>
            <form method="POST" action="{{ route('tenant.accounting.assets.dispose', [$tenant->slug, $asset->id]) }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Disposal Proceeds (AED)</label>
                    <input type="number" name="proceeds" value="0" min="0" step="0.01"
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-36 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700"
                        onclick="return confirm('Dispose this asset?')">
                    Dispose
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

@if($asset->notes)
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Notes</h3>
    <p class="text-sm text-gray-600">{{ $asset->notes }}</p>
</div>
@endif
@endsection
