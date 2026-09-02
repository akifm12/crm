<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>B-Book — {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: radial-gradient(circle at 50% 0%, #1a1a22 0%, #0a0a0d 55%, #000 100%); }
        .gold-glow { box-shadow: 0 0 0 1px rgba(212,175,55,0.25), 0 20px 60px -15px rgba(212,175,55,0.15); }
        .gold-text { background: linear-gradient(135deg, #f4d35e, #d4af37 50%, #b8860b); -webkit-background-clip: text; background-clip: text; color: transparent; }
    </style>
</head>
<body class="h-full flex items-center justify-center px-4">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <p class="text-xs tracking-[0.3em] text-amber-500/70 uppercase mb-2">{{ $tenant->name }}</p>
        <h1 class="text-3xl font-serif gold-text tracking-wide">B-Book</h1>
        <p class="text-xs text-gray-500 mt-2">Consolidated ledger &amp; OTC dealings</p>
    </div>

    <div class="bg-[#131318] border border-white/5 rounded-2xl p-7 gold-glow">
        @if(session('error'))
        <div class="mb-4 px-3 py-2 bg-red-950/50 border border-red-900/50 rounded-lg text-xs text-red-300">
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-4 px-3 py-2 bg-red-950/50 border border-red-900/50 rounded-lg text-xs text-red-300">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('bbook.login.store', $tenant->slug) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full px-3 py-2.5 bg-black/40 border border-white/10 rounded-lg text-sm text-gray-100 placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50">
            </div>
            <div class="mb-6">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Password</label>
                <input type="password" name="password" required autocomplete="current-password"
                       class="w-full px-3 py-2.5 bg-black/40 border border-white/10 rounded-lg text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-amber-500/50 focus:border-amber-500/50">
            </div>
            <button type="submit"
                    class="w-full py-2.5 text-sm font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-600 rounded-lg hover:from-amber-300 hover:to-yellow-500 transition">
                Enter B-Book
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-700 mt-6">Restricted access — authorized personnel only</p>
</div>

</body>
</html>
