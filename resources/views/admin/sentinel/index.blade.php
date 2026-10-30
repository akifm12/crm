@extends('layouts.admin')
@section('title', 'Sentinel — BlueArrow Portal')
@section('page-title', 'Sentinel')

@php
    $tabs = ['overview' => 'Overview', 'signups' => 'Sign-ups', 'clients' => 'Clients', 'users' => 'Users',
             'orders' => 'Orders', 'requests' => 'API requests', 'log' => 'Activity log'];
    $day  = fn ($d) => $d ? \Carbon\Carbon::parse($d)->timezone('Asia/Dubai')->format('j M Y') : '—';
    $ago  = fn ($d) => $d ? \Carbon\Carbon::parse($d)->diffForHumans() : 'never';
    $clientOptions = collect($clients)->sortBy('name');
@endphp

@section('content')
<div class="space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Run Sentinel AML (<a href="https://aml.bluearrow.ae" target="_blank" class="text-blue-600 hover:underline">aml.bluearrow.ae</a>): sign-ups, clients, plans, users and API keys. Every change is logged under your name.</p>
        @if ($tab === 'clients')
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="px-3 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ New client</button>
                <form x-show="open" x-cloak @click.outside="open = false" method="POST" action="{{ route('sentinel-admin.companies.store') }}"
                      class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-xl shadow-lg p-4 space-y-3 z-20">
                    @csrf
                    <input name="name" required placeholder="Client name" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                    <select name="plan" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                        @foreach ($plans as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach
                    </select>
                    <button class="w-full py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create client</button>
                </form>
            </div>
        @endif
    </div>

    {{-- Tabs --}}
    <nav class="flex flex-wrap gap-1 border-b border-gray-200">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('sentinel-admin.index', ['tab' => $key]) }}"
               class="px-4 py-2 text-sm font-medium -mb-px border-b-2 {{ $tab === $key ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if (! $configured)
        <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-800 border border-amber-200">SENTINEL_ADMIN_KEY is not set in the portal's .env.</div>
    @elseif ($data === null)
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-800 border border-red-200">Sentinel could not be reached. Refresh in a minute; if it persists, check the Sentinel containers.</div>
    @else

    {{-- Search (lists) --}}
    @if (in_array($tab, ['signups', 'clients', 'users'], true))
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input name="q" value="{{ $q }}" placeholder="Search name, email or client…" class="w-72 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @if ($tab === 'clients')
                <select name="plan" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
                    <option value="">All plans</option>
                    @foreach ($plans + ['STANDARD' => 'Standard (legacy)'] as $code => $label)
                        <option value="{{ $code }}" @selected(request('plan') === $code)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="portal" value="1" @checked(request('portal'))> Portal tenants</label>
            @endif
            <button class="px-3 py-2 text-sm font-medium bg-gray-800 text-white rounded-lg">Search</button>
        </form>
    @endif

    {{-- ── OVERVIEW ─────────────────────────────────────────────────────── --}}
    @if ($tab === 'overview')
        @php $planCount = collect($data['plans'])->pluck('n', 'plan'); @endphp
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach ([
                ['Sign-ups (30 days)', $data['signups_30d'], 'signups'],
                ['Trials', $planCount['TRIAL'] ?? 0, 'signups'],
                ['Paying clients', ($planCount['ESSENTIAL'] ?? 0) + ($planCount['COMPLETE'] ?? 0) + ($planCount['API'] ?? 0) + ($planCount['PREMIUM'] ?? 0), 'clients'],
                ['Orders awaiting payment', count($data['open_orders']), 'orders'],
                ['API key requests', $data['pending_key_requests'], 'requests'],
                ['Screenings (30 days)', $data['screenings_30d'], null],
            ] as [$label, $n, $link])
                <a @if ($link) href="{{ route('sentinel-admin.index', ['tab' => $link]) }}" @endif class="bg-white rounded-xl border border-gray-200 p-4 {{ $link ? 'hover:border-blue-300' : '' }}">
                    <p class="text-xs font-medium text-gray-500">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($n) }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-white rounded-xl border border-gray-200">
                <h2 class="px-5 py-3 text-sm font-semibold text-gray-700 border-b border-gray-100">Latest sign-ups and clients</h2>
                <ul class="divide-y divide-gray-100">
                    @foreach ($data['recent_signups'] as $c)
                        <li class="px-5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <a href="{{ route('sentinel-admin.company', $c['id']) }}" class="text-sm font-medium text-gray-900 hover:text-blue-600">{{ $c['name'] }}</a>
                                <p class="text-xs text-gray-500 truncate">{{ $c['contact'] ?? 'no users' }} · {{ $day($c['created_at']) }}</p>
                            </div>
                            @include('admin.sentinel._plan', ['plan' => $c['plan']])
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-gray-200">
                <h2 class="px-5 py-3 text-sm font-semibold text-gray-700 border-b border-gray-100">Orders awaiting payment</h2>
                @forelse ($data['open_orders'] as $o)
                    <a href="{{ route('sentinel-admin.company', $o['company_id']) }}" class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                        <span class="text-sm text-gray-900">#{{ $o['id'] }} {{ $o['legal_name'] }} <span class="text-gray-500">· {{ $plans[$o['plan']] ?? $o['plan'] }}</span></span>
                        <span class="text-sm font-medium text-gray-700">AED {{ number_format($o['total_aed'], 2) }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-500">No orders waiting for payment.</p>
                @endforelse
                <div class="px-5 py-3 border-t border-gray-100 text-xs text-gray-500">
                    Users: {{ $data['users']['active'] }} active of {{ $data['users']['total'] }} · {{ $data['users']['active_30d'] }} signed in during the last 30 days
                </div>
            </div>
        </div>
    @endif

    {{-- ── SIGN-UPS (trial users) ───────────────────────────────────────── --}}
    @if ($tab === 'signups')
        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr><th class="px-4 py-3 text-left">Person</th><th class="px-4 py-3 text-left">Trial client</th><th class="px-4 py-3 text-left">Signed up</th>
                        <th class="px-4 py-3 text-left">Screenings</th><th class="px-4 py-3 text-left">Last sign-in</th><th class="px-4 py-3 text-right">Convert to a client</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse ($data as $u)
                    <tr class="{{ $u['is_active'] ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3"><p class="font-medium text-gray-900">{{ $u['full_name'] }}</p><p class="text-xs text-gray-500">{{ $u['email'] }}</p></td>
                        <td class="px-4 py-3"><a href="{{ route('sentinel-admin.company', $u['company_id']) }}" class="text-blue-600 hover:underline">{{ $u['company_name'] }}</a></td>
                        <td class="px-4 py-3 text-gray-600">{{ $day($u['created_at']) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $u['screen_count'] }} / {{ $u['quota'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ago($u['last_login']) }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('sentinel-admin.users.convert', $u['id']) }}" class="inline-flex flex-wrap justify-end gap-2"
                                  onsubmit="return confirm('Create this client and move {{ $u['email'] }} and their screenings into it?')">
                                @csrf
                                <input name="company_name" required value="{{ $u['company_name'] === 'Trial' ? '' : $u['company_name'] }}" placeholder="Client name" class="w-48 px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                                <select name="plan" class="px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                                    @foreach ($plans as $code => $label)<option value="{{ $code }}" @selected($code === 'ESSENTIAL')>{{ $label }}</option>@endforeach
                                </select>
                                <button class="px-3 py-1.5 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">Convert</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No trial sign-ups{{ $q ? ' match “' . $q . '”' : '' }}.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500">Trial sign-ups without a company name share the “Trial” client. Converting creates their own client on the chosen plan (a year from today), makes them its MLRO and moves their screenings with them. The client isn't emailed; the team is.</p>
    @endif

    {{-- ── CLIENTS ──────────────────────────────────────────────────────── --}}
    @if ($tab === 'clients')
        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr><th class="px-4 py-3 text-left">Client</th><th class="px-4 py-3 text-left">Plan</th><th class="px-4 py-3 text-left">Data</th>
                        <th class="px-4 py-3 text-left">Users</th><th class="px-4 py-3 text-left">Screenings</th><th class="px-4 py-3 text-left">API keys</th>
                        <th class="px-4 py-3 text-left">Renewal</th><th class="px-4 py-3 text-left">Last sign-in</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse ($data as $c)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="location='{{ route('sentinel-admin.company', $c['id']) }}'">
                        <td class="px-4 py-3"><p class="font-medium text-gray-900">{{ $c['name'] }}</p><p class="text-xs text-gray-500">{{ $c['contact'] ?? 'no users' }} · since {{ $day($c['created_at']) }}</p></td>
                        <td class="px-4 py-3">@include('admin.sentinel._plan', ['plan' => $c['plan']])
                            @if ($c['last_order_status'] === 'awaiting_payment')<span class="ml-1 text-xs text-amber-600">order unpaid</span>@endif</td>
                        <td class="px-4 py-3 text-gray-600">{{ ucfirst($c['data_tier']) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $c['user_count'] }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($c['screens_used']) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $c['api_keys'] }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $day($c['expiry_date']) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ago($c['last_login']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No clients found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── USERS ────────────────────────────────────────────────────────── --}}
    @if ($tab === 'users')
        <div x-data="{ add: false }">
            <button @click="add = !add" class="mb-3 px-3 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Add user</button>
            <form x-show="add" x-cloak method="POST" action="{{ route('sentinel-admin.users.store') }}" class="mb-4 bg-white rounded-xl border border-gray-200 p-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                @csrf
                <input name="full_name" required placeholder="Full name" value="{{ old('full_name') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
                <input name="email" type="email" required placeholder="Email" value="{{ old('email') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
                <select name="company_id" required class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
                    <option value="">Client…</option>
                    @foreach ($clientOptions as $c)<option value="{{ $c['id'] }}">{{ $c['name'] }} ({{ $c['plan'] }})</option>@endforeach
                </select>
                <select name="role" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
                    @foreach ($roles as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach
                </select>
                <button class="py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create and email login link</button>
            </form>
        </div>
        @include('admin.sentinel._users', ['users' => $data, 'showClient' => true])
    @endif

    {{-- ── ORDERS ───────────────────────────────────────────────────────── --}}
    @if ($tab === 'orders')
        @include('admin.sentinel._orders', ['orders' => $data, 'showClient' => true])
    @endif

    {{-- ── API KEY REQUESTS ─────────────────────────────────────────────── --}}
    @if ($tab === 'requests')
        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr><th class="px-4 py-3 text-left">Requested by</th><th class="px-4 py-3 text-left">Company</th><th class="px-4 py-3 text-left">Integration</th>
                        <th class="px-4 py-3 text-left">Notes</th><th class="px-4 py-3 text-left">Received</th><th class="px-4 py-3 text-right">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse ($data as $r)
                    <tr>
                        <td class="px-4 py-3"><p class="font-medium text-gray-900">{{ $r['name'] }}</p><p class="text-xs text-gray-500">{{ $r['email'] }}{{ $r['phone'] ? ' · ' . $r['phone'] : '' }}</p></td>
                        <td class="px-4 py-3 text-gray-600">{{ $r['company'] ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $r['integration'] ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $r['notes'] }}">{{ $r['notes'] ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $day($r['created_at']) }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('sentinel-admin.requests.status', $r['id']) }}">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1.5 text-sm border border-gray-200 rounded-lg {{ $r['status'] === 'PENDING' ? 'bg-amber-50' : '' }}">
                                    @foreach (['PENDING' => 'Pending', 'CONTACTED' => 'Contacted', 'ISSUED' => 'Key issued', 'DECLINED' => 'Declined'] as $code => $label)
                                        <option value="{{ $code }}" @selected($r['status'] === $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No API key requests.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ── ACTIVITY LOG ─────────────────────────────────────────────────── --}}
    @if ($tab === 'log')
        @include('admin.sentinel._log', ['log' => $data, 'showClient' => true])
    @endif

    @endif
</div>
@endsection
