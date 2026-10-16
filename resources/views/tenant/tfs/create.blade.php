@extends('layouts.tenant')
@section('title', 'New TFS Submission — ' . $tenant->name)
@section('page-title', 'New TFS Submission')
@section('page-subtitle', 'Log a UAEIEC TFS alert and capture submission evidence')

@section('content')

<div class="max-w-2xl">
    <form action="{{ route('tenant.tfs.store', $tenant->slug) }}" method="POST" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alert title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       placeholder="e.g. Amending Four Entries ISIL Daesh and Al-Qaida — August 2026"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-400">Copy the email subject line here for reference.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Link to the alert / notice <span class="text-xs font-normal text-gray-400">(optional, for your records)</span></label>
                <input type="url" name="alert_url" value="{{ old('alert_url') }}" placeholder="https://..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('alert_url') border-red-400 @enderror">
                @error('alert_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes on your search <span class="text-xs font-normal text-gray-400">(optional)</span></label>
                <p class="text-xs text-gray-500 mb-2">For your records: which designated names, which client lists or systems you searched, and who did it.</p>
                <textarea name="search_notes" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('search_notes') border-red-400 @enderror">{{ old('search_notes') }}</textarea>
                @error('search_notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">TFS survey links <span class="text-red-500">*</span></label>
                <p class="text-xs text-gray-500 mb-2">
                    Paste the "FOLLOW TFS INSTRUCTIONS" links — one per line.<br>
                    Optional label prefix: <code class="bg-gray-100 px-1 rounded">review@company.ae: https://www.uaeiec.gov.ae/...</code>
                </p>
                <textarea name="tfs_links" rows="6" required
                          placeholder="review@company.ae: https://www.uaeiec.gov.ae//en-us/SurveyPages/...&#10;info@company.ae: https://www.uaeiec.gov.ae//en-us/SurveyPages/..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tfs_links') border-red-400 @enderror">{{ old('tfs_links') }}</textarea>
                @error('tfs_links')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                Save &amp; read the links
            </button>
            <p class="text-xs text-gray-400">Only reads the links. Nothing is sent to the Ministry until you confirm each answer yourself.</p>
            <a href="{{ route('tenant.tfs.index', $tenant->slug) }}"
               class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>
</div>

@endsection
