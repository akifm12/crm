@extends('layouts.admin')
@section('title', 'Screening — BlueArrow Portal')
@section('page-title', 'Screening')
@section('page-subtitle', 'AML & sanctions screening powered by Blue Arrow Sentinel')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── SEARCH FORM ──────────────────────────────────────────────────── --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ type: 'entity' }">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Run screening</h2>

            <form method="POST" action="{{ route('screening.run') }}" class="space-y-4">
                @csrf

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

                {{-- Name / query --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        <span x-show="type==='entity'">Company name</span>
                        <span x-show="type==='individual'">Full name</span>
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="query" value="{{ old('query') }}" required
                           placeholder="Enter name to screen..."
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Entity fields --}}
                <div x-show="type==='entity'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Trade licence number</label>
                        <input type="text" name="license_number" value="{{ old('license_number') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country of issue</label>
                        <input type="text" name="country_of_issue" value="{{ old('country_of_issue') }}"
                               placeholder="e.g. UAE"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of issue</label>
                        <input type="date" name="date_of_issue" value="{{ old('date_of_issue') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Individual fields --}}
                <div x-show="type==='individual'" x-cloak class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                        <input type="date" name="dob" value="{{ old('dob') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality') }}"
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

            {{-- Link to full Sentinel app --}}
            <div class="mt-5 pt-4 border-t border-gray-100">
                <a href="https://aml.bluearrow.ae" target="_blank"
                   class="flex items-center justify-between text-sm text-blue-600 hover:underline">
                    <span>Open full Sentinel platform</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                <p class="text-xs text-gray-400 mt-1">For case management, reports & monitoring</p>
            </div>
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
                    <p class="text-sm text-red-600">{{ $result['total_hits'] }} hit(s) returned for "{{ $query }}" — review required</p>
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
                </div>
                @endif
            </div>
        </div>

        {{-- Hits list --}}
        @if(!empty($result['hits']))
        <div class="bg-white rounded-xl border border-gray-200 mb-5">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Screening hits</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($result['hits'] as $hit)
                <div class="px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800">{{ $hit['name'] ?? $hit['entity_name'] ?? $hit['fullName'] ?? 'Unknown' }}</p>

                            @if(!empty($hit['type']) || !empty($hit['entityType']))
                            <span class="inline-block text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full mt-1">
                                {{ $hit['type'] ?? $hit['entityType'] ?? '' }}
                            </span>
                            @endif

                            @if(!empty($hit['listName']) || !empty($hit['source']) || !empty($hit['list']))
                            <span class="inline-block text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full mt-1 ml-1">
                                {{ $hit['listName'] ?? $hit['source'] ?? $hit['list'] ?? '' }}
                            </span>
                            @endif

                            @if(!empty($hit['score']) || !empty($hit['matchScore']))
                            <p class="text-xs text-gray-400 mt-1">
                                Match score: <span class="font-semibold text-gray-600">{{ $hit['score'] ?? $hit['matchScore'] }}%</span>
                            </p>
                            @endif

                            @if(!empty($hit['nationality']) || !empty($hit['country']))
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $hit['nationality'] ?? $hit['country'] ?? '' }}
                            </p>
                            @endif

                            @if(!empty($hit['reason']) || !empty($hit['remarks']))
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                {{ $hit['reason'] ?? $hit['remarks'] ?? '' }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Raw JSON toggle --}}
        <div x-data="{ show: false }" class="bg-white rounded-xl border border-gray-200">
            <button @click="show=!show"
                    class="w-full px-5 py-3 flex items-center justify-between text-sm text-gray-500 hover:text-gray-700 transition">
                <span>View raw API response</span>
                <svg class="w-4 h-4 transition-transform" :class="show ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="show" x-cloak class="px-5 pb-4 border-t border-gray-100">
                <pre class="text-xs text-gray-600 overflow-x-auto bg-gray-50 p-3 rounded-lg mt-3">{{ json_encode($rawData, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>

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
            <p class="text-xs text-gray-400 mt-3">Screens against UN, OFAC, EU, UAE and other sanctions lists via Blue Arrow Sentinel.</p>
        </div>

        @endif
    </div>
</div>

@endsection
