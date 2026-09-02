@extends('layouts.bbook')
@section('title', 'OTC Deals — ' . $tenant->name)
@section('page-title', 'OTC Deals')
@section('page-subtitle', 'Generic dealings — no VAT, no formal invoice')

@section('content')

<div class="flex justify-end mb-4">
    <a href="{{ route('bbook.deals.create', $tenant->slug) }}"
       class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-600 rounded-lg hover:from-amber-300 hover:to-yellow-500 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New OTC deal
    </a>
</div>

<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-xs text-gray-500 border-b border-white/5">
                <th class="px-5 py-2.5 font-medium">Deal #</th>
                <th class="px-5 py-2.5 font-medium">Date</th>
                <th class="px-5 py-2.5 font-medium">Type</th>
                <th class="px-5 py-2.5 font-medium">Counterparty</th>
                <th class="px-5 py-2.5 font-medium">Status</th>
                <th class="px-5 py-2.5 font-medium text-right">Total (AED)</th>
                <th class="px-5 py-2.5 font-medium text-right">Outstanding</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($deals as $deal)
            <tr class="hover:bg-white/[0.02] cursor-pointer" onclick="location.href='{{ route('bbook.deals.show', [$tenant->slug, $deal->id]) }}'">
                <td class="px-5 py-3 text-amber-500">{{ $deal->deal_number }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $deal->deal_date->format('d M Y') }}</td>
                <td class="px-5 py-3 text-gray-400 capitalize">{{ $deal->deal_type }}</td>
                <td class="px-5 py-3 text-gray-300">{{ $deal->counterparty_name }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $deal->status === 'posted' ? 'bg-emerald-950/60 text-emerald-400' : ($deal->status === 'void' ? 'bg-red-950/60 text-red-400' : 'bg-gray-800 text-gray-400') }}">
                        {{ ucfirst($deal->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right font-mono text-gray-300">{{ number_format($deal->total, 2) }}</td>
                <td class="px-5 py-3 text-right font-mono text-gray-500">{{ number_format($deal->outstandingBalance(), 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-600 text-sm">No OTC deals yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-white/5">{{ $deals->links() }}</div>
</div>

@endsection
