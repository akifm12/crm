@extends('layouts.tenant')
@section('title', 'Client Balances — ' . $tenant->name)
@section('page-title', 'Client Balances')
@section('page-subtitle', 'Outstanding client account balances')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
    <p class="text-lg font-medium mb-2">Client Balances</p>
    <p class="text-sm">Client balance data comes from the Bullion module.</p>
</div>
@endsection
