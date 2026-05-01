<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $tenant->name . ' — Compliance Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-gray-50 flex overflow-hidden">

<aside class="w-56 flex-shrink-0 flex flex-col" style="background:#0f2744;">

    <div class="px-4 py-4" style="border-bottom:1px solid rgba(255,255,255,0.1)">
        @if($tenant->logo_url)
            <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-8 object-contain">
        @else
            <p class="font-bold text-sm text-white leading-tight">{{ $tenant->name }}</p>
        @endif
        <p class="text-xs mt-0.5" style="color:#93c5fd">Compliance Portal</p>
    </div>

    <nav class="flex-1 px-2 py-3 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="{{ route('tenant.dashboard', $tenant->slug) }}"
           style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:14px;font-weight:500;text-decoration:none;margin-bottom:2px;
                  {{ request()->routeIs('tenant.dashboard') ? 'background:rgba(255,255,255,0.15);color:#fff' : 'color:rgba(255,255,255,0.6)' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        {{-- Clients (collapsible) --}}
        <div x-data="{ open: {{ request()->routeIs('tenant.clients*') ? 'true' : 'false' }} }" style="margin-bottom:2px">
            <button @click="open=!open"
                    style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:14px;font-weight:500;width:100%;text-align:left;background:transparent;border:none;cursor:pointer;
                           {{ request()->routeIs('tenant.clients*') ? 'background:rgba(255,255,255,0.15);color:#fff' : 'color:rgba(255,255,255,0.6)' }}">
                <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span style="flex:1">Clients</span>
                <svg style="width:12px;height:12px;flex-shrink:0;transition:transform 0.15s" :style="open ? 'transform:rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak style="padding-left:36px;margin-top:2px">
                <a href="{{ route('tenant.clients.index', $tenant->slug) }}"
                   style="display:block;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;margin-bottom:1px;
                          {{ request()->routeIs('tenant.clients.index') ? 'color:#fff' : 'color:rgba(255,255,255,0.5)' }}">
                    All clients
                </a>
                <a href="{{ route('tenant.clients.create', $tenant->slug) }}"
                   style="display:block;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;margin-bottom:1px;
                          {{ request()->routeIs('tenant.clients.create') ? 'color:#fff' : 'color:rgba(255,255,255,0.5)' }}">
                    Add new client
                </a>
                <a href="{{ route('tenant.clients.index', $tenant->slug) }}"
                   style="display:block;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;color:rgba(255,255,255,0.5)">
                    KYC reviews due
                </a>
            </div>
        </div>

        {{-- Screening --}}
        <a href="{{ route('tenant.screening', $tenant->slug) }}"
           style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:14px;font-weight:500;text-decoration:none;margin-bottom:2px;
                  {{ request()->routeIs('tenant.screening') ? 'background:rgba(255,255,255,0.15);color:#fff' : 'color:rgba(255,255,255,0.6)' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Screening
        </a>

        {{-- Risk Assessment --}}
        <a href="{{ route('tenant.risk', $tenant->slug) }}"
           style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:14px;font-weight:500;text-decoration:none;margin-bottom:2px;
                  {{ request()->routeIs('tenant.risk') ? 'background:rgba(255,255,255,0.15);color:#fff' : 'color:rgba(255,255,255,0.6)' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Risk assessment
        </a>

        {{-- Documents (collapsible) --}}
        <div x-data="{ open: {{ request()->routeIs('tenant.docs*') ? 'true' : 'false' }} }" style="margin-bottom:2px">
            <button @click="open=!open"
                    style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:14px;font-weight:500;width:100%;text-align:left;background:transparent;border:none;cursor:pointer;
                           {{ request()->routeIs('tenant.docs*') ? 'background:rgba(255,255,255,0.15);color:#fff' : 'color:rgba(255,255,255,0.6)' }}">
                <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                </svg>
                <span style="flex:1">Documents</span>
                <svg style="width:12px;height:12px;flex-shrink:0;transition:transform 0.15s" :style="open ? 'transform:rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak style="padding-left:36px;margin-top:2px">
                <a href="{{ route('tenant.docs.company', $tenant->slug) }}"
                   style="display:block;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;margin-bottom:1px;color:rgba(255,255,255,0.5)">
                    Company docs
                </a>
                <a href="{{ route('tenant.docs.clients', $tenant->slug) }}"
                   style="display:block;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;color:rgba(255,255,255,0.5)">
                    Client docs
                </a>
            </div>
        </div>

        {{-- goAML --}}
        <a href="{{ route('tenant.goaml', $tenant->slug) }}"
           style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:14px;font-weight:500;text-decoration:none;margin-bottom:2px;
                  {{ request()->routeIs('tenant.goaml') ? 'background:rgba(255,255,255,0.15);color:#fff' : 'color:rgba(255,255,255,0.6)' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            goAML reports
        </a>

        <div style="border-top:1px solid rgba(255,255,255,0.1);margin:8px 0"></div>

        {{-- Settings --}}
        <a href="{{ route('tenant.settings', $tenant->slug) }}"
           style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:14px;font-weight:500;text-decoration:none;
                  {{ request()->routeIs('tenant.settings') ? 'background:rgba(255,255,255,0.15);color:#fff' : 'color:rgba(255,255,255,0.6)' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
        </a>

    </nav>

    {{-- User footer --}}
    <div style="border-top:1px solid rgba(255,255,255,0.1);padding:12px 16px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:28px;height:28px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white;flex-shrink:0">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div style="min-width:0;flex:1">
                <p style="color:white;font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->name ?? 'User' }}</p>
                <p style="color:#93c5fd;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $tenant->name }}</p>
            </div>
        </div>
    </div>
</aside>

{{-- ── MAIN CONTENT ─────────────────────────────────────────────────────── --}}
<div class="flex-1 flex flex-col min-h-0 overflow-hidden">

    <header class="bg-white border-b border-gray-200 px-6 py-3.5 flex items-center justify-between flex-shrink-0">
        <div>
            <h1 class="text-base font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            @hasSection('page-subtitle')
                <p class="text-xs text-gray-400 mt-0.5">@yield('page-subtitle')</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>
            <span class="text-xs text-gray-400 hidden sm:block">{{ now()->format('d M Y') }}</span>
        </div>
    </header>

    @if(session('success'))
        <div class="mx-6 mt-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mx-6 mt-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <main class="flex-1 overflow-y-auto p-6">
        @yield('content')
    </main>
</div>

</body>
</html>
