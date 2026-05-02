@extends('layouts.tenant')
@section('title', 'goAML Reports — ' . $tenant->name)
@section('page-title', 'goAML Reports')
@section('page-subtitle', 'DPMSR, STR and SAR filings')

@section('content')

@if(!$config || !$config->isComplete())
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-800">goAML configuration incomplete</p>
            <p class="text-xs text-amber-600">Configure your MLRO and entity details before filing reports.</p>
        </div>
    </div>
    <a href="{{ route('tenant.goaml.settings', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition flex-shrink-0">
        Configure now →
    </a>
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-4 gap-4 mb-5">
    @foreach([
        ['Total reports', $stats['total'],  'text-gray-900',   'border-gray-200'],
        ['DPMSR',         $stats['dpmsr'],  'text-blue-700',   'border-blue-200  bg-blue-50'],
        ['STR',           $stats['str'],    'text-red-700',    'border-red-200   bg-red-50'],
        ['SAR',           $stats['sar'],    'text-orange-700', 'border-orange-200 bg-orange-50'],
    ] as [$label, $count, $tc, $bc])
    <div class="rounded-xl border {{ $bc }} p-4 text-center bg-white">
        <p class="text-2xl font-bold font-mono {{ $tc }}">{{ $count }}</p>
        <p class="text-xs font-medium text-gray-400 mt-0.5">{{ $label }}</p>
    </div>
    @endforeach
</div>

{{-- Actions + filters --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    <form method="GET" class="flex flex-wrap gap-2 flex-1">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by invoice or client…"
               class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white w-56">
        <select name="type" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All types</option>
            <option value="DPMSR" {{ request('type')==='DPMSR' ? 'selected' : '' }}>DPMSR</option>
            <option value="STR"   {{ request('type')==='STR'   ? 'selected' : '' }}>STR</option>
            <option value="SAR"   {{ request('type')==='SAR'   ? 'selected' : '' }}>SAR</option>
        </select>
        <select name="client" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All clients</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>
                {{ $c->client_type === 'individual' ? $c->full_name : $c->company_name }}
            </option>
            @endforeach
        </select>
        <button type="submit" class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Filter</button>
        @if(request()->hasAny(['search','type','client']))
        <a href="{{ route('tenant.goaml', $tenant->slug) }}" class="px-3 py-2 text-sm text-gray-400 hover:text-gray-600">Clear</a>
        @endif
    </form>
    <div class="flex items-center gap-2 flex-shrink-0">
        <a href="{{ route('tenant.goaml.settings', $tenant->slug) }}"
           class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            ⚙ Config
        </a>
        @if($config && $config->isComplete())
        <a href="{{ route('tenant.goaml.create', $tenant->slug) }}"
           class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New report
        </a>
        @endif
    </div>
</div>

{{-- Reports table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Report</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Invoice</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Value</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Filed by</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($reports as $report)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $report->reportTypeBadgeColor() }}">
                        {{ $report->report_type }}
                    </span>
                </td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $report->client_name }}</td>
                <td class="px-4 py-3 hidden md:table-cell font-mono text-xs text-gray-500">{{ $report->entity_reference }}</td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    <span class="text-sm font-semibold text-gray-800">{{ $report->currency_code }} {{ number_format($report->disposed_value, 0) }}</span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-400">{{ $report->registration_date->format('d M Y') }}</td>
                <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-400">{{ $report->author?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('tenant.goaml.download', [$tenant->slug, $report->id]) }}"
                           class="text-sm font-medium text-blue-600 hover:underline">
                            ↓ XML
                        </a>
                        <form method="POST" action="{{ route('tenant.goaml.destroy', [$tenant->slug, $report->id]) }}"
                              onsubmit="return confirm('Delete this report?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-16 text-center">
                    <p class="text-gray-400 text-sm">No reports filed yet.</p>
                    @if($config && $config->isComplete())
                    <a href="{{ route('tenant.goaml.create', $tenant->slug) }}" class="text-sm text-blue-600 hover:underline mt-1 block">File your first report</a>
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
