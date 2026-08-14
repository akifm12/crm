@extends('layouts.tenant')
@section('title', Str::limit($submission->title, 60) . ' — TFS')
@section('page-title', 'TFS Submission')
@section('page-subtitle', Str::limit($submission->title, 80))

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5 text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="flex items-start justify-between mb-6 gap-4">
    <a href="{{ route('tenant.tfs.index', $tenant->slug) }}"
       class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        ← Back to TFS Submissions
    </a>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $submission->statusBadge() }}">
        {{ ucfirst($submission->status) }}
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

{{-- Left column --}}
<div class="lg:col-span-2 space-y-5">

    {{-- Screening results --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Alerted Names &amp; Screening</h2>
            @if($submission->alert_url && empty($submission->alerted_names))
            <a href="{{ route('tenant.tfs.screen', [$tenant->slug, $submission->id]) }}"
               class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Extract &amp; Screen Names
            </a>
            @endif
        </div>

        @if(!empty($submission->alerted_names))
        <div class="divide-y divide-gray-100">
            @foreach($submission->screening_results ?? [] as $result)
            @php
                $matched = $result['matched'] ?? false;
                $indiv   = $result['indiv'] ?? [];
                $entity  = $result['entity'] ?? [];
            @endphp
            <div class="px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="font-medium text-gray-900 text-sm">{{ $result['name'] }}</div>
                    @if($matched)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">
                        ⚠ Match found
                    </span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700">
                        ✓ No match
                    </span>
                    @endif
                </div>
                @if(!empty($indiv['hits']))
                <p class="mt-1 text-xs text-red-600">Individual: {{ count($indiv['hits']) }} hit(s)</p>
                @endif
                @if(!empty($entity['hits']))
                <p class="mt-1 text-xs text-red-600">Entity: {{ count($entity['hits']) }} hit(s)</p>
                @endif
            </div>
            @endforeach
        </div>
        {{-- Allow re-screening with updated names --}}
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50" x-data="{ open: false }">
            <button @click="open = !open" class="text-xs text-gray-500 hover:text-gray-700">
                ↺ Re-screen with different names
            </button>
            <div x-show="open" x-cloak class="mt-3">
                <form action="{{ route('tenant.tfs.names', [$tenant->slug, $submission->id]) }}" method="POST">
                    @csrf
                    <textarea name="names" rows="4" placeholder="One name per line"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">{{ implode("\n", $submission->alerted_names ?? []) }}</textarea>
                    <button type="submit"
                            class="mt-2 bg-blue-600 text-white text-xs font-medium px-4 py-1.5 rounded-lg hover:bg-blue-700 transition">
                        Screen Names
                    </button>
                </form>
            </div>
        </div>
        @else
        {{-- No names yet — manual entry always available --}}
        <div class="px-5 py-5 space-y-4">
            @if($submission->alert_url)
            <div class="flex items-center justify-between p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-xs text-amber-800">Auto-extraction failed or not yet run.</p>
                <a href="{{ route('tenant.tfs.screen', [$tenant->slug, $submission->id]) }}"
                   class="text-xs text-blue-600 hover:underline font-medium">Try again</a>
            </div>
            @endif
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">Enter alerted names manually — one per line:</p>
                <form action="{{ route('tenant.tfs.names', [$tenant->slug, $submission->id]) }}" method="POST">
                    @csrf
                    <textarea name="names" rows="6"
                              placeholder="JOHN DOE&#10;ACME TRADING LLC&#10;JANE SMITH"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    <button type="submit"
                            class="mt-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Screen Names
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- TFS URLs --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">TFS Survey Links</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($submission->tfs_urls ?? [] as $i => $entry)
            @php
                $urlStatus = $entry['status'] ?? 'pending';
                $statusColour = match($urlStatus) {
                    'submitted' => 'bg-green-100 text-green-700',
                    'failed'    => 'bg-red-100 text-red-700',
                    default     => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <div class="px-5 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        @if($entry['label'] ?? null)
                        <div class="text-xs font-medium text-gray-700">{{ $entry['label'] }}</div>
                        @endif
                        <div class="text-xs text-gray-400 truncate">{{ $entry['url'] }}</div>
                        @if(!empty($entry['message']))
                        <div class="text-xs mt-0.5 {{ $urlStatus === 'submitted' ? 'text-green-600' : 'text-red-500' }}">
                            {{ $entry['message'] }}
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColour }}">
                            {{ ucfirst($urlStatus) }}
                        </span>
                        @if($urlStatus === 'submitted' && isset($entry['snapshot_path']))
                        <a href="{{ route('tenant.tfs.snapshot', [$tenant->slug, $submission->id, $i]) }}"
                           target="_blank"
                           class="text-xs text-blue-600 hover:underline">Snapshot</a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- Right column: actions --}}
<div class="space-y-5">

    {{-- Recommended response + submit --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
        <h2 class="font-semibold text-gray-800">Submit Response</h2>

        @if($submission->status === 'submitted')
        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
            <p class="text-sm text-green-800 font-medium">All URLs submitted.</p>
            @if($submission->submitted_at)
            <p class="text-xs text-green-600 mt-0.5">{{ $submission->submitted_at->format('d M Y H:i') }}</p>
            @endif
        </div>
        @else
        <form action="{{ route('tenant.tfs.submit', [$tenant->slug, $submission->id]) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Response to submit</label>
                @php
                    $rec = $submission->selected_response ?? $submission->recommended_response ?? 'no_match';
                @endphp
                <select name="response"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="no_match" {{ $rec === 'no_match' ? 'selected' : '' }}>No match identified</option>
                    <option value="partial_match" {{ $rec === 'partial_match' ? 'selected' : '' }}>Partial match</option>
                    <option value="confirmed_match" {{ $rec === 'confirmed_match' ? 'selected' : '' }}>Confirmed match</option>
                </select>
                @if($submission->recommended_response)
                <p class="mt-1 text-xs text-gray-400">
                    Recommended based on screening:
                    <span class="{{ $rec === 'no_match' ? 'text-green-600' : 'text-red-600' }} font-medium">
                        {{ $submission->responseLabel() }}
                    </span>
                </p>
                @endif
            </div>

            @php
                $pending = collect($submission->tfs_urls ?? [])->where('status', '!=', 'submitted')->count();
            @endphp

            @if($pending === 0)
            <p class="text-xs text-gray-400">All links already submitted.</p>
            @else
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded-lg transition"
                    onclick="return confirm('Submit {{ $pending }} pending TFS URL(s) with the selected response?')">
                Submit {{ $pending }} Pending URL(s)
            </button>
            @endif
        </form>
        @endif
    </div>

    {{-- Alert link --}}
    @if($submission->alert_url)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-600 mb-1">UN Alert URL</p>
        <a href="{{ $submission->alert_url }}" target="_blank"
           class="text-xs text-blue-600 hover:underline break-all">{{ $submission->alert_url }}</a>
    </div>
    @endif

    {{-- Meta --}}
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-xs text-gray-500 space-y-1">
        <div class="flex justify-between">
            <span>Created</span>
            <span>{{ $submission->created_at->format('d M Y') }}</span>
        </div>
        @if($submission->creator)
        <div class="flex justify-between">
            <span>By</span>
            <span>{{ $submission->creator->name }}</span>
        </div>
        @endif
        <div class="flex justify-between">
            <span>Links total</span>
            <span>{{ $submission->totalUrls() }}</span>
        </div>
        <div class="flex justify-between">
            <span>Submitted</span>
            <span>{{ $submission->submittedCount() }}</span>
        </div>
    </div>

</div>
</div>

@endsection
