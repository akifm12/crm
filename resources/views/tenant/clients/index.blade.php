@extends('layouts.tenant')
@section('title', 'Clients — ' . $tenant->name)
@section('page-title', 'Clients')
@section('page-subtitle', 'All onboarded clients for ' . $tenant->name)

@section('content')

{{-- ── TOOLBAR ──────────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row gap-3 mb-5">

    <form method="GET" class="flex gap-2 flex-1">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name, licence, email..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
        </div>
        <select name="status"
                class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All statuses</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="suspended"{{ request('status') === 'suspended'? 'selected' : '' }}>Suspended</option>
        </select>
        <select name="risk"
                class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All risk levels</option>
            <option value="low"    {{ request('risk') === 'low'    ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ request('risk') === 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high"   {{ request('risk') === 'high'   ? 'selected' : '' }}>High</option>
        </select>
        <button type="submit"
                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
            Filter
        </button>
    </form>

    <a href="{{ route('tenant.clients.create', $tenant->slug) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add new client
    </a>
</div>

{{-- ── CLIENT TABLE ─────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead>
            <tr class="bg-gray-50">
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Licence no.</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Licence expiry</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Risk</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Screening</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden xl:table-cell">Next review</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($clients as $client)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700 flex-shrink-0">
                            {{ strtoupper(substr($client->displayName(), 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $client->displayName() }}</p>
                            <p class="text-xs text-gray-400">{{ ucfirst($client->client_type) }}
                                @if($client->email) · {{ $client->email }} @endif
                            </p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-500 hidden md:table-cell">
                    {{ $client->trade_license_no ?? '—' }}
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @if($client->trade_license_expiry)
                        <span class="text-xs font-medium
                            {{ $client->isLicenseExpired() ? 'text-red-600' : ($client->isLicenseExpiringSoon() ? 'text-orange-600' : 'text-gray-500') }}">
                            {{ $client->trade_license_expiry->format('d M Y') }}
                            @if($client->isLicenseExpired())
                                <span class="block text-red-500">Expired</span>
                            @elseif($client->isLicenseExpiringSoon())
                                <span class="block text-orange-500">Expiring soon</span>
                            @endif
                        </span>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if($client->risk_rating)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $client->riskBadgeColor() }}">
                            {{ ucfirst($client->risk_rating) }}
                        </span>
                    @else
                        <span class="text-gray-300 text-xs">Unrated</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $client->statusBadgeColor() }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @php
                        $screenColors = [
                            'clear'        => 'bg-green-100 text-green-700',
                            'match'        => 'bg-red-100 text-red-700',
                            'pending'      => 'bg-amber-100 text-amber-700',
                            'not_screened' => 'bg-gray-100 text-gray-500',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $screenColors[$client->screening_status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst(str_replace('_', ' ', $client->screening_status)) }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden xl:table-cell text-xs
                    {{ $client->isReviewDue() ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                    {{ $client->next_review_date ? $client->next_review_date->format('d M Y') : '—' }}
                    @if($client->isReviewDue() && $client->next_review_date)
                        <span class="block text-red-500 font-normal">Due</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
                       class="text-sm text-blue-600 hover:underline font-medium">
                        View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-16 text-center">
                    <p class="text-gray-400 mb-3">No clients found</p>
                    <a href="{{ route('tenant.clients.create', $tenant->slug) }}"
                       class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add your first client
                    </a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($clients->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $clients->links() }}
        </div>
    @endif
</div>

@endsection
