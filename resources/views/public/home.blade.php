@extends('layouts.public')

@section('title', 'AML Compliance Made Simple for UAE Businesses | Blue Arrow')
@section('meta_description', 'AML compliance made simple for UAE gold dealers, real estate brokers, corporate service providers and accountants. A plain-English guide to screening, due diligence, risk assessment and goAML, plus hands-on support.')

@section('content')

{{-- ── HERO SLIDER ──────────────────────────────────────────────────────
     Three slides stacked in one grid cell so the height never jumps; they cross-fade.
     Auto-advances every 8s, pauses on hover/focus, and stays put for reduced-motion users. --}}
<section class="relative" aria-roledescription="carousel" aria-label="Highlights"
         x-data="{
            i: 0, n: 3, paused: false, timer: null,
            go(k) { this.i = (k + this.n) % this.n },
            pick(k) { this.go(k); clearInterval(this.timer); this.start() },  // visitor's choice gets a full 8s
            start() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                this.timer = setInterval(() => { if (! this.paused && ! document.hidden) this.go(this.i + 1) }, 8000);
            },
         }"
         x-init="start()"
         @mouseenter="paused = true" @mouseleave="paused = false"
         @focusin="paused = true" @focusout="paused = false"
         @keydown.arrow-left="pick(i - 1)" @keydown.arrow-right="pick(i + 1)">

    <div class="grid">

        {{-- 1. Compliance guide ──────────────────────────────────────────── --}}
        <div class="col-start-1 row-start-1 bg-gradient-to-b from-brand-50 via-white to-white transition-opacity duration-700"
             :class="i === 0 ? 'opacity-100 z-10' : 'opacity-0 pointer-events-none'" :inert="i !== 0"
             role="group" aria-roledescription="slide" aria-label="1 of 3: AML compliance guide">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-24 lg:pt-20 lg:pb-28 grid lg:grid-cols-5 gap-12 items-center">
                <div class="lg:col-span-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 text-brand-700 text-xs font-semibold px-3 py-1">
                        🛡️ AML &amp; regulatory compliance · UAE
                    </span>

                    <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.1]">
                        AML compliance in the UAE, <span class="text-brand-600">made simple.</span>
                    </h1>

                    <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-2xl">
                        Gold dealers, real estate brokers, company service providers and accountants all have AML duties.
                        We explain what yours are, and then we help you get them done.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('guide') }}" class="inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3.5 rounded-xl shadow-sm shadow-brand-600/20 transition">
                            Start the 5-step guide →
                        </a>
                        <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-800 font-semibold px-6 py-3.5 rounded-xl transition">
                            Get free consultation
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium text-gray-500">
                        <span>📊 100% Compliance Rate</span>
                        <span>🎓 Certified Regulatory Experts</span>
                        <span>👥 25+ Years of Combined Experience</span>
                    </div>
                </div>

                {{-- The 5 obligations as a checklist; each links into the guide --}}
                <div class="hidden md:block lg:col-span-2 rounded-3xl bg-white border border-gray-100 shadow-xl shadow-brand-600/5 p-6 sm:p-8">
                    <p class="text-xs font-bold uppercase tracking-wider text-brand-600">Your AML checklist</p>
                    <ol class="mt-5 space-y-3">
                        @foreach (\App\Support\ComplianceGuide::topics() as $slug => $g)
                            <li>
                                <a href="{{ route('guide.show', $slug) }}" class="group flex items-center gap-4 rounded-xl p-2 -m-2 hover:bg-brand-50">
                                    <span class="shrink-0 inline-flex h-9 w-9 items-center justify-center rounded-full border-2 border-brand-200 text-sm font-bold text-brand-600 group-hover:bg-brand-600 group-hover:border-brand-600 group-hover:text-white transition">{{ $g['step'] }}</span>
                                    <span class="flex-1">
                                        <span class="block font-semibold text-gray-900">{{ $g['title'] }}</span>
                                        <span class="block text-xs text-gray-500">{{ $g['nav'] }}</span>
                                    </span>
                                    <span class="text-gray-300 group-hover:text-brand-600">→</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                    <p class="mt-6 pt-4 border-t border-gray-100 text-xs text-gray-500">Not sure which apply to you? <a href="{{ route('guide') }}" class="font-semibold text-brand-600">Pick your sector in the guide →</a></p>
                </div>
            </div>
        </div>

        {{-- 2. Sentinel ───────────────────────────────────────────────────── --}}
        <div x-cloak class="col-start-1 row-start-1 bg-slate-950 text-white transition-opacity duration-700"
             :class="i === 1 ? 'opacity-100 z-10' : 'opacity-0 pointer-events-none'" :inert="i !== 1"
             role="group" aria-roledescription="slide" aria-label="2 of 3: Sentinel AML screening">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-24 lg:pt-20 lg:pb-28 grid lg:grid-cols-5 gap-12 items-center">
                <div class="lg:col-span-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 border border-emerald-400/20 px-3 py-1 text-xs font-semibold text-emerald-300">
                        🔎 Sentinel AML screening
                    </span>

                    <h2 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.1]">
                        Screen anyone in <span class="text-sky-300">one search.</span>
                    </h2>

                    <p class="mt-6 text-lg text-slate-300 leading-relaxed max-w-2xl">
                        Check a customer, company, ship or crypto wallet against {{ $sentinel['total'] }} sanctions, PEP and
                        watch-list records, plus live adverse media. Then keep the case, the decision and the report in one place.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('sentinel') }}#try-free" class="inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-3.5 rounded-xl transition">
                            Try 5 free screenings →
                        </a>
                        <a href="{{ route('sentinel') }}" class="inline-flex items-center justify-center gap-2 border border-white/20 hover:border-white/40 text-white font-semibold px-6 py-3.5 rounded-xl transition">
                            See how Sentinel works
                        </a>
                    </div>

                    <p class="mt-10 text-sm text-slate-400">No card needed. Your login is emailed straight away.</p>
                </div>

                <div class="hidden md:grid lg:col-span-2 grid-cols-2 gap-px rounded-3xl overflow-hidden border border-white/10 bg-white/10">
                    @foreach ([
                        [$sentinel['total'], 'records checked in every search'],
                        [$sentinel['sanctions'], 'sanctions designations'],
                        [$sentinel['peps'], 'politically exposed persons'],
                        ['Nightly', 'list updates, checked before they go live'],
                    ] as [$num, $label])
                        <div class="bg-slate-900 p-6 sm:p-8">
                            <div class="text-3xl font-extrabold text-sky-300">{{ $num }}</div>
                            <div class="mt-1 text-xs text-slate-400">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. Compliance calendar ──────────────────────────────────────── --}}
        <div x-cloak class="col-start-1 row-start-1 bg-gradient-to-b from-amber-50 via-white to-white transition-opacity duration-700"
             :class="i === 2 ? 'opacity-100 z-10' : 'opacity-0 pointer-events-none'" :inert="i !== 2"
             role="group" aria-roledescription="slide" aria-label="3 of 3: Compliance calendar">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-24 lg:pt-20 lg:pb-28 grid lg:grid-cols-5 gap-12 items-center">
                <div class="lg:col-span-3">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold px-3 py-1">
                        📅 Free compliance calendar
                    </span>

                    <h2 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.1]">
                        Never miss a <span class="text-amber-600">compliance deadline.</span>
                    </h2>

                    <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-2xl">
                        Trade licence, Ejari, goAML registration, your annual risk assessment, staff training. Keep your own dates
                        in one place, right next to the {{ $calendarCount }} recurring AML obligations in our calendar.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        @auth('public')
                            <a href="{{ route('account.dashboard') }}" class="inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-3.5 rounded-xl transition">
                                Open my deadlines →
                            </a>
                        @else
                            <a href="{{ route('account.register') }}" class="inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6 py-3.5 rounded-xl transition">
                                Create your free tracker →
                            </a>
                        @endauth
                        <a href="{{ route('compliance-calendar') }}" class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-800 font-semibold px-6 py-3.5 rounded-xl transition">
                            View the calendar
                        </a>
                    </div>

                    <p class="mt-10 text-sm text-gray-500">Free, and it takes about a minute to set up.</p>
                </div>

                {{-- An illustration of the tracker, labelled as such --}}
                <div class="hidden md:block lg:col-span-2 rounded-3xl bg-white border border-gray-100 shadow-xl shadow-amber-600/5 p-6 sm:p-8">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">My Deadlines</p>
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 border border-gray-200 rounded px-1.5 py-0.5">Example</span>
                    </div>
                    <ul class="mt-5 space-y-3">
                        @foreach ([
                            ['Staff AML training', 'Overdue', 'bg-red-100 text-red-700'],
                            ['Trade licence renewal', 'Due in 18 days', 'bg-amber-100 text-amber-800'],
                            ['Ejari renewal', 'In 3 months', 'bg-emerald-100 text-emerald-700'],
                            ['Annual risk assessment review', 'In 5 months', 'bg-emerald-100 text-emerald-700'],
                        ] as [$label, $when, $badge])
                            <li class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 px-4 py-3">
                                <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
                                <span class="shrink-0 text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $badge }}">{{ $when }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-6 pt-4 border-t border-gray-100 text-xs text-gray-500">Overdue and upcoming dates are flagged so nothing slips.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Controls: labelled tabs (clearer than dots) plus arrows --}}
    <div class="absolute inset-x-0 bottom-6 z-20 flex justify-center px-4">
        <div class="flex items-center gap-1 rounded-full bg-white/90 backdrop-blur border border-gray-200 shadow-sm p-1">
            <button type="button" @click="pick(i - 1)" class="h-8 w-8 inline-flex items-center justify-center rounded-full text-gray-500 hover:bg-gray-100" aria-label="Previous slide">‹</button>
            @foreach (['AML guide', 'Sentinel', 'Calendar'] as $k => $tab)
                <button type="button" @click="pick({{ $k }})"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold transition"
                        :class="i === {{ $k }} ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        :aria-current="i === {{ $k }}" aria-label="Show slide {{ $k + 1 }}: {{ $tab }}">{{ $tab }}</button>
            @endforeach
            <button type="button" @click="pick(i + 1)" class="h-8 w-8 inline-flex items-center justify-center rounded-full text-gray-500 hover:bg-gray-100" aria-label="Next slide">›</button>
        </div>
    </div>
</section>

{{-- ── WHY IT MATTERS ─────────────────────────────────────────────────── --}}
<section class="bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid sm:grid-cols-3 gap-8">
        <div>
            <div class="text-3xl font-extrabold text-sky-300">AED 42M+</div>
            <p class="mt-1 text-sm text-slate-300">in fines issued to DNFBPs by the Ministry of Economy and Tourism in the first half of 2025 alone.</p>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-sky-300">1,063</div>
            <p class="mt-1 text-sm text-slate-300">AML violations found by the Ministry's inspection campaigns in those same six months.</p>
        </div>
        <div>
            <div class="text-3xl font-extrabold text-sky-300">24 hours</div>
            <p class="mt-1 text-sm text-slate-300">to freeze a sanctioned customer's funds once they're listed. <a href="{{ route('guide.show', 'screening') }}" class="text-white font-semibold hover:text-sky-300">How screening works →</a></p>
        </div>
    </div>
</section>

{{-- ── SERVICES OVERVIEW ──────────────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="max-w-2xl mb-12">
        <span class="text-brand-600 font-semibold text-sm">🏢 Who we work with</span>
        <h2 class="mt-2 text-3xl font-bold text-gray-900">Help that fits your sector</h2>
        <p class="mt-3 text-gray-600">A gold trader and a property broker face different risks and file different reports. Pick your sector to see how we help.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($services as $service)
            <a href="{{ route('services') }}#{{ $service['slug'] }}" class="group relative rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-lg hover:border-brand-100 transition">
                <div class="text-3xl">{{ $service['icon'] }}</div>
                <h3 class="mt-4 font-semibold text-gray-900 group-hover:text-brand-600">{{ $service['title'] }}</h3>
                <p class="mt-2 text-sm text-gray-500 line-clamp-2">{{ $service['summary'] }}</p>
                @unless($service['portal_backed'])
                    <span class="absolute top-4 right-4 text-[10px] font-semibold uppercase tracking-wide text-gray-400">Advisory</span>
                @endunless
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        <a href="{{ route('services') }}" class="text-brand-600 font-semibold text-sm hover:text-brand-700">View All Services →</a>
    </div>
</section>

{{-- ── TECHNOLOGY SERVICES TEASER ─────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="max-w-2xl mb-12">
        <span class="text-brand-600 font-semibold text-sm">💻 Technology Services</span>
        <h2 class="mt-2 text-3xl font-bold text-gray-900">We build the software, too</h2>
        <p class="mt-3 text-gray-600">We built the tools we use for our own clients. You can use them as well.</p>
    </div>

    <div class="grid sm:grid-cols-3 gap-5">
        @foreach ($techServices as $service)
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="text-3xl">{{ $service['icon'] }}</div>
                <h3 class="mt-4 font-semibold text-gray-900">{{ $service['title'] }}</h3>
                <p class="mt-2 text-sm text-gray-500 line-clamp-2">{{ $service['summary'] }}</p>
                @isset($service['link'])
                    <a href="{{ route($service['link']) }}" class="mt-3 inline-flex text-sm font-semibold text-brand-600 hover:text-brand-700">Learn more →</a>
                @endisset
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        <a href="{{ route('technology') }}" class="text-brand-600 font-semibold text-sm hover:text-brand-700">Explore Technology Services →</a>
    </div>
</section>

{{-- ── LIVE REGULATORY FEED ───────────────────────────────────────────── --}}
<section class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between flex-wrap gap-4 mb-10">
            <div>
                <span class="inline-flex items-center gap-1.5 text-red-600 font-semibold text-sm">🔴 Live Regulatory Feed</span>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">What's changing in UAE compliance</h2>
                <p class="mt-2 text-gray-600 max-w-xl">AML, sanctions and industry news that matters to UAE businesses, updated through the day.</p>
            </div>
            <a href="{{ route('news') }}" class="text-brand-600 font-semibold text-sm hover:text-brand-700 whitespace-nowrap">📘 Browse All Updates →</a>
        </div>

        @if ($news->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                Nothing here yet. Check back soon.
            </div>
        @else
            <div class="grid md:grid-cols-3 gap-5">
                @foreach ($news as $item)
                    <a href="{{ route('news.show', $item) }}" class="block rounded-2xl border border-gray-100 bg-white p-6 hover:shadow-lg transition">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-[11px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded {{ $item->categoryBadgeColor() }}">{{ $item->category }}</span>
                            @if ($item->isAiGenerated())
                                <span class="text-[11px] font-medium text-purple-500">✨ BA-Digest</span>
                            @endif
                        </div>
                        <h3 class="font-semibold text-gray-900 leading-snug">{{ $item->title }}</h3>
                        @if ($item->summary)<p class="mt-2 text-sm text-gray-500 line-clamp-3">{{ $item->summary }}</p>@endif
                        <p class="mt-4 text-xs text-gray-400">📅 {{ $item->published_at->format('d M Y') }} @if($item->source_name) · {{ $item->source_name }} @endif</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ── CTA ─────────────────────────────────────────────────────────────── --}}
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
    <h2 class="text-3xl font-bold text-gray-900">Not sure where to start?</h2>
    <p class="mt-3 text-gray-600">Tell us what your business does and we'll tell you what you need. The first conversation is free.</p>
    <a href="{{ route('contact') }}" class="mt-8 inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-semibold px-8 py-3.5 rounded-xl shadow-sm shadow-brand-600/20 transition">
        Book a free consultation
    </a>
</section>

@endsection
