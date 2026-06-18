@extends('layouts.tenant')
@section('title', 'Screening — ' . $tenant->name)
@section('page-title', 'Screening')
@section('page-subtitle', 'AML & sanctions screening via Blue Arrow Sentinel')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── SEARCH FORM ──────────────────────────────────────────────────── --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ type: '{{ $client && $client->client_type !== 'individual' ? 'entity' : ($client ? 'individual' : 'entity') }}' }">

            <h2 class="text-sm font-semibold text-gray-700 mb-4">Run screening</h2>

            {{-- Pre-loaded client notice --}}
            @if($client)
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-xs font-semibold text-blue-700 mb-1">Pre-loaded client</p>
                <p class="text-sm text-gray-800 font-medium">{{ $client->displayName() }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    Result will be saved to this client's profile.
                    <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}" class="text-blue-600 hover:underline ml-1">View profile →</a>
                </p>
            </div>
            @endif

            <form method="POST" action="{{ route('tenant.screening.run', $tenant->slug) }}" class="space-y-4">
                @csrf

                @if($client)
                <input type="hidden" name="client_id" value="{{ $client->id }}">
                @endif

                {{-- Entity type toggle --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                        <button type="button" @click="type='entity'"
                                :class="type==='entity' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                                class="flex-1 py-2 text-sm font-medium transition">
                            Entity / Company
                        </button>
                        <button type="button" @click="type='individual'"
                                :class="type==='individual' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                                class="flex-1 py-2 text-sm font-medium transition border-l border-gray-200">
                            Individual
                        </button>
                    </div>
                    <input type="hidden" name="entity_type" :value="type">
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        <span x-show="type==='entity'">Company name</span>
                        <span x-show="type==='individual'">Full name</span>
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="search_query"
                           value="{{ old('search_query', $client ? ($client->client_type !== 'individual' ? $client->company_name : $client->full_name) : '') }}"
                           required placeholder="Enter name to screen..."
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Country --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Country <span class="text-red-500">*</span></label>
                    <input type="text" name="country"
                           value="{{ old('country', $client ? ($client->country_of_incorporation ?? $client->nationality ?? 'UAE') : 'UAE') }}"
                           required placeholder="e.g. UAE, UK, US"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Entity fields --}}
                <div x-show="type==='entity'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Trade licence number</label>
                        <input type="text" name="license_number"
                               value="{{ old('license_number', $client?->trade_license_no ?? '') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of issue</label>
                        <input type="date" name="date_of_issue"
                               value="{{ old('date_of_issue', $client?->trade_license_issue?->format('Y-m-d') ?? '') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Individual fields --}}
                <div x-show="type==='individual'" x-cloak class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                        <input type="date" name="dob"
                               value="{{ old('dob', $client?->dob?->format('Y-m-d') ?? '') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                        <input type="text" name="nationality"
                               value="{{ old('nationality', $client?->nationality ?? '') }}"
                               placeholder="e.g. UAE, British, Indian"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Run screening
                </button>
            </form>

            @if(session('error'))
            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                {{ session('error') }}
            </div>
            @endif
        </div>
    </div>

    {{-- ── RESULTS ───────────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2">

        @if(isset($result))

        {{-- Result header --}}
        <div class="rounded-xl border p-5 mb-5 {{ $result['status'] === 'match' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
            <div class="flex items-center gap-3">
                @if($result['status'] === 'match')
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-red-800">Potential match found</p>
                    <p class="text-sm text-red-600">{{ $result['total_hits'] }} hit(s) for "{{ $query }}" — review required</p>
                    @if($client)
                    <p class="text-xs text-red-500 mt-0.5">Result saved to {{ $client->displayName() }}'s profile</p>
                    @endif
                </div>
                @else
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-green-800">Clear — no matches found</p>
                    <p class="text-sm text-green-600">No sanctions, PEP or adverse media hits for "{{ $query }}"</p>
                    @if($client)
                    <p class="text-xs text-green-600 mt-0.5">Result saved to {{ $client->displayName() }}'s profile</p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Hits list --}}
        @if(!empty($result['hits']))
        <div class="bg-white rounded-xl border border-gray-200 mb-5">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Screening hits ({{ count($result['hits']) }})</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($result['hits'] as $hit)
                <div class="px-5 py-4">
                    <p class="font-semibold text-gray-800">{{ $hit['name'] ?? 'Unknown' }}</p>
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @if(!empty($hit['type']))
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">{{ $hit['type'] }}</span>
                        @endif
                        @if(!empty($hit['riskLevel']))
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $hit['riskLevel']==='CRITICAL' ? 'bg-red-100 text-red-700' : ($hit['riskLevel']==='HIGH' ? 'bg-orange-100 text-orange-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $hit['riskLevel'] }}
                        </span>
                        @endif
                        @if(!empty($hit['list']['name']))
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $hit['list']['name'] }}</span>
                        @endif
                        @if(!empty($hit['matchScore']))
                        <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">{{ $hit['matchScore'] }}% match</span>
                        @endif
                        @if(!empty($hit['matchType']))
                        <span class="text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full">{{ ucfirst($hit['matchType']) }}</span>
                        @endif
                    </div>
                    @if(!empty($hit['programs']) && is_array($hit['programs']))
                    <p class="text-xs text-gray-500 mt-1">{{ implode(', ', $hit['programs']) }}</p>
                    @endif
                    @if(!empty($hit['reason']))
                    <p class="text-xs text-gray-400 mt-0.5">{{ $hit['reason'] }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Go back to client profile --}}
        @if($client)
        <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition">
            ← Back to {{ $client->displayName() }}'s profile
        </a>
        @endif

        @else

        {{-- Empty state --}}
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-16 text-center">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <p class="text-gray-500 font-medium mb-1">No screening run yet</p>
            <p class="text-sm text-gray-400">Enter a name and run a screening to see results here.</p>
            <p class="text-xs text-gray-400 mt-3">Screens against UN, OFAC, EU, UAE and other sanctions lists.</p>
        </div>

        @endif
    </div>
</div>

{{-- ── SCREENING HISTORY ─────────────────────────────────────────────── --}}
<div class="mt-8">
    <div class="bg-white rounded-xl border border-gray-200">

        {{-- Header + filters --}}
        <div class="px-5 py-4 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                    <h2 class="text-sm font-semibold text-gray-700">Screening history</h2>
                    <p class="text-xs text-gray-400 mt-0.5">All KYC and ad-hoc screenings in one place</p>
                </div>
                <form method="GET" action="{{ route('tenant.screening', $tenant->slug) }}"
                      class="flex flex-wrap gap-2 items-center">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name..."
                           class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-44">
                    <select name="status"
                            class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All statuses</option>
                        <option value="clear"  {{ request('status') === 'clear'  ? 'selected' : '' }}>Clear</option>
                        <option value="match"  {{ request('status') === 'match'  ? 'selected' : '' }}>Match</option>
                    </select>
                    <select name="source"
                            class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All sources</option>
                        <option value="adhoc" {{ request('source') === 'adhoc' ? 'selected' : '' }}>Ad-hoc</option>
                        <option value="kyc"   {{ request('source') === 'kyc'   ? 'selected' : '' }}>KYC</option>
                    </select>
                    <button type="submit"
                            class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Filter
                    </button>
                    @if(request('search') || request('status') || request('source'))
                    <a href="{{ route('tenant.screening', $tenant->slug) }}"
                       class="px-3 py-1.5 text-xs text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Clear
                    </a>
                    @endif
                </form>
            </div>
        </div>

        @if(isset($logs) && $logs->isEmpty())
        <div class="px-5 py-12 text-center text-sm text-gray-400">
            No screenings recorded yet. Run a screening above to start building history.
        </div>
        @elseif(isset($logs))

        {{-- Log table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Name screened</th>
                        <th class="px-4 py-3 font-medium">Client</th>
                        <th class="px-4 py-3 font-medium">Type</th>
                        <th class="px-4 py-3 font-medium">Source</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Hits</th>
                        <th class="px-4 py-3 font-medium">Reference</th>
                        <th class="px-4 py-3 font-medium">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">
                            {{ $log->created_at->format('d M Y') }}<br>
                            <span class="text-gray-400">{{ $log->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800 max-w-[200px] truncate">
                            {{ $log->query }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            @if($log->client)
                            <a href="{{ route('tenant.clients.show', [$tenant->slug, $log->client->id]) }}"
                               class="text-blue-600 hover:underline">
                                {{ $log->client->displayName() }}
                            </a>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 capitalize">{{ $log->entity_type }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $log->source === 'kyc' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $log->source === 'kyc' ? 'KYC' : 'Ad-hoc' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $log->statusBadge() }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $log->total_hits > 0 ? $log->total_hits : '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400 font-mono">
                            {{ $log->reference ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $log->screener?->name ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
        @endif

        @endif
    </div>
</div>

@endsection
