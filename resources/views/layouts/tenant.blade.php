<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $tenant->name . ' — Compliance Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .nav-link { display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;transition:all .15s;margin-bottom:2px; }
        .nav-link:not(.active) { color:rgba(255,255,255,0.6); }
        .nav-link:not(.active):hover { background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.9); }
        .nav-link.active { background:rgba(255,255,255,0.15);color:#fff; }
        .nav-sub { display:block;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:500;text-decoration:none;transition:all .15s;margin-bottom:1px; }
        .nav-sub:not(.active) { color:rgba(255,255,255,0.5); }
        .nav-sub:not(.active):hover { color:rgba(255,255,255,0.8); }
        .nav-sub.active { color:#fff; }
        .nav-btn { display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;font-size:13px;font-weight:500;text-align:left;background:transparent;border:none;cursor:pointer;width:100%;transition:all .15s;margin-bottom:2px; }
        .nav-btn:not(.active) { color:rgba(255,255,255,0.6); }
        .nav-btn:not(.active):hover { background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.9); }
        .nav-btn.active { background:rgba(255,255,255,0.15);color:#fff; }
    </style>
</head>
<body class="h-full bg-gray-50 flex overflow-hidden">

{{-- ── Sidebar ────────────────────────────────────────────────────────── --}}
<aside style="width:220px;flex-shrink:0;display:flex;flex-direction:column;background:#0f2744">

    {{-- Logo --}}
    <div style="padding:16px;border-bottom:1px solid rgba(255,255,255,0.1);flex-shrink:0">
        @if($tenant->logo_url ?? false)
            <img src="{{ Storage::url($tenant->logo_url) }}" alt="{{ $tenant->name }}" style="height:32px;object-fit:contain">
        @else
            <p style="font-weight:700;font-size:14px;color:#fff;line-height:1.2">{{ $tenant->name }}</p>
        @endif
        <p style="font-size:11px;color:#93c5fd;margin-top:2px">Compliance Portal</p>
    </div>

    {{-- Nav --}}
    <nav style="flex:1;padding:12px 8px;overflow-y:auto">

        @php $slug = $tenant->slug; @endphp

        {{-- Dashboard --}}
        <a href="{{ route('tenant.dashboard', $slug) }}"
           class="nav-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        {{-- Clients --}}
        <div x-data="{ open: {{ request()->routeIs('tenant.clients*') ? 'true' : 'false' }} }">
            <button @click="open=!open"
                    class="nav-btn {{ request()->routeIs('tenant.clients*') ? 'active' : '' }}">
                <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span style="flex:1">Clients</span>
                <svg style="width:11px;height:11px;flex-shrink:0;transition:transform .15s" :style="open ? 'transform:rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak style="padding-left:34px;margin-top:2px">
                <a href="{{ route('tenant.clients.index', $slug) }}"
                   class="nav-sub {{ request()->routeIs('tenant.clients.index') && !request('type') ? 'active' : '' }}">
                    All clients
                </a>
                @foreach($sector['client_types'] as $typeKey => $typeLabel)
                <a href="{{ route('tenant.clients.index', $slug) }}?type={{ $typeKey }}"
                   class="nav-sub {{ request('type') === $typeKey ? 'active' : '' }}">
                    {{ $typeLabel }}
                </a>
                @endforeach
                <div style="border-top:1px solid rgba(255,255,255,0.08);margin:6px 0"></div>
                <a href="{{ route('tenant.clients.create', $slug) }}"
                   class="nav-sub {{ request()->routeIs('tenant.clients.create') ? 'active' : '' }}">
                    + Add new client
                </a>
            </div>
        </div>

        {{-- Screening --}}
        <a href="{{ route('tenant.screening', $slug) }}"
           class="nav-link {{ request()->routeIs('tenant.screening') ? 'active' : '' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Screening
        </a>

        {{-- Risk Assessment --}}
        <a href="{{ route('tenant.risk', $slug) }}"
           class="nav-link {{ request()->routeIs('tenant.risk') ? 'active' : '' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Risk assessment
        </a>

        {{-- Documents --}}
        <div x-data="{ open: {{ request()->routeIs('tenant.docs*') ? 'true' : 'false' }} }">
            <button @click="open=!open"
                    class="nav-btn {{ request()->routeIs('tenant.docs*') ? 'active' : '' }}">
                <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                </svg>
                <span style="flex:1">Documents</span>
                <svg style="width:11px;height:11px;flex-shrink:0;transition:transform .15s" :style="open ? 'transform:rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak style="padding-left:34px;margin-top:2px">
                <a href="{{ route('tenant.docs.company', $slug) }}"
                   class="nav-sub {{ request()->routeIs('tenant.docs.company') ? 'active' : '' }}">Company docs</a>
                <a href="{{ route('tenant.docs.clients', $slug) }}"
                   class="nav-sub {{ request()->routeIs('tenant.docs.clients') ? 'active' : '' }}">Client docs</a>
            </div>
        </div>

        {{-- goAML --}}
        <a href="{{ route('tenant.goaml', $slug) }}"
           class="nav-link {{ request()->routeIs('tenant.goaml') ? 'active' : '' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            goAML reports
        </a>

        {{-- Settings --}}
        <a href="{{ route('tenant.settings', $slug) }}"
           class="nav-link {{ request()->routeIs('tenant.settings') ? 'active' : '' }}">
            <svg style="width:16px;height:16px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
        </a>

    </nav>

    {{-- User footer --}}
    @auth
    <div style="border-top:1px solid rgba(255,255,255,0.1);padding:12px">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div style="min-width:0;flex:1">
                <p style="font-size:12px;font-weight:500;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ auth()->user()->name }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="font-size:11px;color:rgba(255,255,255,0.4);background:none;border:none;cursor:pointer;padding:0">Out</button>
            </form>
        </div>
    </div>
    @endauth

</aside>

{{-- ── Main content ────────────────────────────────────────────────────── --}}
<div style="flex:1;display:flex;flex-direction:column;min-height:0;overflow:hidden">

    {{-- Top bar --}}
    <header style="background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
        <div>
            <h1 style="font-size:15px;font-weight:600;color:#111827">@yield('page-title', 'Dashboard')</h1>
            @hasSection('page-subtitle')
            <p style="font-size:12px;color:#9ca3af;margin-top:1px">@yield('page-subtitle')</p>
            @endif
        </div>
        <span style="font-size:12px;color:#9ca3af">{{ now()->format('l, d M Y') }}</span>
    </header>

    {{-- Page content --}}
    <div style="flex:1;overflow-y:auto">
        @if(session('success'))
        <div style="margin:16px 24px 0;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;color:#15803d">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="margin:16px 24px 0;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:13px;color:#b91c1c">
            {{ session('error') }}
        </div>
        @endif

        <main style="padding:24px">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
