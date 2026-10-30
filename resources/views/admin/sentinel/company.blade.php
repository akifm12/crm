@extends('layouts.admin')
@php $c = $d['company']; $day = fn ($x) => $x ? \Carbon\Carbon::parse($x)->format('j M Y') : '—'; @endphp
@section('title', $c['name'] . ' — Sentinel — BlueArrow Portal')
@section('page-title', 'Sentinel')

@section('content')
<div class="space-y-5">

    <a href="{{ route('sentinel-admin.index', ['tab' => 'clients']) }}" class="text-sm text-blue-600 hover:underline">← All clients</a>

    {{-- Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-gray-900">{{ $c['name'] }}</h2>
                @include('admin.sentinel._plan', ['plan' => $c['plan']])
                @if ($c['portal_tenant_id'])<span class="text-xs text-gray-500">portal tenant #{{ $c['portal_tenant_id'] }}</span>@endif
            </div>
            <p class="mt-1 text-sm text-gray-500">Client #{{ $c['id'] }} · since {{ $day($c['created_at']) }} · {{ ucfirst($c['data_tier']) }} data
                @if ($c['plan'] === 'TRIAL' && $c['sandbox_started_at']) · sandbox: {{ $c['sandbox_used'] }} of 50 API screenings since {{ $day($c['sandbox_started_at']) }}@endif</p>
        </div>
        <div class="grid grid-cols-3 gap-6 text-center">
            <div><p class="text-2xl font-semibold text-gray-900">{{ count($d['users']) }}</p><p class="text-xs text-gray-500">Users</p></div>
            <div><p class="text-2xl font-semibold text-gray-900">{{ number_format($d['usage']['cases_30d']) }}</p><p class="text-xs text-gray-500">Screenings, 30 days</p></div>
            <div><p class="text-2xl font-semibold text-gray-900">{{ number_format($d['usage']['cases_total']) }}</p><p class="text-xs text-gray-500">Screenings, all time</p></div>
        </div>
    </div>

    @if (session('new_key'))
        @php $k = session('new_key'); @endphp
        <div class="rounded-xl border-2 border-emerald-300 bg-emerald-50 p-4" x-data="{ copied: false }">
            <p class="text-sm font-semibold text-emerald-800">New {{ $k['mode'] }} API key for {{ $c['name'] }}: copy it now, it won't be shown again.</p>
            <div class="mt-2 flex gap-2">
                <code class="flex-1 px-3 py-2 bg-white border border-emerald-200 rounded-lg text-sm break-all" x-ref="key">{{ $k['apiKey'] }}</code>
                <button @click="navigator.clipboard.writeText($refs.key.innerText); copied = true" class="px-3 py-2 text-sm font-medium bg-emerald-600 text-white rounded-lg" x-text="copied ? 'Copied' : 'Copy'"></button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Plan --}}
        <form method="POST" action="{{ route('sentinel-admin.companies.plan', $c['id']) }}" class="bg-white rounded-xl border border-gray-200 p-5 space-y-3"
              onsubmit="return confirm('Change the plan for {{ addslashes($c['name']) }}? Every user\'s access changes with it.')">
            @csrf
            <h3 class="text-sm font-semibold text-gray-700">Plan</h3>
            <div class="grid grid-cols-2 gap-3">
                <label class="text-xs text-gray-600">Plan
                    <select name="plan" class="mt-1 w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                        @foreach ($plans as $code => $label)<option value="{{ $code }}" @selected($c['plan'] === $code)>{{ $label }}</option>@endforeach
                    </select></label>
                <label class="text-xs text-gray-600">Expiry (optional)
                    <input type="date" name="expiry_date" class="mt-1 w-full px-3 py-2 text-sm border border-gray-200 rounded-lg"></label>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed">Switches the client's data and its users' access together: Essential has no monitoring;
                Complete and Corporate API add monitoring and the API; Premium data adds the wider coverage. A paid plan runs a year from today unless you set an expiry. aml@ is emailed.
                To activate a self-service order, use <b>Mark paid</b> below instead.</p>
            <button class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">Change plan</button>
        </form>

        {{-- Details --}}
        <form method="POST" action="{{ route('sentinel-admin.companies.update', $c['id']) }}" class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            @csrf @method('PATCH')
            <h3 class="text-sm font-semibold text-gray-700">Details</h3>
            <label class="block text-xs text-gray-600">Name
                <input name="name" value="{{ $c['name'] }}" required class="mt-1 w-full px-3 py-2 text-sm border border-gray-200 rounded-lg"></label>
            <div class="grid grid-cols-2 gap-3">
                <label class="text-xs text-gray-600">Data
                    <select name="data_tier" class="mt-1 w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                        <option value="standard" @selected($c['data_tier'] === 'standard')>Standard (official lists)</option>
                        <option value="premium" @selected($c['data_tier'] === 'premium')>Premium (wider coverage)</option>
                    </select></label>
                <label class="text-xs text-gray-600">Client quota
                    <input name="quota" type="number" min="0" value="{{ $c['quota'] }}" class="mt-1 w-full px-3 py-2 text-sm border border-gray-200 rounded-lg"></label>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="id_scan_enabled" value="1" @checked($c['id_scan_enabled'])> ID scanning enabled</label>
            <div class="flex justify-between items-center">
                <button class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save details</button>
            </div>
        </form>
    </div>

    {{-- Users --}}
    <div class="space-y-3" x-data="{ add: false }">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Users</h3>
            <button @click="add = !add" class="px-3 py-1.5 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Add user</button>
        </div>
        <form x-show="add" x-cloak method="POST" action="{{ route('sentinel-admin.users.store') }}" class="bg-white rounded-xl border border-gray-200 p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            @csrf
            <input type="hidden" name="company_id" value="{{ $c['id'] }}">
            <input name="full_name" required placeholder="Full name" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
            <input name="email" type="email" required placeholder="Email" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
            <select name="role" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
                @foreach ($roles as $code => $label)
                    @if ($code !== 'admin' || $d['is_blue_arrow'])<option value="{{ $code }}">{{ $label }}</option>@endif
                @endforeach
            </select>
            <button class="py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create and email login link</button>
        </form>
        @include('admin.sentinel._users', ['users' => $d['users'], 'showClient' => false])
    </div>

    {{-- API keys --}}
    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-semibold text-gray-700">API keys</h3>
            <form method="POST" action="{{ route('sentinel-admin.keys.store', $c['id']) }}" class="flex flex-wrap gap-2">
                @csrf
                <input name="label" placeholder="Label (optional)" class="w-48 px-3 py-1.5 text-sm border border-gray-200 rounded-lg">
                <select name="mode" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg">
                    <option value="test">Test (sandbox)</option>
                    <option value="live" @disabled(! in_array($c['plan'], ['COMPLETE', 'API', 'PREMIUM'], true))>Live{{ in_array($c['plan'], ['COMPLETE', 'API', 'PREMIUM'], true) ? '' : ' (Complete plans only)' }}</option>
                </select>
                <button class="px-3 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Create key</button>
            </form>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr><th class="px-4 py-3 text-left">Key</th><th class="px-4 py-3 text-left">Mode</th><th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-left">Last used</th><th class="px-4 py-3 text-left">Expires</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse ($d['keys'] as $k)
                    <tr class="{{ $k['is_active'] ? '' : 'text-gray-400' }}">
                        <td class="px-4 py-3"><p class="font-medium">{{ $k['owner_name'] }}</p><code class="text-xs text-gray-500">{{ $k['key_display'] }}</code></td>
                        <td class="px-4 py-3">{{ $k['mode'] === 'test' ? 'Test' : 'Live' }}</td>
                        <td class="px-4 py-3">{{ $day($k['created_at']) }}</td>
                        <td class="px-4 py-3">{{ $k['last_used_at'] ? \Carbon\Carbon::parse($k['last_used_at'])->diffForHumans() : 'never' }}</td>
                        <td class="px-4 py-3">{{ $k['is_active'] ? $day($k['expires_at']) : 'Revoked' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($k['is_active'])
                                <form method="POST" action="{{ route('sentinel-admin.keys.revoke', [$c['id'], $k['key_id']]) }}" onsubmit="return confirm('Revoke this key? Any system using it stops working at once.')">
                                    @csrf @method('DELETE')<button class="text-sm text-red-600 hover:text-red-800">Revoke</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No API keys.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Orders --}}
    @if (count($d['orders']))
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">Orders</h3>
            @include('admin.sentinel._orders', ['orders' => $d['orders'], 'showClient' => false])
        </div>
    @endif

    {{-- Log --}}
    <div class="space-y-3">
        <h3 class="text-sm font-semibold text-gray-700">Activity</h3>
        @include('admin.sentinel._log', ['log' => $d['log'], 'showClient' => false])
    </div>

    {{-- Delete --}}
    @unless ($d['is_blue_arrow'])
        <form method="POST" action="{{ route('sentinel-admin.companies.destroy', $c['id']) }}" class="pt-2"
              onsubmit="return confirm('Delete {{ addslashes($c['name']) }}? Only a client with no users, screenings, orders or keys can be deleted.')">
            @csrf @method('DELETE')
            <button class="text-sm text-red-600 hover:text-red-800">Delete this client</button>
        </form>
    @endunless
</div>
@endsection
