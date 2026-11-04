@extends('layouts.tenant')
@section('title', 'Client Statement — ' . $tenant->name)
@section('page-title', 'Client Statement')
@section('page-subtitle', 'Individual client account statement')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
    <p class="text-lg font-medium mb-2">Client Statement</p>
    <p class="text-sm">Client statements are generated from the Bullion module.</p>
</div>
@endsection
