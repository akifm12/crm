@extends('layouts.tenant')
@section('title', 'Clients — ' . $tenant->name)
@section('page-title', 'Clients')
@section('page-subtitle', 'All onboarded clients')

@section('content')

@php
$typeLabels  = array_merge(['' => 'All'], $sector['client_types'] ?? []);
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
    <form method="GET" class="flex gap-2 flex-1" id="filter-form">
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
        <select name="year" onchange="document.getElementById('filter-form').submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All years</option>
            @foreach($years as $yr)
            <option value="{{ $yr }}" {{ request('year')===$yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>

        {{-- Sort --}}
        <select name="sort" onchange="document.getElementById('filter-form').submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="newest" {{ request('sort','newest')==='newest' ? 'selected' : '' }}>Newest first</option>
            <option value="oldest" {{ request('sort')==='oldest' ? 'selected' : '' }}>Oldest first</option>
            <option value="az"     {{ request('sort')==='az' ? 'selected' : '' }}>A → Z</option>
            <option value="za"     {{ request('sort')==='za' ? 'selected' : '' }}>Z → A</option>
        </select>
        @if(request()->hasAny(['search','status','risk','year']))
        <a href="{{ route('tenant.clients.index', $tenant->slug) }}{{ request('type') ? '?type='.request('type') : '' }}"
           class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Clear</a>
        @endif
    </form>

    {{-- Total count --}}
    <p class="text-xs text-gray-400 mt-2 px-1">
        Showing {{ $clients->firstItem() }}–{{ $clients->lastItem() }} of <span class="font-semibold text-gray-600">{{ $clients->total() }}</span> clients
        @if(request('year')) · {{ request('year') }} @endif
    </p>
    <div class="flex items-center gap-2">
        {{-- Generate self-fill link --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open=!open"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                KYC link
            </button>
            <div x-show="open" x-cloak @click.away="open=false"
                 class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-lg p-4 z-10">
                <p class="text-sm font-semibold text-gray-700 mb-3">Generate self-fill KYC link</p>
                <form method="POST" action="{{ route('tenant.fill.generate', $tenant->slug) }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Client type</label>
                            <select name="client_type" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="individual">Individual</option>
                                <option value="corporate">Corporate</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Client name (optional)</label>
                            <input type="text" name="client_name" placeholder="Pre-fills their name"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Client email (sends link automatically)</label>
                            <input type="email" name="client_email" placeholder="client@example.com"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            Generate link
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <a href="{{ route('tenant.clients.create', $tenant->slug) }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add client
        </a>
    </div>
</div>

{{-- Generated link modal --}}
@if(session('fill_link'))
<div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" x-data="{ show: true }" x-show="show">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-sm font-bold text-gray-800 mb-2">KYC link generated ✓</h3>
        @if(session('email_sent'))
        <p class="text-xs text-green-600 mb-3">✓ Email sent to client</p>
        @endif
        <p class="text-xs text-gray-500 mb-2">Copy and share this link with your client:</p>
        <div class="flex gap-2 mb-2">
            <input type="text" value="{{ session('fill_link') }}" readonly id="fill-link-input"
                   class="flex-1 px-3 py-2 text-xs border border-gray-200 rounded-lg bg-gray-50 font-mono">
            <button onclick="navigator.clipboard.writeText(document.getElementById('fill-link-input').value).then(()=>this.textContent='Copied!')"
                    class="px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 whitespace-nowrap">
                Copy
            </button>
        </div>
        <p class="text-xs text-gray-400">Expires in 7 days · One-time use only</p>
        <button @click="show=false" class="mt-4 w-full py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Close</button>
    </div>
</div>
@endif
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-10">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Risk</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Screening</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Added</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($clients as $client)
            @php $serial = $clients->firstItem() + $loop->index; @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $serial }}</td>
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
                        $typeLabel = $sector['client_types'][$client->client_type] ?? ucfirst(str_replace('_',' ',$client->client_type));
                        $typeColors = ['corporate_local'=>'bg-blue-100 text-blue-700','corporate_import'=>'bg-purple-100 text-purple-700','corporate_export'=>'bg-amber-100 text-amber-700','buyer'=>'bg-blue-100 text-blue-700','seller'=>'bg-green-100 text-green-700','developer'=>'bg-purple-100 text-purple-700','landlord'=>'bg-amber-100 text-amber-700','tenant_client'=>'bg-teal-100 text-teal-700','individual'=>'bg-gray-100 text-gray-600'];
                        $typeColor = $typeColors[$client->client_type] ?? 'bg-gray-100 text-gray-500';
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
                <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-400">
                    {{ $client->created_at ? $client->created_at->format('d M Y') : '—' }}
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
                <td colspan="8" class="px-4 py-16 text-center">
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
