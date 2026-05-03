@extends('layouts.admin')
@section('title', 'Tenants')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Tenant portals</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage compliance portal clients</p>
    </div>
    <a href="{{ route('kyc.tenants.create') }}"
       class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New tenant
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sector</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Portal URL</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Clients</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tenants as $tenant)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <p class="font-semibold text-gray-800">{{ $tenant->name }}</p>
                    <p class="text-xs text-gray-400">{{ $tenant->contact_email }}</p>
                </td>
                <td class="px-4 py-3">
                    @php
                    $sectorColors = ['gold'=>'bg-amber-100 text-amber-700','real_estate'=>'bg-blue-100 text-blue-700','company_services'=>'bg-purple-100 text-purple-700','accounting'=>'bg-green-100 text-green-700','other'=>'bg-gray-100 text-gray-600'];
                    @endphp
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $sectorColors[$tenant->business_type] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $sectors[$tenant->business_type] ?? $tenant->business_type }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ $tenant->portalUrl() }}" target="_blank" class="text-xs text-blue-600 hover:underline font-mono">
                        {{ $tenant->portalUrl() }}
                    </a>
                </td>
                <td class="px-4 py-3 font-mono text-gray-700">{{ $tenant->clients_count ?? 0 }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('kyc.tenants.edit', $tenant->id) }}"
                           class="text-sm text-blue-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('kyc.tenants.toggle', $tenant->id) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs {{ $tenant->is_active ? 'text-red-400 hover:text-red-600' : 'text-green-500 hover:text-green-700' }}">
                                {{ $tenant->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No tenants yet. Create your first portal client.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
