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
                    'clear'     => 'bg-green-100 text-green-700',
                    'review'    => 'bg-red-100 text-red-700',
                    'failed'    => 'bg-red-100 text-red-700',
                    'used'      => 'bg-amber-100 text-amber-700',
                    default     => 'bg-gray-100 text-gray-500',
                };
                $statusLabel = match($urlStatus) {
                    'clear'  => 'Checked — clear',
                    'review' => 'Needs your review',
                    'used'   => 'Already used',
                    default  => ucfirst($urlStatus),
                };
            @endphp
            <div class="px-5 py-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }}" id="tfs-row-{{ $i }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        @if($entry['label'] ?? null)
                        <p class="text-sm font-medium text-gray-800">{{ $entry['label'] }}</p>
                        @endif
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $entry['url'] }}</p>

                        @if(!empty($entry['message']) && $urlStatus !== 'pending')
                        <p class="text-xs mt-1 {{ in_array($urlStatus, ['submitted','clear']) ? 'text-green-600' : (in_array($urlStatus, ['used']) ? 'text-amber-600' : 'text-red-500') }}">
                            {{ $entry['message'] }}
                        </p>
                        @endif

                        <p class="text-xs mt-1 text-blue-600 hidden" data-live="{{ $i }}"></p>

                        @if(!empty($entry['submitted_at']))
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $urlStatus === 'submitted' ? 'Submitted' : 'Attempted' }} {{ \Carbon\Carbon::parse($entry['submitted_at'])->format('d M Y H:i') }}
                        </p>
                        @elseif(!empty($entry['checked_at']))
                        <p class="text-xs text-gray-400 mt-0.5">Checked {{ \Carbon\Carbon::parse($entry['checked_at'])->format('d M Y H:i') }}</p>
                        @endif

                        {{-- Possible matches in the tenant's client base --}}
                        @if(!empty($entry['candidates']))
                        <div class="mt-3 border border-red-200 bg-red-50 rounded-lg p-3 space-y-2">
                            <p class="text-xs font-semibold text-red-800">Possible matches — not submitted automatically</p>
                            @foreach($entry['candidates'] as $cand)
                            <div class="text-xs text-gray-700">
                                <a href="{{ route('tenant.clients.show', [$tenant->slug, $cand['client_id']]) }}" class="font-semibold text-blue-700 hover:underline">{{ $cand['client_name'] }}</a>
                                <span class="text-gray-500">
                                    · {{ $cand['role'] }} “{{ $cand['subject'] }}”
                                    · matched on {{ $cand['matched_on'] === 'id' ? 'ID number' : 'name' }}
                                    @if(($cand['strength'] ?? '') === 'weak')(weak: single word)@endif
                                </span>
                                @if(!empty($cand['snippet']))
                                <p class="text-gray-400 italic mt-0.5">…{{ $cand['snippet'] }}…</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @elseif($urlStatus === 'review' && !empty($entry['page_excerpt']))
                        <details class="mt-2 text-xs text-gray-500">
                            <summary class="cursor-pointer">What the browser could read</summary>
                            <pre class="mt-1 whitespace-pre-wrap bg-gray-50 border border-gray-200 rounded p-2 max-h-40 overflow-auto">{{ $entry['page_excerpt'] }}</pre>
                        </details>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColour }}" data-badge="{{ $i }}">
                            {{ $statusLabel }}
                        </span>

                        @if(!empty($entry['pre_snapshot_path']))
                        <a href="{{ route('tenant.tfs.snapshot', [$tenant->slug, $submission->id, $i, 'type' => 'before']) }}" target="_blank"
                           class="text-xs font-medium text-blue-600 hover:text-blue-800">Evidence: alert + answer</a>
                        @endif
                        @if($urlStatus === 'submitted' && !empty($entry['snapshot_path']))
                        <a href="{{ route('tenant.tfs.snapshot', [$tenant->slug, $submission->id, $i]) }}" target="_blank"
                           class="text-xs font-medium text-blue-600 hover:text-blue-800">Evidence: confirmation</a>
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

    <div class="space-y-4">

        {{-- Automatic check + submit --}}
        @if(count($runIndices))
        <div class="bg-white rounded-xl border border-gray-200 p-5" id="tfs-runner">
            <h2 class="font-semibold text-gray-800 mb-1">Check my clients &amp; submit</h2>
            <p class="text-xs text-gray-500 mb-4">
                For each link the app reads the alert, checks it against your clients, signatories, shareholders and UBOs,
                and submits <strong>“No match”</strong> only if nothing matches. Any possible match is held for you to review.
                A PDF of the alert and the confirmation is saved for each link.
            </p>
            <button type="button" id="tfs-run-btn"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                Check {{ count($runIndices) }} link(s) &amp; submit
            </button>
            <p id="tfs-run-note" class="mt-2 text-xs text-gray-400 text-center">Takes around 30 seconds per link. Keep this page open.</p>
        </div>
        @endif

        {{-- Manual submit (after you've reviewed a possible match, or for a partial / confirmed answer) --}}
        @if(in_array($submission->status, ['draft', 'partial', 'screened']))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-1">Submit manually</h2>
            <p class="text-xs text-gray-500 mb-4">Use this once you have reviewed a possible match yourself, or to send a different answer. It skips the client check.</p>

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

                @php $pending = collect($submission->tfs_urls ?? [])->whereNotIn('status', ['submitted', 'used'])->count(); @endphp

                <button type="submit"
                        class="w-full bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold py-2.5 rounded-lg transition"
                        onclick="return confirm('Submit {{ $pending }} TFS link(s) with the selected response, without the client check?')">
                    Submit {{ $pending }} link(s) &amp; capture evidence
                </button>
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

@if(count($runIndices))
<script>
(function () {
    const indices   = @json($runIndices);
    const urlBase   = {!! json_encode(route('tenant.tfs.process', [$tenant->slug, $submission->id, 0])) !!};
    const csrf      = @json(csrf_token());
    const autoStart = @json(request()->boolean('auto'));
    const btn  = document.getElementById('tfs-run-btn');
    const note = document.getElementById('tfs-run-note');

    const urlFor = idx => urlBase.replace(/\/0$/, '/' + idx);
    const live   = (idx, text) => {
        const el = document.querySelector('[data-live="' + idx + '"]');
        if (!el) return;
        el.textContent = text;
        el.classList.remove('hidden');
    };

    async function step(idx, phase) {
        const res = await fetch(urlFor(idx), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ phase }),
        });
        if (!res.ok) throw new Error('Server returned ' + res.status + (res.status === 504 ? ' (timed out)' : ''));
        return res.json();
    }

    async function run() {
        btn.disabled = true;
        for (const idx of indices) {
            try {
                live(idx, 'Opening the link and checking your clients…');
                let r = await step(idx, 'preview');
                if (r.status === 'clear') {
                    live(idx, 'No match found — submitting and saving evidence…');
                    r = await step(idx, 'submit');
                }
                live(idx, r.message || r.status);
            } catch (e) {
                live(idx, 'Stopped: ' + e.message + '. Reload to see where it got to.');
            }
        }
        note.textContent = 'Done — refreshing…';
        setTimeout(() => { window.location.href = window.location.pathname; }, 1200);
    }

    btn.addEventListener('click', run);
    if (autoStart) run();
})();
</script>
@endif

@endsection
