@extends('layouts.public')

@section('title', 'Blue Arrow Management Consultants — Navigate UAE Regulations with Confidence')
@section('meta_description', 'Expert AML & regulatory compliance guidance for bullion, real estate, company service providers and accounting firms across the UAE.')

@section('content')

{{-- ── HERO ────────────────────────────────────────────────────────────── --}}
<section class="relative overflow-hidden bg-gradient-to-b from-brand-50 via-white to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 lg:pt-24 lg:pb-28">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 text-brand-700 text-xs font-semibold px-3 py-1">
                ✨ Trusted name in Regulatory Compliance
            </span>

            <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.1]">
                Navigate UAE Regulations <span class="text-brand-600">with Confidence</span>
            </h1>

            <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-2xl">
                Your trusted compliance partner in a complex regulatory landscape. Expert guidance for bullion, real estate, company services and accounting sectors.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3.5 rounded-xl shadow-sm shadow-brand-600/20 transition">
                    Get Free Consultation →
                </a>
                <a href="{{ route('services') }}" class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-800 font-semibold px-6 py-3.5 rounded-xl transition">
                    Explore Services
                </a>
            </div>

            <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium text-gray-500">
                <span>📊 100% Compliance Rate</span>
                <span>🎓 Certified Regulatory Experts</span>
                <span>👥 25+ Years of Combined Experience</span>
            </div>
        </div>
    </div>

    <div class="absolute -right-24 top-24 h-72 w-72 rounded-full bg-brand-100 blur-3xl opacity-60 hidden lg:block"></div>
</section>

{{-- ── SERVICES OVERVIEW ──────────────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="max-w-2xl mb-12">
        <span class="text-brand-600 font-semibold text-sm">🏢 Industry Expertise</span>
        <h2 class="mt-2 text-3xl font-bold text-gray-900">Specialized Compliance Solutions</h2>
        <p class="mt-3 text-gray-600">Select your industry to discover tailored regulatory services designed for your specific compliance needs.</p>
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
        <h2 class="mt-2 text-3xl font-bold text-gray-900">Software That Powers Compliance</h2>
        <p class="mt-3 text-gray-600">We build and run the software behind our own compliance service — and make it available to clients.</p>
    </div>

    <div class="grid sm:grid-cols-3 gap-5">
        @foreach ($techServices as $service)
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="text-3xl">{{ $service['icon'] }}</div>
                <h3 class="mt-4 font-semibold text-gray-900">{{ $service['title'] }}</h3>
                <p class="mt-2 text-sm text-gray-500 line-clamp-2">{{ $service['summary'] }}</p>
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
                <h2 class="mt-2 text-3xl font-bold text-gray-900">Stay Ahead of Regulatory Changes</h2>
                <p class="mt-2 text-gray-600 max-w-xl">Continuously updated AML, sanctions and industry news relevant to UAE DNFBPs.</p>
            </div>
            <a href="{{ route('news') }}" class="text-brand-600 font-semibold text-sm hover:text-brand-700 whitespace-nowrap">📘 Browse All Updates →</a>
        </div>

        @if ($news->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                No updates published yet — check back soon.
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
                        <p class="mt-2 text-sm text-gray-500 line-clamp-3">{{ $item->summary }}</p>
                        <p class="mt-4 text-xs text-gray-400">📅 {{ $item->published_at->format('d M Y') }} @if($item->source_name) · {{ $item->source_name }} @endif</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ── CTA ─────────────────────────────────────────────────────────────── --}}
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
    <h2 class="text-3xl font-bold text-gray-900">Ready to Simplify Your Compliance?</h2>
    <p class="mt-3 text-gray-600">Get started today and ensure your business stays compliant.</p>
    <a href="{{ route('contact') }}" class="mt-8 inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-semibold px-8 py-3.5 rounded-xl shadow-sm shadow-brand-600/20 transition">
        Schedule Consultation
    </a>
</section>

@endsection
