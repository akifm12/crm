@extends('layouts.tenant')
@section('page-title', $module)
@section('content')
<div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
    </div>
    <p class="text-xl font-bold text-gray-300">{{ $module }}</p>
    <p class="text-sm text-gray-400 mt-2">This module is coming soon.</p>
</div>
@endsection
