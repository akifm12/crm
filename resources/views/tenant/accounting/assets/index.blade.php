@extends('layouts.tenant')
@section('title', 'Fixed Assets — ' . $tenant->name)
@section('page-title', 'Fixed Assets')
@section('page-subtitle', 'Asset register and depreciation')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div></div>
    <a href="{{ route('tenant.accounting.assets.create', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + New asset
    </a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Asset #</th>
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">Category</th>
                <th class="px-5 py-3">Purchase Date</th>
                <th class="px-5 py-3 text-right">Cost</th>
                <th class="px-5 py-3 text-right">Net Book Value</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($assets as $asset)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5 font-mono text-xs text-blue-600">{{ $asset->asset_number }}</td>
                <td class="px-5 py-2.5 font-medium text-gray-800">{{ $asset->name }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ $asset->category?->name ?? '—' }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ $asset->purchase_date->format('d M Y') }}</td>
                <td class="px-5 py-2.5 text-right text-gray-800">{{ number_format($asset->purchase_cost, 2) }}</td>
                <td class="px-5 py-2.5 text-right font-semibold text-gray-900">{{ number_format($asset->net_book_value, 2) }}</td>
                <td class="px-5 py-2.5">
                    @php $sc = match($asset->status) {
                        'active' => 'bg-green-50 text-green-700',
                        'disposed' => 'bg-gray-100 text-gray-500',
                        default => 'bg-amber-50 text-amber-700' }; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $sc }}">{{ ucfirst(str_replace('_',' ',$asset->status)) }}</span>
                </td>
                <td class="px-5 py-2.5">
                    <a href="{{ route('tenant.accounting.assets.show', [$tenant->slug, $asset->id]) }}"
                       class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-5 py-8 text-center text-gray-400">No assets yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
