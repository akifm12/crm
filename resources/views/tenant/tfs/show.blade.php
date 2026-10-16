@extends('layouts.tenant')
@section('title', Str::limit($submission->title, 60) . ' — TFS')
@section('page-title', 'TFS Submission')
@section('page-subtitle', Str::limit($submission->title, 80))

@section('content')
@php
    $url = $submission->survey_url ?: ($submission->tfs_urls[0]['url'] ?? null);
    $slots = [
        'survey' => ['1. Screenshot of the submission', 'The "Survey completed successfully" page after you submit.', $submission->survey_evidence_path],
        'search' => ['2. Screenshot of your client search', 'Your search of your client list showing the names were not found.', $submission->search_evidence_path],
    ];
@endphp

@if(session('success'))<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5 text-sm text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5 text-sm text-red-800">{{ $errors->first() }}</div>@endif

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between text-sm">
            <span class="font-medium text-gray-700">Survey</span>
            @if($url)<a href="{{ $url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline text-xs">Page not showing? Open in a new tab →</a>@endif
        </div>
        @if($url)
        <iframe src="{{ $url }}" class="w-full border-0" style="height:720px" referrerpolicy="no-referrer"></iframe>
        @else
        <p class="p-6 text-sm text-gray-500">No survey link on this record.</p>
        @endif
    </div>

    <div class="space-y-4">
        @foreach($slots as $type => [$label, $hint, $path])
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
            <p class="text-xs text-gray-500 mb-3">{{ $hint }}</p>
            @if($path)
            <a href="{{ route('tenant.tfs.file', [$tenant->slug, $submission->id, $type]) }}" target="_blank" class="text-xs text-green-700 font-medium hover:underline">✓ Uploaded — view</a>
            @endif
            <form action="{{ route('tenant.tfs.upload', [$tenant->slug, $submission->id, $type]) }}" method="POST" enctype="multipart/form-data" class="mt-2 space-y-2">
                @csrf
                <input type="file" name="file" required accept=".png,.jpg,.jpeg,.pdf" class="block w-full text-xs text-gray-600">
                <button class="bg-gray-800 hover:bg-gray-900 text-white text-xs px-3 py-1.5 rounded-lg">{{ $path ? 'Replace' : 'Upload' }}</button>
            </form>
        </div>
        @endforeach
        <p class="text-xs text-gray-400">PNG, JPG or PDF, up to 8 MB. The record is complete once both are uploaded.</p>
    </div>
</div>
@endsection
