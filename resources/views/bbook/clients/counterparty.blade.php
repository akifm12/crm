@extends('layouts.bbook')
@section('title', $name . ' — ' . $tenant->name)
@section('page-title', $name)
@section('page-subtitle', 'OTC-only counterparty — never onboarded to A-Book')

@section('content')

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">OTC outstanding</p>
        <p class="text-xl font-semibold gold-text">{{ number_format($otcOutstanding, 2) }} <span class="text-xs text-gray-600">AED</span></p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">Posted deals</p>
        <p class="text-xl font-semibold text-gray-200">{{ $otcDeals->where('status', 'posted')->count() }}</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-white/5">
        <h2 class="text-sm font-semibold text-gray-300">OTC Deals</h2>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs text-gray-500 border-b border-white/5">
                <th class="px-5 py-2.5 font-medium">Deal #</th>
                <th class="px-5 py-2.5 font-medium">Date</th>
                <th class="px-5 py-2.5 font-medium">Type</th>
                <th class="px-5 py-2.5 font-medium">Status</th>
                <th class="px-5 py-2.5 font-medium text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @foreach($otcDeals as $deal)
            <tr class="hover:bg-white/[0.02] cursor-pointer" onclick="location.href='{{ route('bbook.deals.show', [$tenant->slug, $deal->id]) }}'">
                <td class="px-5 py-3 text-amber-500">{{ $deal->deal_number }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $deal->deal_date->format('d M Y') }}</td>
                <td class="px-5 py-3 text-gray-400 capitalize">{{ $deal->deal_type }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $deal->status === 'posted' ? 'bg-emerald-950/60 text-emerald-400' : ($deal->status === 'void' ? 'bg-red-950/60 text-red-400' : 'bg-gray-800 text-gray-400') }}">
                        {{ ucfirst($deal->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right font-mono text-gray-300">{{ number_format($deal->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
