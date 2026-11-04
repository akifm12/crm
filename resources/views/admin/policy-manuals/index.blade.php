@extends('layouts.admin')
@section('title', 'Policy Manuals')
@section('page-title', 'Policy Manuals')

@section('content')
<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($manuals as $slug => [$title, $blurb])
    <a href="{{ route('policy-manuals.show', $slug) }}" class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-400 transition">
        <p class="text-sm font-semibold text-gray-800">{{ $title }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $blurb }}</p>
        <p class="text-xs text-gray-400 mt-3">Not written yet</p>
    </a>
    @endforeach
</div>
@endsection
