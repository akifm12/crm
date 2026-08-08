@extends('layouts.admin')
@section('title', 'Resources — Content Manager')
@section('page-title', 'Resources')
@section('page-subtitle', 'Manage downloadable PDF resources shown on bluearrow.ae')

@section('content')
<div x-data="{ showForm: false }">

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-gray-700">{{ $resources->count() }} resource(s)</h2>
        <button @click="showForm = !showForm" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Upload resource
        </button>
    </div>

    <div x-show="showForm" x-cloak class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <form method="POST" action="{{ route('content.resources.store') }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-4">
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
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">PDF file (max 10MB)</label>
                <input type="file" name="file" accept="application/pdf" required class="w-full text-sm">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Upload</button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($resources as $r)
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900 text-sm">{{ $r->title }}</span>
                        <span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded bg-blue-50 text-blue-700">{{ $r->category }}</span>
                        @unless ($r->is_published)
                            <span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-500">Unpublished</span>
                        @endunless
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $r->description }}</p>
                    <a href="{{ $r->downloadUrl() }}" target="_blank" class="mt-1 inline-block text-xs text-blue-600 hover:text-blue-800">View PDF ({{ $r->fileSizeHuman() }})</a>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <form method="POST" action="{{ route('content.resources.toggle', $r) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 whitespace-nowrap">{{ $r->is_published ? 'Unpublish' : 'Publish' }}</button>
                    </form>
                    <form method="POST" action="{{ route('content.resources.destroy', $r) }}" onsubmit="return confirm('Remove this resource?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 whitespace-nowrap">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">No resources yet.</p>
        @endforelse
    </div>
</div>
@endsection
