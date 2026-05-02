@extends('layouts.tenant')
@section('title', 'Client Documents — ' . $tenant->name)
@section('page-title', 'Client Documents')
@section('page-subtitle', 'All client KYC documents across your portfolio')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold font-mono text-gray-900">{{ $stats['total'] }}</p>
        <p class="text-xs font-medium text-gray-400 mt-0.5">Total documents</p>
    </div>
    <div class="bg-white rounded-xl border {{ $stats['expired'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200' }} p-4 text-center">
        <p class="text-2xl font-bold font-mono {{ $stats['expired'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $stats['expired'] }}</p>
        <p class="text-xs font-medium text-gray-400 mt-0.5">Expired</p>
    </div>
    <div class="bg-white rounded-xl border {{ $stats['expiring'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-gray-200' }} p-4 text-center">
        <p class="text-2xl font-bold font-mono {{ $stats['expiring'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $stats['expiring'] }}</p>
        <p class="text-xs font-medium text-gray-400 mt-0.5">Expiring within 30 days</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <select name="client"
            class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All clients</option>
        @foreach($clients as $c)
        <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>
            {{ $c->client_type === 'individual' ? $c->full_name : $c->company_name }}
        </option>
        @endforeach
    </select>
    <select name="type"
            class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All document types</option>
        @foreach(\App\Models\ClientDocument::corporateDocTypes() as $dt)
        <option value="{{ $dt['type'] }}" {{ request('type') === $dt['type'] ? 'selected' : '' }}>{{ $dt['label'] }}</option>
        @endforeach
    </select>
    <select name="status"
            class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All statuses</option>
        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
        <option value="expiring" {{ request('status') === 'expiring' ? 'selected' : '' }}>Expiring soon</option>
    </select>
    <button type="submit"
            class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Filter
    </button>
    @if(request()->hasAny(['client','type','status']))
    <a href="{{ route('tenant.docs.clients', $tenant->slug) }}"
       class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Clear</a>
    @endif
    <span class="ml-auto text-sm text-gray-400 self-center">{{ $documents->count() }} document(s)</span>
</form>

{{-- Expired/expiring alerts --}}
@if($stats['expired'] > 0 || $stats['expiring'] > 0)
<div class="mb-5 space-y-2">
    @if($stats['expired'] > 0)
    <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <strong>{{ $stats['expired'] }} expired document(s)</strong> — renewal required.
        <a href="?status=expired" class="underline ml-1">View expired</a>
    </div>
    @endif
    @if($stats['expiring'] > 0)
    <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <strong>{{ $stats['expiring'] }} document(s)</strong> expiring within 30 days.
        <a href="?status=expiring" class="underline ml-1">View expiring</a>
    </div>
    @endif
</div>
@endif

{{-- Document table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Document</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Expiry</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Uploaded</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($documents as $doc)
            @php
                $clientName = $doc->client_type === 'individual' ? $doc->full_name : $doc->company_name;
            @endphp
            <tr class="hover:bg-gray-50 transition {{ $doc->isExpired() ? 'bg-red-50/30' : ($doc->isExpiringSoon() ? 'bg-amber-50/30' : '') }}">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $doc->isExpired() ? 'bg-red-50' : 'bg-blue-50' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 {{ $doc->isExpired() ? 'text-red-400' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 truncate">{{ $doc->document_label }}</p>
                            <p class="text-xs text-gray-400">{{ $doc->file_name }} · {{ $doc->fileSizeFormatted() }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('tenant.clients.show', [$tenant->slug, $doc->bullion_client_id]) }}"
                       class="text-sm font-medium text-blue-600 hover:underline truncate block max-w-32">
                        {{ $clientName ?? '—' }}
                    </a>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <span class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @if($doc->expiry_date)
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        {{ $doc->isExpired() ? 'bg-red-100 text-red-700' : ($doc->isExpiringSoon() ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                        {{ $doc->isExpired() ? '⚠ Expired' : '' }} {{ $doc->expiry_date->format('d M Y') }}
                    </span>
                    @else
                    <span class="text-gray-300 text-xs">No expiry</span>
                    @endif
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    <span class="text-xs text-gray-400">{{ $doc->created_at->format('d M Y') }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('tenant.docs.client.download', [$tenant->slug, $doc->id]) }}"
                           class="text-sm font-medium text-blue-600 hover:underline">Download</a>
                        <form method="POST" action="{{ route('tenant.docs.client.delete', [$tenant->slug, $doc->id]) }}"
                              onsubmit="return confirm('Delete this document?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-16 text-center">
                    <p class="text-gray-400 text-sm">No client documents found.</p>
                    <p class="text-xs text-gray-300 mt-1">Upload documents from individual client profiles.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
