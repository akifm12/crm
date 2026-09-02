@extends('layouts.bbook')
@section('title', $client->displayName() . ' — ' . $tenant->name)
@section('page-title', $client->displayName())
@section('page-subtitle', 'Consolidated position — A-Book statement + OTC dealings')

@section('content')

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">A-Book balance</p>
        @if($aBookStatement)
        <p class="text-xl font-semibold {{ $aBookStatement['balance'] > 0 ? 'text-red-400' : ($aBookStatement['balance'] < 0 ? 'text-emerald-400' : 'text-gray-300') }}">
            {{ number_format(abs($aBookStatement['balance']), 2) }} <span class="text-xs text-gray-600">AED</span>
        </p>
        <p class="text-xs text-gray-600 mt-0.5">{{ $aBookStatement['balance'] > 0 ? 'Client owes' : ($aBookStatement['balance'] < 0 ? 'We owe client' : 'Settled') }}</p>
        @else
        <p class="text-sm text-gray-600">Not applicable</p>
        @endif
    </div>
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">OTC outstanding</p>
        <p class="text-xl font-semibold gold-text">{{ number_format($otcOutstanding, 2) }} <span class="text-xs text-gray-600">AED</span></p>
        <p class="text-xs text-gray-600 mt-0.5">{{ $otcDeals->where('status', 'posted')->count() }} posted deal(s)</p>
    </div>
    <div class="card p-4">
        <p class="text-xs text-gray-500 mb-1">Consolidated (A+B)</p>
        @php $combined = ($aBookStatement['balance'] ?? 0) + $otcOutstanding; @endphp
        <p class="text-xl font-semibold {{ $combined > 0 ? 'text-red-400' : ($combined < 0 ? 'text-emerald-400' : 'text-gray-300') }}">
            {{ number_format(abs($combined), 2) }} <span class="text-xs text-gray-600">AED</span>
        </p>
        <p class="text-xs text-gray-600 mt-0.5">Net position across both books</p>
    </div>
</div>

@if($aBookStatement)
<div class="card overflow-hidden mb-5">
    <div class="px-5 py-3 border-b border-white/5">
        <h2 class="text-sm font-semibold text-gray-300">A-Book Statement</h2>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs text-gray-500 border-b border-white/5">
                <th class="px-5 py-2.5 font-medium">Date</th>
                <th class="px-5 py-2.5 font-medium">Description</th>
                <th class="px-5 py-2.5 font-medium text-right">Amount</th>
                <th class="px-5 py-2.5 font-medium text-right">Balance</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($aBookStatement['rows'] as $row)
            <tr class="hover:bg-white/[0.02]">
                <td class="px-5 py-2.5 text-gray-500">{{ $row['date']->format('d M Y') }}</td>
                <td class="px-5 py-2.5 text-gray-300">{{ $row['description'] }}</td>
                <td class="px-5 py-2.5 text-right font-mono text-gray-400">{{ number_format($row['amount'], 2) }}</td>
                <td class="px-5 py-2.5 text-right font-mono text-gray-300">{{ number_format($row['balance'], 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-5 py-6 text-center text-gray-600 text-sm">No A-Book transactions.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

<div class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-white/5 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-300">OTC Deals</h2>
        <a href="{{ route('bbook.deals.create', $tenant->slug) }}" class="text-xs text-amber-500 hover:text-amber-400">+ New deal →</a>
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
            @forelse($otcDeals as $deal)
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
            @empty
            <tr><td colspan="5" class="px-5 py-6 text-center text-gray-600 text-sm">No OTC deals with this client yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
