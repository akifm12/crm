<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'B-Book — ' . $tenant->name)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { background: #08080a; }
        .gold-text { background: linear-gradient(135deg, #f4d35e, #d4af37 50%, #b8860b); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .nav-link { display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;transition:all .15s;margin-bottom:2px; }
        .nav-link:not(.active) { color:rgba(255,255,255,0.45); }
        .nav-link:not(.active):hover { background:rgba(212,175,55,0.06);color:rgba(255,255,255,0.85); }
        .nav-link.active { background:rgba(212,175,55,0.12);color:#f4d35e;border:1px solid rgba(212,175,55,0.2); }
        .card { background:#111114;border:1px solid rgba(255,255,255,0.06);border-radius:14px; }
    </style>
</head>
<body class="h-full text-gray-200 flex overflow-hidden">

<aside style="width:230px;flex-shrink:0;display:flex;flex-direction:column;background:#050506;border-right:1px solid rgba(255,255,255,0.06)">
    <div style="padding:18px 16px;border-bottom:1px solid rgba(255,255,255,0.06)">
        <p class="text-xs tracking-[0.25em] text-amber-500/60 uppercase">{{ $tenant->name }}</p>
        <p class="text-xl font-serif gold-text mt-0.5">B-Book</p>
    </div>

    <nav style="flex:1;overflow-y:auto;padding:14px 10px">
        <a href="{{ route('bbook.dashboard', $tenant->slug) }}"
           class="nav-link {{ request()->routeIs('bbook.dashboard') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('bbook.deals.index', $tenant->slug) }}"
           class="nav-link {{ request()->routeIs('bbook.deals.*') ? 'active' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            OTC Deals
        </a>
    </nav>

    <div style="padding:14px;border-top:1px solid rgba(255,255,255,0.06)">
        <form method="POST" action="{{ route('bbook.logout', $tenant->slug) }}">
            @csrf
            <button type="submit" class="w-full text-left nav-link" style="color:rgba(255,255,255,0.4)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Sign out
            </button>
        </form>
    </div>
</aside>

<main style="flex:1;overflow-y:auto">
    <div style="padding:28px 36px;max-width:1200px">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
            @hasSection('page-subtitle')
            <p class="text-sm text-gray-500 mt-1">@yield('page-subtitle')</p>
            @endif
        </div>

        @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-emerald-950/40 border border-emerald-900/50 rounded-lg text-sm text-emerald-300">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mb-5 px-4 py-3 bg-red-950/40 border border-red-900/50 rounded-lg text-sm text-red-300">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</main>

</body>
</html>
