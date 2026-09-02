@extends('layouts.bbook')
@section('title', 'B-Book Dashboard — ' . $tenant->name)
@section('page-title', 'B-Book Dashboard')
@section('page-subtitle', 'The consolidated position — A-Book activity plus OTC dealings')

@section('content')

<div class="grid grid-cols-4 gap-4 mb-8">
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">OTC Deals</p>
        <p class="text-2xl font-semibold text-white">{{ $stats['deals_total'] }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">Drafts</p>
        <p class="text-2xl font-semibold text-white">{{ $stats['deals_draft'] }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">OTC Volume (AED)</p>
        <p class="text-2xl font-semibold gold-text">{{ number_format($stats['otc_volume_aed'], 2) }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">OTC Net Income</p>
        <p class="text-2xl font-semibold {{ $stats['otc_net_income'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
            {{ number_format($stats['otc_net_income'], 2) }}
        </p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-300">Recent OTC Deals</h2>
        <a href="{{ route('bbook.deals.index', $tenant->slug) }}" class="text-xs text-amber-500 hover:text-amber-400">View all →</a>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs text-gray-500 border-b border-white/5">
                <th class="px-5 py-2.5 font-medium">Deal #</th>
                <th class="px-5 py-2.5 font-medium">Date</th>
                <th class="px-5 py-2.5 font-medium">Type</th>
                <th class="px-5 py-2.5 font-medium">Counterparty</th>
                <th class="px-5 py-2.5 font-medium">Status</th>
                <th class="px-5 py-2.5 font-medium text-right">Total (AED)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($recentDeals as $deal)
            <tr class="hover:bg-white/[0.02]">
                <td class="px-5 py-3 text-gray-300">{{ $deal->deal_number }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $deal->deal_date->format('d M Y') }}</td>
                <td class="px-5 py-3 text-gray-400 capitalize">{{ $deal->deal_type }}</td>
                <td class="px-5 py-3 text-gray-300">{{ $deal->counterparty_name }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $deal->status === 'posted' ? 'bg-emerald-950/60 text-emerald-400' : ($deal->status === 'void' ? 'bg-red-950/60 text-red-400' : 'bg-gray-800 text-gray-400') }}">
                        {{ ucfirst($deal->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right font-mono text-gray-300">{{ number_format($deal->total, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-8 text-center text-gray-600 text-sm">No OTC deals yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
