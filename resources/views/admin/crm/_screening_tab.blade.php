{{-- resources/views/admin/crm/_screening_tab.blade.php --}}
{{-- Include inside the CRM show.blade.php tabs section --}}

@php
    $screenResult   = $crm->screening_result ?? null;
    $screenStatus   = $crm->screening_status ?? 'not_screened';
    $screenColors   = [
        'clear'        => 'bg-green-100 text-green-700',
        'match'        => 'bg-red-100 text-red-700',
        'pending'      => 'bg-amber-100 text-amber-700',
        'not_screened' => 'bg-gray-100 text-gray-500',
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Screen client panel --}}
    <div class="space-y-4">

        {{-- Current status card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Screening status</h3>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-1 rounded-full text-sm font-semibold {{ $screenColors[$screenStatus] ?? 'bg-gray-100 text-gray-500' }}">
                    {{ ucfirst(str_replace('_', ' ', $screenStatus)) }}
                </span>
            </div>
            @if($crm->screening_date)
            <p class="text-xs text-gray-400">Last screened: {{ $crm->screening_date->format('d M Y, H:i') }}</p>
            @if($crm->screening_reference)
            <p class="text-xs text-gray-400">Ref: {{ $crm->screening_reference }}</p>
            @endif
            @else
            <p class="text-xs text-gray-400">Not yet screened</p>
            @endif
        </div>

        {{-- Screen company button --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Screen company</h3>
            <p class="text-xs text-gray-400 mb-3">
                Screens: <strong>{{ $crm->company_name }}</strong>
                @if($crm->license_number) · {{ $crm->license_number }} @endif
            </p>
            <form method="POST" action="{{ route('screening.client', $crm->id) }}">
                @csrf
                <button type="submit"
                        class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Screen company now
                </button>
            </form>
        </div>

        {{-- Screen shareholders --}}
        @if(($crm->shareholders ?? collect())->count())
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Screen shareholders</h3>
            <div class="space-y-2">
                @foreach($crm->shareholders as $sh)
                @php
                    $shResult = $screenResult['shareholders'][$sh->id] ?? null;
                @endphp
                <div class="flex items-center justify-between gap-2 p-2 bg-gray-50 rounded-lg">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-700 truncate">{{ $sh->shareholder_name }}</p>
                        <p class="text-xs text-gray-400">{{ $sh->nationality ?? '—' }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($shResult)
                        <span class="text-xs px-1.5 py-0.5 rounded-full {{ $shResult['status'] === 'match' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $shResult['status'] === 'match' ? $shResult['total_hits'].' hit(s)' : 'Clear' }}
                        </span>
                        @endif
                        <form method="POST" action="{{ route('screening.shareholder', $sh->id) }}">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 hover:underline font-medium">
                                Screen
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Link to full Sentinel --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <a href="https://aml.bluearrow.ae" target="_blank"
               class="flex items-center justify-between text-sm text-blue-600 hover:underline">
                <span>Open Sentinel for full report</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
            <p class="text-xs text-gray-400 mt-1">Case management, monitoring & PDF reports</p>
        </div>
    </div>

    {{-- Results panel --}}
    <div class="lg:col-span-2">

        @if($screenResult && isset($screenResult['hits']))

            {{-- Company result --}}
            <div class="bg-white rounded-xl border {{ isset($screenResult['status']) && $screenResult['status'] === 'match' ? 'border-red-200' : 'border-gray-200' }} mb-4">
                <div class="px-5 py-4 border-b {{ isset($screenResult['status']) && $screenResult['status'] === 'match' ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100' }} rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold {{ isset($screenResult['status']) && $screenResult['status'] === 'match' ? 'text-red-700' : 'text-gray-700' }}">
                            Company screening result — {{ $crm->company_name }}
                        </h3>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $screenColors[$screenResult['status'] ?? 'not_screened'] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ $screenResult['total_hits'] ?? 0 }} hit(s)
                        </span>
                    </div>
                </div>

                @if(!empty($screenResult['hits']))
                <div class="divide-y divide-gray-100">
                    @foreach($screenResult['hits'] as $hit)
                    <div class="px-5 py-3">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $hit['name'] ?? $hit['entity_name'] ?? $hit['fullName'] ?? 'Unknown' }}
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @if(!empty($hit['type']) || !empty($hit['entityType']))
                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                                {{ $hit['type'] ?? $hit['entityType'] ?? '' }}
                            </span>
                            @endif
                            @if(!empty($hit['listName']) || !empty($hit['source']) || !empty($hit['list']))
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                {{ $hit['listName'] ?? $hit['source'] ?? $hit['list'] ?? '' }}
                            </span>
                            @endif
                            @if(!empty($hit['score']) || !empty($hit['matchScore']))
                            <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">
                                {{ $hit['score'] ?? $hit['matchScore'] }}% match
                            </span>
                            @endif
                        </div>
                        @if(!empty($hit['reason']) || !empty($hit['remarks']))
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                            {{ $hit['reason'] ?? $hit['remarks'] ?? '' }}
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-6 text-center">
                    <p class="text-sm text-green-600 font-medium">✓ No matches found</p>
                    <p class="text-xs text-gray-400 mt-0.5">No sanctions, PEP or adverse media hits</p>
                </div>
                @endif
            </div>

            {{-- Shareholder results --}}
            @if(!empty($screenResult['shareholders']))
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Shareholder screening results</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($screenResult['shareholders'] as $shId => $shResult)
                    <div class="px-5 py-3">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-semibold text-gray-800">{{ $shResult['name'] ?? 'Unknown' }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $shResult['status'] === 'match' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $shResult['status'] === 'match' ? $shResult['total_hits'].' hit(s)' : 'Clear' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400">
                            Screened {{ isset($shResult['screened_at']) ? \Carbon\Carbon::parse($shResult['screened_at'])->format('d M Y, H:i') : '—' }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        @else
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <p class="text-gray-400 text-sm">No screening results yet.</p>
            <p class="text-xs text-gray-400 mt-1">Click "Screen company now" to run a check against sanctions and PEP lists.</p>
        </div>
        @endif
    </div>
</div>
