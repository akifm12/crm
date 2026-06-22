@extends('layouts.tenant')
@section('title', 'Expiry Report — ' . $tenant->name)
@section('page-title', 'Expiry Report')
@section('page-subtitle', 'All active clients with missing or expiring documents (next ' . $window . ' days)')

@section('content')

@php
$badge = function($date, bool $missingIsAlert = true) {
    if (!$date) return $missingIsAlert
        ? ['bg-orange-100 text-orange-700', 'Missing']
        : ['bg-gray-100 text-gray-400', '—'];
    $d = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
    if ($d->isPast())                return ['bg-red-100 text-red-700',     'Expired · ' . $d->format('d M Y')];
    if ($d->diffInDays(now()) <= 30) return ['bg-amber-100 text-amber-700', $d->format('d M Y') . ' · ' . $d->diffInDays(now()) . 'd'];
    return                                  ['bg-blue-100 text-blue-700',   $d->format('d M Y')];
};

$tabs = [
    'licence'  => ['label' => 'Trade licences',       'icon' => '📋'],
    'ejari'    => ['label' => 'Ejari',                'icon' => '🏢'],
    'eid'      => ['label' => 'Shareholder EIDs',     'icon' => '🪪'],
    'passport' => ['label' => 'Shareholder passports','icon' => '📘'],
    'docs'     => ['label' => 'Client documents',     'icon' => '📄'],
];
@endphp

{{-- Tab bar --}}
<div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5 overflow-x-auto">
    @foreach($tabs as $key => $t)
    <a href="{{ route('tenant.expiry', $tenant->slug) }}?tab={{ $key }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap flex items-center gap-1.5
              {{ $tab === $key ? 'bg-red-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
        {{ $t['icon'] }} {{ $t['label'] }}
        @if($counts[$key] > 0)
        <span class="text-xs font-bold px-1.5 py-0.5 rounded-full
                     {{ $tab === $key ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700' }}">
            {{ $counts[$key] }}
        </span>
        @endif
    </a>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

    @if($data->isEmpty())
    <div class="py-16 text-center">
        <span class="text-4xl">✓</span>
        <p class="text-gray-500 font-medium mt-3">All clear — nothing to action in this category.</p>
    </div>

    @elseif(in_array($tab, ['licence', 'ejari']))
    {{-- Client-level table --}}
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                    {{ $tab === 'licence' ? 'Trade licence expiry' : 'Ejari expiry' }}
                </th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $client)
            @php
                $expiry = $tab === 'licence' ? $client->trade_license_expiry : $client->ejari_expiry;
                [$cls, $label] = $badge($expiry, true);
            @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $client->displayName() }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ ucfirst(str_replace('_', ' ', $client->client_type)) }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-1.5 py-0.5 rounded {{ $client->statusBadgeColor() }}">{{ ucfirst($client->status) }}</span>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cls }}">{{ $label }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
                       class="text-xs text-blue-600 hover:underline">View client →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif(in_array($tab, ['eid', 'passport']))
    {{-- Shareholder-level table --}}
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Shareholder</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                    {{ $tab === 'eid' ? 'EID expiry' : 'Passport expiry' }}
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                    {{ $tab === 'eid' ? 'EID number' : 'Passport number' }}
                </th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $sh)
            @php
                $expiry = $tab === 'eid' ? $sh->eid_expiry : $sh->passport_expiry;
                $docNo  = $tab === 'eid' ? $sh->eid_number : $sh->passport_number;
                [$cls, $label] = $badge($expiry, $tab === 'eid');
            @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $sh->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $sh->client?->displayName() ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cls }}">{{ $label }}</span>
                </td>
                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $docNo ?? '—' }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenant.clients.show', [$tenant->slug, $sh->bullion_client_id]) }}"
                       class="text-xs text-blue-600 hover:underline">View client →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @elseif($tab === 'docs')
    {{-- Document-level table --}}
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Document</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Expiry</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($data as $doc)
            @php [$cls, $label] = $badge($doc->expiry_date, false); @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $doc->client?->displayName() ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $doc->document_label }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cls }}">{{ $label }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenant.clients.show', [$tenant->slug, $doc->bullion_client_id]) }}"
                       class="text-xs text-blue-600 hover:underline">View client →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</div>

<p class="text-xs text-gray-400 mt-3">Showing all active/pending clients with issues in the next {{ $window }} days. Click any row to go to the client profile.</p>

@endsection
