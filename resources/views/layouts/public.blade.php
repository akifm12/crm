<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Blue Arrow Management Consultants — your trusted AML & regulatory compliance partner for DNFBPs across the UAE.')">

    <title>@yield('title', 'Blue Arrow Management Consultants — UAE Regulatory Compliance')</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-white" x-data="{ mobileNav: false }">

    {{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-18 py-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Blue Arrow" class="h-10 w-auto">
                    <span class="font-bold text-gray-900 leading-tight hidden sm:block">
                        Blue Arrow<span class="block text-[11px] font-medium text-gray-500 tracking-wide">MANAGEMENT CONSULTANTS</span>
                    </span>
                </a>

                <nav class="hidden lg:flex items-center gap-7 text-sm font-medium text-gray-600">
                    <a href="{{ route('home') }}" class="hover:text-brand-600 {{ request()->routeIs('home') ? 'text-brand-600' : '' }}">Home</a>

                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button @click="open = !open" class="flex items-center gap-1 hover:text-brand-600 {{ request()->routeIs('services', 'technology') ? 'text-brand-600' : '' }}">
                            Services
                            <svg class="h-3.5 w-3.5" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition class="absolute left-0 top-full mt-2 w-64 rounded-xl border border-gray-100 bg-white shadow-lg py-2">
                            <a href="{{ route('services') }}" class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                                <span class="block font-medium text-gray-900">Compliance Services</span>
                                <span class="block text-xs text-gray-400">Sector-specific AML/CFT advisory</span>
                            </a>
                            <a href="{{ route('technology') }}" class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                                <span class="block font-medium text-gray-900">Technology Services</span>
                                <span class="block text-xs text-gray-400">KYC portal, accounting software, custom builds</span>
                            </a>
                        </div>
                    </div>

                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button @click="open = !open" class="flex items-center gap-1 hover:text-brand-600 {{ request()->routeIs('compliance-calendar', 'resources', 'news*') ? 'text-brand-600' : '' }}">
                            Resources
                            <svg class="h-3.5 w-3.5" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition class="absolute left-0 top-full mt-2 w-64 rounded-xl border border-gray-100 bg-white shadow-lg py-2">
                            <a href="{{ route('compliance-calendar') }}" class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                                <span class="block font-medium text-gray-900">Compliance Calendar</span>
                                <span class="block text-xs text-gray-400">Track your own deadlines free</span>
                            </a>
                            <a href="{{ route('resources') }}" class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                                <span class="block font-medium text-gray-900">Downloads</span>
                                <span class="block text-xs text-gray-400">Free checklists &amp; guides</span>
                            </a>
                            <a href="{{ route('news') }}" class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                                <span class="block font-medium text-gray-900">News &amp; Insights</span>
                                <span class="block text-xs text-gray-400">Regulatory updates + BA-Digest</span>
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('about') }}" class="hover:text-brand-600 {{ request()->routeIs('about') ? 'text-brand-600' : '' }}">About</a>
                    <a href="{{ route('contact') }}" class="hover:text-brand-600 {{ request()->routeIs('contact') ? 'text-brand-600' : '' }}">Contact</a>
                </nav>

                <div class="hidden lg:flex items-center gap-3">
                    <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                        <button @click="open = !open" class="flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-800">
                            Account
                            <svg class="h-3.5 w-3.5" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition class="absolute right-0 top-full mt-2 w-56 rounded-xl border border-gray-100 bg-white shadow-lg py-2">
                            @auth('public')
                                <a href="{{ route('account.dashboard') }}" class="block px-4 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-50">My Deadlines</a>
                                <form method="POST" action="{{ route('account.logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-gray-500 hover:bg-gray-50">Sign out</button>
                                </form>
                            @else
                                <a href="{{ route('account.register') }}" class="block px-4 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-50">Register Free</a>
                                <a href="{{ route('account.login') }}" class="block px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50">Sign In</a>
                            @endauth
                            <div class="my-1 border-t border-gray-100"></div>
                            <a href="{{ route('login') }}" class="block px-4 py-2.5 text-sm text-gray-500 hover:bg-gray-50">Client Portal Login</a>
                        </div>
                    </div>
                    <a href="{{ route('contact') }}" class="text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg shadow-sm transition">Get Consultation</a>
                </div>

                <button @click="mobileNav = ! mobileNav" class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileNav, 'inline-flex': !mobileNav}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !mobileNav, 'inline-flex': mobileNav}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-cloak x-show="mobileNav" x-transition class="lg:hidden border-t border-gray-100 bg-white">
            <div class="px-4 py-3 space-y-1 text-sm font-medium">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">Home</a>

                <div x-data="{ open: {{ request()->routeIs('services', 'technology') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                        Services
                        <svg class="h-4 w-4" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="pl-6 space-y-1">
                        <a href="{{ route('services') }}" class="block px-3 py-2 rounded-md text-gray-600 hover:bg-gray-50">Compliance Services</a>
                        <a href="{{ route('technology') }}" class="block px-3 py-2 rounded-md text-gray-600 hover:bg-gray-50">Technology Services</a>
                    </div>
                </div>

                <div x-data="{ open: {{ request()->routeIs('compliance-calendar', 'resources', 'news*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                        Resources
                        <svg class="h-4 w-4" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="pl-6 space-y-1">
                        <a href="{{ route('compliance-calendar') }}" class="block px-3 py-2 rounded-md text-gray-600 hover:bg-gray-50">Compliance Calendar</a>
                        <a href="{{ route('resources') }}" class="block px-3 py-2 rounded-md text-gray-600 hover:bg-gray-50">Downloads</a>
                        <a href="{{ route('news') }}" class="block px-3 py-2 rounded-md text-gray-600 hover:bg-gray-50">News &amp; Insights</a>
                    </div>
                </div>

                <a href="{{ route('about') }}" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">About</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">Contact</a>

                <div class="pt-2 mt-2 border-t border-gray-100 flex flex-col gap-1">
                    @auth('public')
                        <a href="{{ route('account.dashboard') }}" class="text-brand-700 font-semibold px-3 py-2">My Deadlines</a>
                        <form method="POST" action="{{ route('account.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left text-gray-500 px-3 py-2">Sign out</button>
                        </form>
                    @else
                        <a href="{{ route('account.register') }}" class="text-brand-700 font-semibold px-3 py-2">Register Free</a>
                        <a href="{{ route('account.login') }}" class="text-gray-600 px-3 py-2">Sign In</a>
                    @endauth
                    <a href="{{ route('login') }}" class="text-gray-500 px-3 py-2">Client Portal Login</a>
                    <a href="{{ route('contact') }}" class="mt-2 bg-brand-600 text-white text-center font-semibold px-3 py-2 rounded-lg">Get Consultation</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    {{-- ── FOOTER ──────────────────────────────────────────────────────────── --}}
    <footer class="bg-gray-900 text-gray-300 mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 md:grid-cols-4 gap-10">
            <div class="md:col-span-1">
                <img src="{{ asset('images/logo.png') }}" alt="Blue Arrow" class="h-10 w-auto mb-4 brightness-0 invert opacity-90">
                <p class="text-sm text-gray-400 leading-relaxed">Your trusted partner for regulatory compliance in the UAE. Expert AML &amp; DNFBP guidance for confident business operations.</p>
            </div>

            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-white">Services</a></li>
                    <li><a href="{{ route('technology') }}" class="hover:text-white">Technology Services</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                    @auth('public')
                        <li><a href="{{ route('account.dashboard') }}" class="hover:text-white">My Deadlines</a></li>
                    @else
                        <li><a href="{{ route('account.register') }}" class="hover:text-white">Register Free</a></li>
                    @endauth
                    <li><a href="{{ route('login') }}" class="hover:text-white">Client Portal</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Resources</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="{{ route('compliance-calendar') }}" class="hover:text-white">Compliance Calendar</a></li>
                    <li><a href="{{ route('resources') }}" class="hover:text-white">Downloadable Guides</a></li>
                    <li><a href="{{ route('news') }}" class="hover:text-white">Regulatory &amp; Industry News</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Contact</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li>SRTI Park, University Road, Sharjah, UAE</li>
                    <li><a href="mailto:info@bluearrow.ae" class="hover:text-white">info@bluearrow.ae</a></li>
                    <li><a href="tel:+971526461499" class="hover:text-white">+971 52 6461499</a></li>
                </ul>

                @if (session('status') && str_contains(session('status'), 'subscribed'))
                    <p class="mt-4 text-sm text-emerald-400">{{ session('status') }}</p>
                @else
                    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-4 flex gap-2">
                        @csrf
                        <input type="email" name="email" required placeholder="Email address"
                               class="min-w-0 flex-1 rounded-md bg-gray-800 border-gray-700 text-sm text-white placeholder-gray-500 focus:border-brand-500 focus:ring-brand-500">
                        <button type="submit" class="shrink-0 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-3 py-2 rounded-md">Subscribe</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} Blue Arrow Management Consultants. All rights reserved.</p>
                <p>Trusted name in UAE regulatory compliance.</p>
            </div>
        </div>
    </footer>

</body>
</html>
