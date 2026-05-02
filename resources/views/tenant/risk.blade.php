@extends('layouts.tenant')
@section('title', 'Risk Assessment — ' . $tenant->name)
@section('page-title', 'Risk Assessment')
@section('page-subtitle', 'Client risk ratings and CDD status')

@section('content')

{{-- Stats row --}}
<div class="grid grid-cols-3 md:grid-cols-6 gap-3 mb-5">
    @foreach([
        ['High risk',    $stats['high'],       'bg-red-50 border-red-200',    'text-red-700'],
        ['Medium risk',  $stats['medium'],      'bg-amber-50 border-amber-200','text-amber-700'],
        ['Low risk',     $stats['low'],         'bg-green-50 border-green-200','text-green-700'],
        ['Not rated',    $stats['unrated'],     'bg-gray-50 border-gray-200',  'text-gray-500'],
        ['Review due',   $stats['review_due'],  'bg-orange-50 border-orange-200','text-orange-700'],
        ['EDD',          $stats['edd'],         'bg-purple-50 border-purple-200','text-purple-700'],
    ] as [$label, $count, $bg, $tc])
    <div class="rounded-xl border {{ $bg }} p-3 text-center">
        <p class="text-2xl font-bold font-mono {{ $tc }}">{{ $count }}</p>
        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</p>
    </div>
    @endforeach
</div>

{{-- Client risk table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Client risk register</h3>
        <span class="text-xs text-gray-400">{{ $clients->count() }} clients</span>
    </div>
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Risk</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">CDD</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Next review</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Last assessed</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($clients as $client)
            @php
                $typeColor = ['corporate_local'=>'bg-blue-100 text-blue-700','corporate_import'=>'bg-purple-100 text-purple-700','corporate_export'=>'bg-amber-100 text-amber-700','individual'=>'bg-gray-100 text-gray-600'];
                $typeLabel = ['corporate_local'=>'Local','corporate_import'=>'Import','corporate_export'=>'Export','individual'=>'Individual'];
            @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $client->risk_rating === 'high' ? 'bg-red-100 text-red-700' : ($client->risk_rating === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }} flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr($client->displayName(), 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $client->displayName() }}</p>
                            <p class="text-xs text-gray-400">{{ $client->email ?? $client->trade_license_no ?? '—' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeColor[$client->client_type] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $typeLabel[$client->client_type] ?? $client->client_type }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @if($client->risk_rating)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $client->riskBadgeColor() }}">
                        {{ ucfirst($client->risk_rating) }}
                        @if($client->risk_assessment_data)
                        · {{ $client->risk_assessment_data['overall_score'] ?? '' }}
                        @endif
                    </span>
                    @else
                    <span class="text-xs text-gray-400">Not rated</span>
                    @endif
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    <span class="text-xs font-medium {{ $client->cdd_type === 'enhanced' ? 'text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full' : 'text-gray-500' }}">
                        {{ $client->cdd_type ? ucfirst($client->cdd_type) : '—' }}
                    </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @if($client->next_review_date)
                    <span class="text-xs {{ $client->isReviewDue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                        {{ $client->next_review_date->format('d M Y') }}
                        @if($client->isReviewDue()) ⚠️ @endif
                    </span>
                    @else
                    <span class="text-xs text-gray-300">Not set</span>
                    @endif
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    @if($client->risk_assessed_at)
                    <span class="text-xs text-gray-400">{{ $client->risk_assessed_at->format('d M Y') }}</span>
                    @else
                    <span class="text-xs text-gray-300">Never</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenant.risk.assess', [$tenant->slug, $client->id]) }}"
                       class="text-sm font-medium text-blue-600 hover:underline">
                        {{ $client->risk_assessment_data ? 'Re-assess' : 'Assess' }}
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-12 text-center text-gray-400 text-sm">No clients yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
