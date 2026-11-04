@extends('layouts.admin')
@section('title', 'Regulatory updates')
@section('page-title', 'Regulatory updates')

@section('content')
<div class="max-w-5xl">
    <a href="{{ route('policy-manuals.index') }}" class="text-xs text-blue-600 hover:underline">&larr; All policy manuals</a>

    @if(session('success'))
    <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <p class="text-sm text-gray-500 mt-3">A daily check on the administrator's PC compares the official MoET, EOCN, National Committee and FATF pages with the last known state. Anything that changed appears here. Nothing in a manual changes until you review the item and ask for the update.</p>

    <div class="mt-4 rounded-xl border p-4 text-sm {{ $stale ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-white border-gray-200 text-gray-700' }}">
        @if(!$run)
            The daily check has not reported yet.
        @else
            Last check: <strong>{{ $run->ran_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}</strong>
            @if($stale) <span class="font-medium">(more than 48 hours ago: the PC may be off or the task has stopped)</span>@endif
            @if(!$run->ok) <span class="text-red-700 font-medium">One or more sources could not be read.</span>@endif
            <div class="mt-2 grid sm:grid-cols-2 gap-x-6 gap-y-1 text-xs">
                @foreach($run->sources ?? [] as $src)
                <div class="flex gap-2">
                    <span class="{{ !empty($src['ok']) ? 'text-green-700' : 'text-red-700' }}">{{ !empty($src['ok']) ? 'OK' : 'FAILED' }}</span>
                    <span>{{ $src['title'] ?? $src['key'] }}@if(!empty($src['note'])) <span class="text-gray-400">({{ $src['note'] }})</span>@endif</span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="flex gap-2 mt-5 text-xs">
        @foreach(['open' => 'Open ('.$counts['open'].')', 'applied' => 'Applied', 'dismissed' => 'Dismissed', 'all' => 'All'] as $k => $label)
        <a href="{{ route('policy-manuals.updates', ['status' => $k]) }}"
           class="px-3 py-1.5 rounded-lg border {{ $status === $k ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="mt-4 space-y-3">
        @forelse($updates as $u)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800">{{ $u->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $u->detected_at->format('j M Y') }} &middot; {{ str_replace('_', ' ', $u->change_type) }} &middot; {{ $u->source_key }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full whitespace-nowrap {{ ['new' => 'bg-amber-100 text-amber-800', 'reviewed' => 'bg-blue-100 text-blue-800', 'applied' => 'bg-green-100 text-green-800', 'dismissed' => 'bg-gray-100 text-gray-600'][$u->status] ?? 'bg-gray-100' }}">{{ ucfirst($u->status) }}</span>
            </div>
            @if($u->summary)<p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $u->summary }}</p>@endif
            @if($u->affected_sections)<p class="text-xs text-gray-500 mt-2"><strong>Likely affects:</strong> {{ $u->affected_sections }}</p>@endif
            @if($u->url)<p class="text-xs mt-1"><a href="{{ $u->url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline break-all">Open the official source</a></p>@endif

            <form method="POST" action="{{ route('policy-manuals.updates.update', $u) }}" class="mt-3 flex flex-wrap items-center gap-2">
                @csrf @method('PATCH')
                <input type="text" name="reviewer_note" value="{{ $u->reviewer_note }}" placeholder="Note (optional)" maxlength="2000"
                       class="flex-1 min-w-[12rem] px-3 py-1.5 text-xs border border-gray-200 rounded-lg">
                @foreach(['reviewed' => 'Mark reviewed', 'applied' => 'Mark applied', 'dismissed' => 'Dismiss', 'new' => 'Reopen'] as $val => $label)
                @if($u->status !== $val)
                <button type="submit" name="status" value="{{ $val }}" class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">{{ $label }}</button>
                @endif
                @endforeach
            </form>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-sm text-gray-500">Nothing to review.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $updates->links() }}</div>
</div>
@endsection
