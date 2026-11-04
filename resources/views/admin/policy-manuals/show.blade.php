@extends('layouts.admin')
@section('title', $title . ' — Policy Manuals')
@section('page-title', $title)

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('policy-manuals.index') }}" class="text-xs text-blue-600 hover:underline">&larr; All policy manuals</a>
    <div class="bg-white rounded-xl border border-gray-200 p-8 mt-3 text-center">
        <p class="text-sm text-gray-500">{{ $blurb }}</p>
        <p class="text-xs text-gray-400 mt-4">This manual hasn't been written yet.</p>
    </div>
</div>
@endsection
