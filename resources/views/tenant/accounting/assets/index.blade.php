@extends('layouts.tenant')
@section('title', 'Fixed Assets — ' . $tenant->name)
@section('page-title', 'Fixed Assets')
@section('page-subtitle', 'Asset register and depreciation')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="flex gap-3">
        <a href="{{ route('tenant.accounting.assets.create', $tenant->slug) }}" class="px-4 py-2 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">+ Add Asset</a>
    </div>
    {{-- Run depreciation --}}
    <form method="POST" action="{{ route('tenant.accounting.assets.depreciation.run', $tenant->slug) }}" class="flex gap-2 items-center">
        @csrf
        <label class="text-xs text-gray-500">Run depreciation for:</label>
        <input type="date" name="period_end" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
        <button class="px-4 py-1.5 text-sm font-medium border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50">Run</button>
    </form>
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
@endif

{{-- Summary --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Total Cost</p>
        <p class="text-lg font-semibold font-mono">{{ number_format($totalCost, 2) }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Accumulated Depreciation</p>
        <p class="text-lg font-semibold font-mono text-red-600">{{ number_format($totalAccum, 2) }}</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Net Book Value</p>
        <p class="text-lg font-semibold font-mono text-blue-700">{{ number_format($totalNbv, 2) }}</p>
    </div>
</div>

{{-- Category quick-add --}}
<details class="mb-4 bg-white border border-gray-200 rounded-xl">
    <summary class="px-4 py-3 text-sm font-medium cursor-pointer">Manage Categories</summary>
    <div class="px-4 pb-4">
        <form method="POST" action="{{ route('tenant.accounting.assets.categories.store', $tenant->slug) }}" class="flex gap-3 mt-3">
            @csrf
            <input type="text" name="name" placeholder="Category name (e.g. Vehicles)" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 flex-1" required>
            <input type="number" step="0.5" name="useful_life_years" placeholder="Life (years)" value="5" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 w-32">
            <button class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Add</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($categories as $cat)
            <span class="px-3 py-1 bg-gray-100 rounded-full text-xs">{{ $cat->name }} ({{ $cat->useful_life_years }}yr)</span>
            @endforeach
        </div>
    </div>
</details>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Asset #</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-left">In Service</th>
                <th class="px-4 py-3 text-right">Cost</th>
                <th class="px-4 py-3 text-right">Accum. Depr.</th>
                <th class="px-4 py-3 text-right">NBV</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($assets as $asset)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $asset->asset_number }}</td>
                <td class="px-4 py-2.5 font-medium">{{ $asset->name }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $asset->category?->name ?? '—' }}</td>
                <td class="px-4 py-2.5 text-xs">{{ $asset->in_service_date }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($asset->purchase_cost, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs text-red-600">{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold text-blue-700">{{ number_format($asset->net_book_value, 2) }}</td>
                <td class="px-4 py-2.5">
                    @php $sc = ['active'=>'bg-green-100 text-green-800','disposed'=>'bg-gray-100 text-gray-500','fully_depreciated'=>'bg-yellow-100 text-yellow-800']; @endphp
                    <span class="px-2 py-0.5 rounded text-xs {{ $sc[$asset->status] ?? 'bg-gray-100 text-gray-600' }}">{{ str_replace('_',' ',ucfirst($asset->status)) }}</span>
                </td>
                <td class="px-4 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.assets.show', [$tenant->slug, $asset->id]) }}" class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">No assets yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
