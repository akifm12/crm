@extends('layouts.tenant')
@section('title', 'Clients — ' . $tenant->name)
@section('page-title', 'Clients')
@section('page-subtitle', 'All onboarded clients')

@section('content')

@php
$typeLabels = [
    ''                  => 'All',
    'corporate_local'   => 'Corporate — Local',
    'corporate_import'  => 'Corporate — Import',
    'corporate_export'  => 'Corporate — Export',
    'individual'        => 'Individual',
];
$currentType = request('type', '');
@endphp

{{-- Type tabs --}}
<div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5 overflow-x-auto">
    @foreach($typeLabels as $type => $label)
    <a href="{{ route('tenant.clients.index', $tenant->slug) }}{{ $type ? '?type='.$type : '' }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap
              {{ $currentType === $type ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
        {{ $label }}
        <span class="ml-1 text-xs font-mono {{ $currentType === $type ? 'opacity-80' : 'text-gray-400' }}">
            ({{ $typeCounts[$type] ?? 0 }})
        </span>
    </a>
    @endforeach
</div>

{{-- Toolbar --}}
<div class="flex flex-wrap gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-1">
        @if(request('type'))
            <input type="hidden" name="type" value="{{ request('type') }}">
        @endif
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name, licence, email..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        </div>
        <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All statuses</option>
            @foreach(['active'=>'Active','pending'=>'Pending','inactive'=>'Inactive','suspended'=>'Suspended'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')===$v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <select name="risk" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All risk levels</option>
            @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High'] as $v=>$l)
            <option value="{{ $v }}" {{ request('risk')===$v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Filter</button>
        @if(request()->hasAny(['search','status','risk']))
        <a href="{{ route('tenant.clients.index', $tenant->slug) }}{{ request('type') ? '?type='.request('type') : '' }}"
           class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Clear</a>
        @endif
    </form>
    <a href="{{ route('tenant.clients.create', $tenant->slug) }}"
       class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add client
    </a>
</div>

{{-- Client table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Risk</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Screening</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($clients as $client)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 flex-shrink-0">
                            {{ strtoupper(substr($client->displayName(), 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 truncate">{{ $client->displayName() }}</p>
                            <p class="text-xs text-gray-400 truncate">
                                {{ $client->email ?? $client->trade_license_no ?? '—' }}
                            </p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    @php
                        $typeLabel = [
                            'corporate_local'   => 'Local',
                            'corporate_import'  => 'Import',
                            'corporate_export'  => 'Export',
                            'individual'        => 'Individual',
                        ][$client->client_type] ?? $client->client_type;
                        $typeColor = [
                            'corporate_local'   => 'bg-blue-100 text-blue-700',
                            'corporate_import'  => 'bg-purple-100 text-purple-700',
                            'corporate_export'  => 'bg-amber-100 text-amber-700',
                            'individual'        => 'bg-gray-100 text-gray-600',
                        ][$client->client_type] ?? 'bg-gray-100 text-gray-500';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeColor }}">
                        {{ $typeLabel }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @if($client->risk_rating)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $client->riskBadgeColor() }}">
                        {{ ucfirst($client->risk_rating) }}
                    </span>
                    @else
                    <span class="text-gray-300 text-xs">Not rated</span>
                    @endif
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @php
                        $scrColor = ['clear'=>'bg-green-100 text-green-700','match'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700','not_screened'=>'bg-gray-100 text-gray-500'];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $scrColor[$client->screening_status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst(str_replace('_',' ',$client->screening_status)) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $client->statusBadgeColor() }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
                       class="text-sm text-blue-600 hover:underline font-medium">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-16 text-center">
                    <p class="text-gray-400 text-sm mb-3">No clients found.</p>
                    <a href="{{ route('tenant.clients.create', $tenant->slug) }}"
                       class="text-sm text-blue-600 hover:underline">Add your first client</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($clients->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">{{ $clients->appends(request()->query())->links() }}</div>
    @endif
</div>

@endsection
