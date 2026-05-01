@extends('layouts.admin')
@section('title', 'CRM — Clients')
@section('page-title', 'CRM — Clients')

@section('content')

{{-- Pipeline summary bar --}}
<div class="grid grid-cols-7 gap-2 mb-6">
    @foreach([
        'lead'          => ['Lead',          'bg-gray-100 text-gray-600 border-gray-200'],
        'qualified'     => ['Qualified',     'bg-blue-50 text-blue-700 border-blue-200'],
        'proposal_sent' => ['Proposal Sent', 'bg-purple-50 text-purple-700 border-purple-200'],
        'negotiation'   => ['Negotiation',   'bg-amber-50 text-amber-700 border-amber-200'],
        'onboarding'    => ['Onboarding',    'bg-orange-50 text-orange-700 border-orange-200'],
        'active'        => ['Active',        'bg-green-50 text-green-700 border-green-200'],
        'inactive'      => ['Inactive',      'bg-red-50 text-red-700 border-red-200'],
    ] as $key => [$label, $cls])
    <a href="{{ request()->fullUrlWithQuery(['stage' => $key]) }}"
       class="border rounded-xl p-3 text-center cursor-pointer hover:shadow-sm transition {{ $cls }} {{ request('stage') === $key ? 'ring-2 ring-offset-1 ring-blue-400' : '' }}">
        <p class="text-xl font-bold">{{ $pipeline[$key] ?? 0 }}</p>
        <p class="text-xs font-medium mt-0.5">{{ $label }}</p>
    </a>
    @endforeach
</div>

{{-- Toolbar --}}
<div class="flex flex-wrap gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-1">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search clients..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        </div>
        <select name="stage" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All stages</option>
            @foreach(['lead'=>'Lead','qualified'=>'Qualified','proposal_sent'=>'Proposal Sent','negotiation'=>'Negotiation','onboarding'=>'Onboarding','active'=>'Active','inactive'=>'Inactive'] as $v=>$l)
            <option value="{{ $v }}" {{ request('stage')===$v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">Filter</button>
        @if(request()->hasAny(['search','stage','status']))
        <a href="{{ route('crm.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>
    <a href="{{ route('crm.create') }}"
       class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New client
    </a>
</div>

{{-- Client table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Company</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Stage</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Licence expiry</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Portal</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden xl:table-cell">Tasks</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden xl:table-cell">Assigned to</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($clients as $client)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 flex-shrink-0">
                            {{ strtoupper(substr($client->company_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $client->company_name }}</p>
                            <p class="text-xs text-gray-400">{{ $client->email ?? $client->telephone ?? '—' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $client->stageBadgeColor() }}">
                        {{ $client->stageLabel() }}
                    </span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell">
                    @if($client->license_expiry)
                        <span class="text-xs {{ $client->isLicenseExpired() ? 'text-red-600 font-semibold' : ($client->isLicenseExpiringSoon() ? 'text-orange-600' : 'text-gray-500') }}">
                            {{ $client->license_expiry->format('d M Y') }}
                            @if($client->isLicenseExpired()) <span class="block">Expired</span>
                            @elseif($client->isLicenseExpiringSoon()) <span class="block">Expiring soon</span>
                            @endif
                        </span>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 hidden lg:table-cell">
                    @if($client->tenant)
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
                            {{ ucfirst($client->portal_type) }}
                        </span>
                    @else
                        <span class="text-gray-300 text-xs">None</span>
                    @endif
                </td>
                <td class="px-5 py-3 hidden xl:table-cell">
                    @php $pending = $client->tasks->whereIn('status', ['pending','in_progress'])->count(); @endphp
                    @if($pending > 0)
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-semibold">{{ $pending }} open</span>
                    @else
                        <span class="text-gray-300 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 hidden xl:table-cell text-xs text-gray-500">
                    {{ $client->assignee?->name ?? '—' }}
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('crm.show', $client->id) }}" class="text-sm text-blue-600 hover:underline font-medium">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center">
                    <p class="text-gray-400 mb-3">No clients yet.</p>
                    <a href="{{ route('crm.create') }}" class="text-sm text-blue-600 hover:underline font-medium">Add your first client</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($clients->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $clients->links() }}</div>
    @endif
</div>

@endsection
