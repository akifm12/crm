@extends('layouts.bbook')
@section('title', 'Clients — ' . $tenant->name)
@section('page-title', 'Clients')
@section('page-subtitle', 'A-Book clients plus OTC-only counterparties — the full A+B picture')

@section('content')

<div class="card overflow-hidden mb-5">
    <div class="px-5 py-3 border-b border-white/5">
        <h2 class="text-sm font-semibold text-gray-300">A-Book Clients</h2>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs text-gray-500 border-b border-white/5">
                <th class="px-5 py-2.5 font-medium">Name</th>
                <th class="px-5 py-2.5 font-medium">Type</th>
                <th class="px-5 py-2.5 font-medium text-right">OTC Deals</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($clients as $client)
            <tr class="hover:bg-white/[0.02] cursor-pointer" onclick="location.href='{{ route('bbook.clients.show', [$tenant->slug, $client->id]) }}'">
                <td class="px-5 py-3 text-gray-200">{{ $client->displayName() }}</td>
                <td class="px-5 py-3 text-gray-500 capitalize">{{ str_replace('_', ' ', $client->client_type) }}</td>
                <td class="px-5 py-3 text-right">
                    @if($clientDealCounts->get($client->id))
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-950/50 text-amber-400">{{ $clientDealCounts->get($client->id) }} deal(s)</span>
                    @else
                    <span class="text-gray-700">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-5 py-8 text-center text-gray-600 text-sm">No A-Book clients yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($unlinkedCounterparties->isNotEmpty())
<div class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-white/5">
        <h2 class="text-sm font-semibold text-gray-300">OTC-only Counterparties</h2>
        <p class="text-xs text-gray-600 mt-0.5">Never onboarded to A-Book — no KYC required for OTC dealings</p>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs text-gray-500 border-b border-white/5">
                <th class="px-5 py-2.5 font-medium">Name</th>
                <th class="px-5 py-2.5 font-medium text-right">Deals</th>
                <th class="px-5 py-2.5 font-medium text-right">Total Volume (AED)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @foreach($unlinkedCounterparties as $cp)
            <tr class="hover:bg-white/[0.02] cursor-pointer" onclick="location.href='{{ route('bbook.clients.counterparty', [$tenant->slug, urlencode($cp->counterparty_name)]) }}'">
                <td class="px-5 py-3 text-gray-200">{{ $cp->counterparty_name }}</td>
                <td class="px-5 py-3 text-right text-gray-400">{{ $cp->deal_count }}</td>
                <td class="px-5 py-3 text-right font-mono text-gray-300">{{ number_format($cp->volume, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
