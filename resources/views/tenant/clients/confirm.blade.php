@extends('layouts.tenant')
@section('title', 'Client Created — ' . $client->displayName())
@section('page-title', 'Client onboarded')
@section('page-subtitle', $client->displayName() . ' — ' . now()->format('d M Y'))

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Success banner --}}
    <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-5 flex items-start gap-4">
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-bold text-green-800">{{ $client->displayName() }} has been onboarded</p>
            <p class="text-xs text-green-600 mt-0.5">Client record created successfully on {{ now()->format('d F Y') }}</p>
        </div>
    </div>

    {{-- Screening result (if available) --}}
    @if($client->screening_status && $client->screening_status !== 'not_screened')
    <div class="bg-white rounded-xl border {{ $client->screening_status === 'match' ? 'border-red-200' : 'border-green-200' }} p-5 mb-5">
        <div class="flex items-center gap-3">
            <span class="text-sm font-semibold px-3 py-1 rounded-full {{ $client->screening_status === 'match' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                Screening: {{ ucfirst(str_replace('_', ' ', $client->screening_status)) }}
            </span>
            @if($client->screening_status === 'match')
            <p class="text-sm text-red-600">⚠️ Review matches before proceeding</p>
            @else
            <p class="text-sm text-green-600">✓ No sanctions or PEP matches found</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Download declaration --}}
    <div class="bg-white rounded-xl border border-blue-200 p-6 mb-5">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-gray-800">Download combined declaration</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    Pre-filled Word document with client details — print, obtain signature, upload signed copy to client profile.
                </p>
                @php
                    $isCorporate = $client->client_type !== 'individual';
                    $sector = app('sector');
                    $declCount = count($isCorporate ? ($sector['declarations_corporate'] ?? []) : ($sector['declarations_individual'] ?? []));
                @endphp
                <p class="text-xs text-blue-600 mt-1">{{ $declCount }} declarations · single signature required</p>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('tenant.clients.declaration.combined', [$tenant->slug, $client->id]) }}"
               class="w-full flex items-center justify-center gap-2 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download {{ $client->displayName() }}'s declaration
            </a>
        </div>
    </div>

    {{-- Summary --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">What was captured</h3>
        <div class="space-y-2">
            @php $isCorporate = $client->client_type !== 'individual'; @endphp
            @foreach([
                ['Client type',     ucfirst(str_replace('_', ' ', $client->client_type))],
                ['Risk rating',     ucfirst($client->risk_rating ?? '—')],
                ['CDD type',        ucfirst($client->cdd_type ?? '—')],
                ['Screening',       ucfirst(str_replace('_', ' ', $client->screening_status))],
                ['Next review',     $client->next_review_date?->format('d M Y') ?? '—'],
                ['Documents',       $client->documents()->count() . ' uploaded'],
            ] as [$label, $value])
            <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                <span class="text-xs text-gray-400">{{ $label }}</span>
                <span class="text-xs font-medium text-gray-700">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex gap-3">
        <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
           class="flex-1 flex items-center justify-center gap-2 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition">
            View full profile
        </a>
        <a href="{{ route('tenant.clients.create', $tenant->slug) }}"
           class="flex-1 flex items-center justify-center gap-2 py-2.5 text-sm font-medium text-white bg-gray-800 rounded-xl hover:bg-gray-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add another client
        </a>
    </div>

</div>
@endsection
