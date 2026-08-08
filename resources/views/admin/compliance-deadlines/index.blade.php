@extends('layouts.admin')
@section('title', 'Compliance Calendar — Content Manager')
@section('page-title', 'Compliance Calendar')
@section('page-subtitle', 'Manage the public compliance-calendar entries shown on bluearrow.ae')

@section('content')
<div x-data="{ showForm: false }">

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-gray-700">{{ $deadlines->count() }} deadline(s)</h2>
        <button @click="showForm = !showForm" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New deadline
        </button>
    </div>

    <div x-show="showForm" x-cloak class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <form method="POST" action="{{ route('content.deadlines.store') }}" class="grid sm:grid-cols-2 gap-4">
            @csrf
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                <input type="text" name="title" required class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sector (blank = all)</label>
                <select name="sector" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">All sectors</option>
                    @foreach ($sectors as $slug => $label)
                        <option value="{{ $slug }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                <select name="category" class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach ($categories as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Recurrence</label>
                <select name="recurrence" class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach ($recurrences as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Next due date (optional)</label>
                <input type="date" name="next_due_date" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Authority (optional)</label>
                <input type="text" name="authority" placeholder="e.g. UAE FIU" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save deadline</button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($deadlines as $d)
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900 text-sm">{{ $d->title }}</span>
                        <span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded {{ $d->categoryBadgeColor() }}">{{ $d->category }}</span>
                        @if ($d->sector)<span class="text-xs text-gray-400">{{ $sectors[$d->sector] ?? $d->sector }}</span>@endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $d->description }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ ucfirst(str_replace('_',' ',$d->recurrence)) }} @if($d->next_due_date) · due {{ $d->next_due_date->format('d M Y') }} @endif</p>
                </div>
                <form method="POST" action="{{ route('content.deadlines.destroy', $d) }}" onsubmit="return confirm('Remove this deadline?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 whitespace-nowrap">Remove</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-400">No deadlines yet.</p>
        @endforelse
    </div>
</div>
@endsection
