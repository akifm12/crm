@extends('layouts.tenant')
@section('title', 'Dashboard — ' . $tenant->name)
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your compliance portfolio')

@section('content')

@php
$total = $stats['total'];
$riskPct = fn($n) => $total > 0 ? round($n / $total * 100) : 0;
@endphp

{{-- ── TOP STAT CARDS ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total clients</p>
        <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-400 mt-1">
            <span class="text-green-600 font-semibold">{{ $stats['active'] }} active</span>
            @if($stats['pending']) · {{ $stats['pending'] }} pending @endif
        </p>
    </div>

    <div class="bg-white rounded-xl border {{ $stats['reviewOverdue'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200' }} p-4">
        <p class="text-xs font-semibold {{ $stats['reviewOverdue'] > 0 ? 'text-red-500' : 'text-gray-400' }} uppercase tracking-wide mb-1">KYC Reviews</p>
        <p class="text-3xl font-bold text-gray-900">{{ $stats['reviewOverdue'] + $stats['reviewDueSoon'] }}</p>
        <p class="text-xs mt-1">
            @if($stats['reviewOverdue'] > 0)
            <span class="text-red-600 font-semibold">{{ $stats['reviewOverdue'] }} overdue</span>
            @if($stats['reviewDueSoon']) · @endif
            @endif
            @if($stats['reviewDueSoon'] > 0)
            <span class="text-amber-600">{{ $stats['reviewDueSoon'] }} due soon</span>
            @endif
            @if(!$stats['reviewOverdue'] && !$stats['reviewDueSoon'])
            <span class="text-gray-400">All up to date</span>
            @endif
        </p>
    </div>

    <div class="bg-white rounded-xl border {{ ($stats['licenceExpired'] + $stats['docsExpired']) > 0 ? 'border-red-200 bg-red-50' : ($stats['licenceExpiring'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-gray-200') }} p-4">
        <p class="text-xs font-semibold {{ ($stats['licenceExpired'] + $stats['docsExpired']) > 0 ? 'text-red-500' : 'text-gray-400' }} uppercase tracking-wide mb-1">Doc expiry</p>
        <p class="text-3xl font-bold text-gray-900">{{ $stats['licenceExpired'] + $stats['docsExpired'] }}</p>
        <p class="text-xs mt-1">
            @if($stats['licenceExpired'] > 0)<span class="text-red-600 font-semibold">{{ $stats['licenceExpired'] }} licence expired</span>@endif
            @if($stats['docsExpired'] > 0) · <span class="text-red-600 font-semibold">{{ $stats['docsExpired'] }} doc expired</span>@endif
            @if(!$stats['licenceExpired'] && !$stats['docsExpired'])
            <span class="{{ $stats['licenceExpiring'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $stats['licenceExpiring'] + $stats['docsExpiring'] }} expiring soon</span>
            @endif
        </p>
    </div>

    <div class="bg-white rounded-xl border {{ $stats['screeningMatch'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200' }} p-4">
        <p class="text-xs font-semibold {{ $stats['screeningMatch'] > 0 ? 'text-red-500' : 'text-gray-400' }} uppercase tracking-wide mb-1">Screening</p>
        <p class="text-3xl font-bold text-gray-900">{{ $stats['unscreened'] }}</p>
        <p class="text-xs mt-1">
            <span class="text-gray-400">not screened</span>
            @if($stats['screeningMatch'] > 0)
            · <span class="text-red-600 font-semibold">{{ $stats['screeningMatch'] }} match(es)</span>
            @endif
        </p>
    </div>
</div>

{{-- ── RISK BREAKDOWN + TYPE BREAKDOWN + GOAML ────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    {{-- Risk breakdown --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Risk breakdown</h3>
        @if($stats['total'] > 0)
        <div class="space-y-2">
            @foreach([['High','riskHigh','bg-red-500','text-red-700'],['Medium','riskMedium','bg-amber-400','text-amber-700'],['Low','riskLow','bg-green-500','text-green-700'],['Unrated','riskUnrated','bg-gray-300','text-gray-500']] as [$label,$key,$bar,$tc])
            <div>
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="{{ $tc }} font-medium">{{ $label }}</span>
                    <span class="font-mono text-gray-600">{{ $stats[$key] }} <span class="text-gray-400">({{ $riskPct($stats[$key]) }}%)</span></span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="{{ $bar }} h-full rounded-full transition-all" style="width:{{ $riskPct($stats[$key]) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @if($stats['edd'] > 0)
        <p class="text-xs text-purple-600 font-semibold mt-3">{{ $stats['edd'] }} client(s) on Enhanced Due Diligence</p>
        @endif
        @else
        <p class="text-sm text-gray-400 mt-2">No clients yet.</p>
        @endif
    </div>

    {{-- Client type breakdown --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Client types</h3>
        @php
        $typeLabels = ['corporate_local'=>'Corporate — Local','corporate_import'=>'Corporate — Import','corporate_export'=>'Corporate — Export','individual'=>'Individual'];
        $typeColors = ['corporate_local'=>'bg-blue-500','corporate_import'=>'bg-purple-500','corporate_export'=>'bg-amber-500','individual'=>'bg-gray-400'];
        @endphp
        @if($stats['total'] > 0)
        <div class="space-y-2">
            @foreach($typeLabels as $key => $label)
            @php $count = $stats['typeBreakdown'][$key] ?? 0; @endphp
            <div>
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-gray-600">{{ $label }}</span>
                    <span class="font-mono text-gray-600">{{ $count }}</span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="{{ $typeColors[$key] }} h-full rounded-full" style="width:{{ $riskPct($count) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400 mt-2">No clients yet.</p>
        @endif
    </div>

    {{-- goAML summary --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide">goAML reports</h3>
            <a href="{{ route('tenant.goaml', $tenant->slug) }}" class="text-xs text-blue-600 hover:underline">View all</a>
        </div>
        <p class="text-3xl font-bold text-gray-900 mb-1">{{ $stats['goamlTotal'] }}</p>
        <p class="text-xs text-gray-400 mb-4">
            <span class="text-blue-600 font-semibold">{{ $stats['goamlMonth'] }}</span> this month
        </p>
        @if($recent_goaml->count())
        <div class="space-y-1.5">
            @foreach($recent_goaml as $rpt)
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600 truncate max-w-32">{{ $rpt->client_name }}</span>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-bold px-1.5 py-0.5 rounded {{ $rpt->reportTypeBadgeColor() }}">{{ $rpt->report_type }}</span>
                    <span class="text-xs text-gray-400">{{ $rpt->created_at->format('d M') }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-xs text-gray-400">No reports filed yet.</p>
        <a href="{{ route('tenant.goaml.create', $tenant->slug) }}" class="text-xs text-blue-600 hover:underline mt-1 block">File first report →</a>
        @endif
    </div>
</div>

{{-- ── ALERT PANELS + RECENT ───────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

    {{-- Licence expiry alerts --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Licence expiry</h2>
            <span class="text-xs text-gray-400">Next 60 days</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($expiry_alerts as $client)
            <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
               class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                <p class="text-sm font-medium text-gray-800 truncate max-w-24">{{ $client->displayName() }}</p>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0
                    {{ $client->trade_license_expiry->isPast() ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $client->trade_license_expiry->isPast() ? 'Expired' : $client->trade_license_expiry->format('d M') }}
                </span>
            </a>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">No alerts</div>
            @endforelse
        </div>
    </div>

    {{-- Document expiry alerts --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Document expiry</h2>
            <span class="text-xs text-gray-400">Next 30 days</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($doc_alerts as $doc)
            <a href="{{ route('tenant.clients.show', [$tenant->slug, $doc->bullion_client_id]) }}"
               class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                <div class="min-w-0 mr-2">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $doc->client?->displayName() ?? '—' }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $doc->document_label }}</p>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0
                    {{ $doc->isExpired() ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $doc->isExpired() ? 'Expired' : $doc->expiry_date->format('d M') }}
                </span>
            </a>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">No alerts</div>
            @endforelse
        </div>
    </div>

    {{-- KYC reviews due --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">KYC reviews due</h2>
            <span class="text-xs text-gray-400">Next 30 days</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($review_alerts as $client)
            <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
               class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                <div class="min-w-0 mr-2">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $client->displayName() }}</p>
                    <span class="inline-block text-xs px-1.5 py-0.5 rounded {{ $client->riskBadgeColor() }}">
                        {{ ucfirst($client->risk_rating ?? 'unrated') }}
                    </span>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0
                    {{ $client->next_review_date->isPast() ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $client->next_review_date->isPast() ? 'Overdue' : $client->next_review_date->format('d M') }}
                </span>
            </a>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 text-sm">No reviews due</div>
            @endforelse
        </div>
    </div>

    {{-- Recently added --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Recently added</h2>
            <a href="{{ route('tenant.clients.index', $tenant->slug) }}" class="text-xs text-blue-600 hover:underline">All</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recent as $client)
            <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700 flex-shrink-0">
                    {{ strtoupper(substr($client->displayName(), 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $client->displayName() }}</p>
                    <p class="text-xs text-gray-400">{{ $client->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $client->statusBadgeColor() }} flex-shrink-0">
                    {{ ucfirst($client->status) }}
                </span>
            </a>
            @empty
            <div class="px-4 py-8 text-center">
                <p class="text-gray-400 text-sm mb-2">No clients yet</p>
                <a href="{{ route('tenant.clients.create', $tenant->slug) }}" class="text-sm text-blue-600 hover:underline">Add your first client</a>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
