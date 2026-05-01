@extends('layouts.tenant')
@section('title', 'Dashboard — ' . $tenant->name)
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your compliance portfolio')

@section('content')

{{-- ── STAT CARDS ──────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total clients</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['active'] }} active</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-semibold text-amber-500 uppercase tracking-wide">Pending KYC</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['pending'] }}</p>
        <p class="text-xs text-gray-400 mt-1">Awaiting completion</p>
    </div>

    <div class="bg-white rounded-xl border {{ $stats['expiring_soon'] > 0 ? 'border-orange-200 bg-orange-50' : 'border-gray-200' }} p-4">
        <p class="text-xs font-semibold {{ $stats['expiring_soon'] > 0 ? 'text-orange-500' : 'text-gray-400' }} uppercase tracking-wide">Doc expiry</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['expiring_soon'] }}</p>
        <p class="text-xs {{ $stats['expiring_soon'] > 0 ? 'text-orange-500' : 'text-gray-400' }} mt-1">
            Expiring within 30 days
            @if($stats['expired'] > 0)
                · <span class="text-red-500 font-semibold">{{ $stats['expired'] }} expired</span>
            @endif
        </p>
    </div>

    <div class="bg-white rounded-xl border {{ $stats['review_due'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200' }} p-4">
        <p class="text-xs font-semibold {{ $stats['review_due'] > 0 ? 'text-red-500' : 'text-gray-400' }} uppercase tracking-wide">Reviews due</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['review_due'] }}</p>
        <p class="text-xs {{ $stats['review_due'] > 0 ? 'text-red-500' : 'text-gray-400' }} mt-1">KYC review overdue or due soon</p>
    </div>
</div>

{{-- ── SECOND ROW STATS ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['high_risk'] }}</p>
            <p class="text-xs text-gray-400">High-risk clients</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['unscreened'] }}</p>
            <p class="text-xs text-gray-400">Not yet screened</p>
        </div>
    </div>
</div>

{{-- ── ALERTS + RECENT ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Expiry alerts --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Document expiry alerts</h2>
            <span class="text-xs text-gray-400">Next 60 days</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($expiry_alerts as $client)
            <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
               class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $client->displayName() }}</p>
                    <p class="text-xs text-gray-400">Trade licence</p>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded-full
                    {{ $client->trade_license_expiry->isPast() ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                    {{ $client->trade_license_expiry->isPast()
                        ? 'Expired ' . $client->trade_license_expiry->diffForHumans()
                        : 'Expires ' . $client->trade_license_expiry->format('d M Y') }}
                </span>
            </a>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">No expiry alerts</div>
            @endforelse
        </div>
    </div>

    {{-- Review due alerts --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">KYC reviews due</h2>
            <span class="text-xs text-gray-400">Next 30 days</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($review_alerts as $client)
            <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
               class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $client->displayName() }}</p>
                    <span class="inline-block text-xs px-1.5 py-0.5 rounded {{ $client->riskBadgeColor() }} mt-0.5">
                        {{ ucfirst($client->risk_rating ?? 'unrated') }} risk
                    </span>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded-full
                    {{ $client->next_review_date->isPast() ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $client->next_review_date->isPast()
                        ? 'Overdue'
                        : $client->next_review_date->format('d M Y') }}
                </span>
            </a>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">No reviews due</div>
            @endforelse
        </div>
    </div>

    {{-- Recent clients --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Recently added</h2>
            <a href="{{ route('tenant.clients.index', $tenant->slug) }}"
               class="text-xs text-blue-600 hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recent as $client)
            <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
               class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700 flex-shrink-0">
                        {{ strtoupper(substr($client->displayName(), 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $client->displayName() }}</p>
                        <p class="text-xs text-gray-400">{{ $client->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $client->statusBadgeColor() }} flex-shrink-0">
                    {{ ucfirst($client->status) }}
                </span>
            </a>
            @empty
            <div class="px-4 py-8 text-center">
                <p class="text-gray-400 text-sm mb-3">No clients yet</p>
                <a href="{{ route('tenant.clients.create', $tenant->slug) }}"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:underline">
                    Add your first client
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
