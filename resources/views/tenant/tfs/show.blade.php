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

<div class="flex items-center justify-between mb-6">
    <a href="{{ route('tenant.tfs.index', $tenant->slug) }}"
       class="text-sm text-gray-500 hover:text-gray-700">← Back to TFS Submissions</a>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $submission->statusBadge() }}">
        {{ ucfirst($submission->status) }}
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- TFS links list --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">TFS Survey Links</h2>
            </div>

            @forelse($submission->tfs_urls ?? [] as $i => $entry)
            @php
                $urlStatus    = $entry['status'] ?? 'pending';
                $statusColour = match($urlStatus) {
                    'submitted' => 'bg-green-100 text-green-700',
                    'failed'    => 'bg-red-100 text-red-700',
                    default     => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <div class="px-5 py-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        @if($entry['label'] ?? null)
                        <p class="text-sm font-medium text-gray-800">{{ $entry['label'] }}</p>
                        @endif
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $entry['url'] }}</p>

                        @if(!empty($entry['message']) && $urlStatus !== 'pending')
                        <p class="text-xs mt-1 {{ $urlStatus === 'submitted' ? 'text-green-600' : 'text-red-500' }}">
                            {{ $entry['message'] }}
                        </p>
                        @endif

                        @if(!empty($entry['submitted_at']))
                        <p class="text-xs text-gray-400 mt-0.5">
                            Submitted {{ \Carbon\Carbon::parse($entry['submitted_at'])->format('d M Y H:i') }}
                        </p>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColour }}">
                            {{ ucfirst($urlStatus) }}
                        </span>

                        @if($urlStatus === 'submitted' && isset($entry['snapshot_path']))
                        <a href="{{ route('tenant.tfs.snapshot', [$tenant->slug, $submission->id, $i]) }}"
                           target="_blank"
                           class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            </svg>
                            Download evidence
                        </a>
                        @elseif($urlStatus === 'submitted')
                        <span class="text-xs text-gray-400">No snapshot</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">No URLs recorded.</div>
            @endforelse
        </div>
    </div>

    {{-- Submit panel --}}
    <div class="space-y-4">
        @if(in_array($submission->status, ['draft', 'partial', 'screened']))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Submit Response</h2>

            <form action="{{ route('tenant.tfs.submit', [$tenant->slug, $submission->id]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Response</label>
                    <select name="response"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="no_match" {{ ($submission->selected_response ?? '') === 'no_match' ? 'selected' : '' }}>
                            No match identified
                        </option>
                        <option value="partial_match" {{ ($submission->selected_response ?? '') === 'partial_match' ? 'selected' : '' }}>
                            Partial match
                        </option>
                        <option value="confirmed_match" {{ ($submission->selected_response ?? '') === 'confirmed_match' ? 'selected' : '' }}>
                            Confirmed match
                        </option>
                    </select>
                </div>

                @php $pending = collect($submission->tfs_urls ?? [])->whereNotIn('status', ['submitted'])->count(); @endphp

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-lg transition"
                        onclick="return confirm('Submit {{ $pending }} TFS URL(s) with the selected response?')">
                    Submit {{ $pending }} URL(s) &amp; Capture Evidence
                </button>
                <p class="mt-2 text-xs text-gray-400 text-center">
                    A PDF confirmation will be saved automatically.
                </p>
            </form>
        </div>
        @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <p class="text-sm font-semibold text-green-800">All submitted</p>
            @if($submission->submitted_at)
            <p class="text-xs text-green-600 mt-1">{{ $submission->submitted_at->format('d M Y H:i') }}</p>
            @endif
            <p class="text-xs text-green-700 mt-1">Response: {{ $submission->responseLabel() }}</p>
        </div>
        @endif

        {{-- Meta --}}
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-xs text-gray-500 space-y-1.5">
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
                <span>Links</span>
                <span>{{ $submission->totalUrls() }}</span>
            </div>
            <div class="flex justify-between">
                <span>Submitted</span>
                <span>{{ $submission->submittedCount() }} / {{ $submission->totalUrls() }}</span>
            </div>
        </div>
    </div>

</div>

@endsection
