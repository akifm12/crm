@extends('layouts.tenant')
@section('title', 'New TFS Submission — ' . $tenant->name)
@section('page-title', 'New TFS Submission')
@section('page-subtitle', 'Paste your unique survey link')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('tenant.tfs.store', $tenant->slug) }}" method="POST" class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
        @csrf
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
            Search your client list for the names in the alert <strong>before</strong> you continue, and take a screenshot of that search.
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your unique survey link <span class="text-red-500">*</span></label>
            <input type="url" name="survey_url" value="{{ old('survey_url') }}" required placeholder="https://..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('survey_url') border-red-400 @enderror">
            @error('survey_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-xs font-normal text-gray-400">(optional)</span></label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. the email subject"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Open survey</button>
    </form>
</div>
@endsection
