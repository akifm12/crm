@extends('layouts.tenant')
@section('title', Str::limit($submission->title, 60) . ' — TFS')
@section('page-title', 'TFS Submission')
@section('page-subtitle', Str::limit($submission->title, 80))

@section('content')

@php
    $searchNotes = $submission->screening_results['search_notes'] ?? '';
    $userIds = collect($submission->tfs_urls ?? [])
        ->flatMap(fn ($e) => [$e['attested_by'] ?? null, $e['prepared_by'] ?? null, $e['submitted_by'] ?? null])
        ->filter()->unique()->values();
    $userNames = $userIds->isEmpty() ? collect() : \App\Models\User::whereIn('id', $userIds)->pluck('name', 'id');
@endphp

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

<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 text-sm text-amber-900">
    <strong>Nothing is sent to the Ministry automatically.</strong>
    Search your client list for the designated names yourself, then use these steps for each link:
    <span class="font-semibold">1. read the link</span> →
    <span class="font-semibold">2. confirm your search and fill the form</span> (not sent) →
    <span class="font-semibold">3. confirm and submit</span>.
    A "match" answer opens an inquiry, so any answer other than a clear "no match" also needs a typed confirmation.
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
                $opts         = $entry['options'] ?? [];
                $statusColour = match($urlStatus) {
                    'submitted' => 'bg-green-100 text-green-700',
                    'ready'     => 'bg-blue-100 text-blue-700',
                    'prepared'  => 'bg-blue-100 text-blue-700',
                    'failed'    => 'bg-red-100 text-red-700',
                    'used'      => 'bg-amber-100 text-amber-700',
                    default     => 'bg-gray-100 text-gray-500',
                };
                $statusLabel = match($urlStatus) {
                    'ready'    => 'Link read — ready',
                    'prepared' => 'Filled — NOT submitted',
                    'used'     => 'Already used',
                    default    => ucfirst($urlStatus),
                };
                $prepareUrl  = route('tenant.tfs.prepare',  [$tenant->slug, $submission->id, $i]);
                $finalizeUrl = route('tenant.tfs.finalize', [$tenant->slug, $submission->id, $i]);
                $recordUrl   = route('tenant.tfs.record',   [$tenant->slug, $submission->id, $i]);
            @endphp
            <div class="px-5 py-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }}" id="tfs-row-{{ $i }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        @if($entry['label'] ?? null)
                        <p class="text-sm font-medium text-gray-800">{{ $entry['label'] }}</p>
                        @endif
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $entry['url'] }}</p>

                        @if(!empty($entry['message']) && $urlStatus !== 'pending')
                        <p class="text-xs mt-1 {{ $urlStatus === 'submitted' ? 'text-green-600' : (in_array($urlStatus, ['ready','prepared']) ? 'text-blue-700' : ($urlStatus === 'used' ? 'text-amber-600' : 'text-red-500')) }}">
                            {{ $entry['message'] }}
                        </p>
                        @endif

                        <p class="text-xs mt-1 text-blue-600 hidden" data-live="{{ $i }}"></p>

                        {{-- The record: who attested, what was sent, when --}}
                        @if(!empty($entry['prepared_answer']) && in_array($urlStatus, ['prepared', 'submitted']))
                        <div class="mt-2 text-xs text-gray-600 space-y-0.5">
                            <p>Answer: <strong class="text-gray-800">“{{ $entry['prepared_answer'] }}”</strong>@if(!empty($entry['manual'])) <span class="text-gray-400">(recorded manually)</span>@endif</p>
                            @if(!empty($entry['attested_at']))
                            <p>Search confirmed by {{ $userNames[$entry['attested_by'] ?? 0] ?? 'user' }} on {{ \Carbon\Carbon::parse($entry['attested_at'])->format('d M Y H:i') }}</p>
                            @endif
                            @if(!empty($entry['submitted_at']))
                            <p>Submitted by {{ $userNames[$entry['submitted_by'] ?? 0] ?? 'user' }} on {{ \Carbon\Carbon::parse($entry['submitted_at'])->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                        @elseif(!empty($entry['checked_at']))
                        <p class="text-xs text-gray-400 mt-0.5">Read {{ \Carbon\Carbon::parse($entry['checked_at'])->format('d M Y H:i') }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColour }}">{{ $statusLabel }}</span>
                        @if(!empty($entry['prepared_snapshot_path']))
                        <a href="{{ route('tenant.tfs.snapshot', [$tenant->slug, $submission->id, $i, 'type' => 'prepared']) }}" target="_blank"
                           class="text-xs font-medium text-blue-600 hover:text-blue-800">Filled form (PDF)</a>
                        @endif
                        @if(!empty($entry['pre_snapshot_path']))
                        <a href="{{ route('tenant.tfs.snapshot', [$tenant->slug, $submission->id, $i, 'type' => 'before']) }}" target="_blank"
                           class="text-xs font-medium text-blue-600 hover:text-blue-800">Evidence: before submit</a>
                        @endif
                        @if($urlStatus === 'submitted' && !empty($entry['snapshot_path']))
                        <a href="{{ route('tenant.tfs.snapshot', [$tenant->slug, $submission->id, $i]) }}" target="_blank"
                           class="text-xs font-medium text-blue-600 hover:text-blue-800">{{ !empty($entry['manual']) ? 'Evidence (uploaded)' : 'Evidence: confirmation' }}</a>
                        @endif
                    </div>
                </div>

                {{-- STEP 2: confirm the search, choose the answer, fill the form (nothing is sent) --}}
                @if(in_array($urlStatus, ['ready', 'prepared']) && count($opts))
                @php $changing = $urlStatus === 'prepared'; @endphp
                @if($changing)<details class="mt-3"><summary class="text-xs text-gray-500 cursor-pointer">Change the answer</summary><div class="mt-2">@endif
                <form method="POST" action="{{ $prepareUrl }}" data-wait class="mt-3 border border-gray-200 rounded-lg p-3 space-y-3 bg-gray-50">
                    @csrf
                    <p class="text-xs font-semibold text-gray-700">Step 2 — confirm your search and fill the form <span class="font-normal text-gray-500">(nothing is sent)</span></p>

                    <label class="flex items-start gap-2 text-xs text-gray-700">
                        <input type="checkbox" name="attest" value="1" required class="mt-0.5 rounded border-gray-300">
                        <span>I have searched our client database against the designated names in this alert, and the answer below reflects the result.</span>
                    </label>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Answer to send</label>
                        <select name="answer" required data-answer
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Choose the answer…</option>
                            @foreach($opts as $o)
                            <option value="{{ $o }}" data-safe="{{ \App\Services\TfsService::isSafeNoMatch($o) ? 1 : 0 }}">{{ $o }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">These are the exact options on the TFS form.</p>
                    </div>

                    <div data-confirm-box class="hidden border border-red-200 bg-red-50 rounded-lg p-2">
                        <p class="text-xs text-red-800 mb-1">This is not a clear “no match”, and a match answer starts an inquiry at the Ministry. Type <strong>{{ $matchPhrase }}</strong> to continue.</p>
                        <input type="text" name="confirm_text" autocomplete="off" placeholder="{{ $matchPhrase }}"
                               class="w-full border border-red-300 rounded px-2 py-1 text-sm">
                    </div>

                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 disabled:opacity-60 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                        Fill the form — nothing is sent
                    </button>
                </form>
                @if($changing)</div></details>@endif
                @endif

                {{-- STEP 3: confirm and submit (the only step that sends anything) --}}
                @if($urlStatus === 'prepared' && !empty($entry['prepared_answer']))
                @php
                    $preparedSafe = \App\Services\TfsService::isSafeNoMatch($entry['prepared_answer']);
                    $preparedAt   = \Carbon\Carbon::parse($entry['prepared_at']);
                @endphp
                <form method="POST" action="{{ $finalizeUrl }}" data-wait class="mt-3 border-2 {{ $preparedSafe ? 'border-blue-300 bg-blue-50' : 'border-red-400 bg-red-50' }} rounded-lg p-4 space-y-3">
                    @csrf
                    <p class="text-xs font-semibold text-gray-800">Step 3 — confirm and submit</p>
                    <p class="text-sm text-gray-800">The form is filled with:</p>
                    <p class="text-base font-bold {{ $preparedSafe ? 'text-blue-900' : 'text-red-700' }}">“{{ $entry['prepared_answer'] }}”</p>
                    <p class="text-xs text-gray-500">
                        Prepared {{ $preparedAt->diffForHumans() }}. Open the filled-form PDF above and check it first. Valid for 60 minutes.
                    </p>

                    @unless($preparedSafe)
                    <div class="border border-red-300 bg-white rounded-lg p-2">
                        <p class="text-xs text-red-800 mb-1">Not a clear “no match”: this raises a match with the Ministry. Type <strong>{{ $matchPhrase }}</strong> to submit.</p>
                        <input type="text" name="confirm_text" autocomplete="off" placeholder="{{ $matchPhrase }}" required
                               class="w-full border border-red-300 rounded px-2 py-1 text-sm">
                    </div>
                    @endunless

                    <label class="flex items-start gap-2 text-xs text-gray-700">
                        <input type="checkbox" name="confirm" value="1" required class="mt-0.5 rounded border-gray-300">
                        <span>I have checked the filled form. I understand this answer is sent to the Executive Office for Control and Non-Proliferation and cannot be withdrawn.</span>
                    </label>

                    <button type="submit" class="bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                        Submit this answer to the Ministry
                    </button>
                </form>
                @endif

                @if(in_array($urlStatus, ['ready', 'prepared']) && !count($opts))
                <p class="mt-3 text-xs text-red-600">No answer options were detected on the form, so this link can't be answered from here. Open it in your browser.</p>
                @endif

                {{-- Submitted it yourself? Keep the record here --}}
                @if($urlStatus !== 'submitted')
                <details class="mt-3">
                    <summary class="text-xs text-gray-500 cursor-pointer">I submitted this myself — record it</summary>
                    <form method="POST" action="{{ $recordUrl }}" enctype="multipart/form-data" class="mt-2 border border-gray-200 rounded-lg p-3 space-y-2 bg-gray-50">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Answer you gave</label>
                                <input type="text" name="answer" required maxlength="300" placeholder="e.g. No match was identified"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Date submitted</label>
                                <input type="date" name="submitted_on" required max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Screenshot / PDF of the confirmation <span class="text-gray-400">(optional, max 5 MB)</span></label>
                            <input type="file" name="evidence" accept=".pdf,.png,.jpg,.jpeg" class="text-xs text-gray-600">
                        </div>
                        <input type="text" name="note" maxlength="1000" placeholder="Note (optional)" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm bg-white">
                        <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Save record</button>
                    </form>
                </details>
                @endif
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">No URLs recorded.</div>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">

        {{-- The record for this alert --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-2">Record</h2>
            @if($submission->alert_url)
            <p class="text-xs text-gray-500">Alert / notice</p>
            <a href="{{ $submission->alert_url }}" target="_blank" rel="noopener" class="block mb-2 text-xs text-blue-600 hover:underline truncate">{{ $submission->alert_url }}</a>
            @endif
            <p class="text-xs text-gray-500">Notes on the search</p>
            <p class="text-xs text-gray-700 whitespace-pre-line">{{ $searchNotes ?: 'None recorded.' }}</p>
        </div>

        {{-- Step 1: read the links --}}
        @if(count($runIndices))
        <div class="bg-white rounded-xl border border-gray-200 p-5" id="tfs-runner">
            <h2 class="font-semibold text-gray-800 mb-1">Step 1 — read the links</h2>
            <p class="text-xs text-gray-500 mb-4">
                Opens each link to confirm it is still open and to read the answer options.
                <strong>This only reads. It never answers or submits.</strong>
            </p>
            <button type="button" id="tfs-run-btn"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                Read {{ count($runIndices) }} link(s)
            </button>
            <p id="tfs-run-note" class="mt-2 text-xs text-gray-400 text-center">Takes around 15 seconds per link. Keep this page open.</p>
        </div>
        @endif

        {{-- Meta --}}
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-xs text-gray-500 space-y-1.5">
            <div class="flex justify-between"><span>Created</span><span>{{ $submission->created_at->format('d M Y') }}</span></div>
            @if($submission->creator)
            <div class="flex justify-between"><span>By</span><span>{{ $submission->creator->name }}</span></div>
            @endif
            <div class="flex justify-between"><span>Links</span><span>{{ $submission->totalUrls() }}</span></div>
            <div class="flex justify-between"><span>Submitted</span><span>{{ $submission->submittedCount() }} / {{ $submission->totalUrls() }}</span></div>
            @if($submission->submitted_at)
            <div class="flex justify-between"><span>Last submitted</span><span>{{ $submission->submitted_at->format('d M Y H:i') }}</span></div>
            @endif
        </div>
    </div>

</div>

<script>
// Forms that take a while: stop double clicks and say what is happening.
document.querySelectorAll('form[data-wait]').forEach(function (form) {
    form.addEventListener('submit', function () {
        const btn = form.querySelector('button[type=submit]');
        if (btn) { btn.disabled = true; btn.textContent = 'Working… about 30 seconds. Please keep this page open.'; }
    });
});

// Show the typed-confirmation box whenever the chosen answer isn't a clear "no match".
document.querySelectorAll('select[data-answer]').forEach(function (sel) {
    const box = sel.closest('form').querySelector('[data-confirm-box]');
    const input = box ? box.querySelector('input') : null;
    const sync = function () {
        const opt = sel.selectedOptions[0];
        const risky = opt && opt.value !== '' && opt.dataset.safe !== '1';
        if (!box) return;
        box.classList.toggle('hidden', !risky);
        if (input) input.required = !!risky;
    };
    sel.addEventListener('change', sync);
    sync();
});
</script>

@if(count($runIndices))
<script>
// Step 1 only: read each link. Nothing here can answer a survey.
(function () {
    const indices   = {!! json_encode($runIndices) !!};
    const urlBase   = {!! json_encode(route('tenant.tfs.process', [$tenant->slug, $submission->id, 0])) !!};
    const csrf      = {!! json_encode(csrf_token()) !!};
    const autoStart = {!! json_encode(request()->boolean('auto')) !!};
    const btn  = document.getElementById('tfs-run-btn');
    const note = document.getElementById('tfs-run-note');

    const urlFor = idx => urlBase.replace(/\/0$/, '/' + idx);
    const live   = (idx, text) => {
        const el = document.querySelector('[data-live="' + idx + '"]');
        if (!el) return;
        el.textContent = text;
        el.classList.remove('hidden');
    };

    async function run() {
        btn.disabled = true;
        for (const idx of indices) {
            try {
                live(idx, 'Opening the link and reading the answer options…');
                const res = await fetch(urlFor(idx), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({}),
                });
                if (!res.ok) throw new Error('Server returned ' + res.status + (res.status === 504 ? ' (timed out)' : ''));
                const r = await res.json();
                live(idx, r.message || r.status);
            } catch (e) {
                live(idx, 'Stopped: ' + e.message + '. Reload to see where it got to.');
            }
        }
        note.textContent = 'Done — refreshing…';
        setTimeout(() => { window.location.href = window.location.pathname; }, 1000);
    }

    btn.addEventListener('click', run);
    if (autoStart) run();
})();
</script>
@endif

@endsection
