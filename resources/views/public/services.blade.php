@extends('layouts.public')

@section('title', 'Services | Blue Arrow Management Consultants')
@section('meta_description', 'Specialized AML & regulatory compliance services for bullion dealers, real estate, company service providers, accountants, capital markets and virtual asset businesses in the UAE.')

@section('content')

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="max-w-2xl">
        <span class="text-brand-600 font-semibold text-sm">🏢 Our services</span>
        <h1 class="mt-2 text-4xl font-extrabold text-gray-900">Compliance help for your sector</h1>
        <p class="mt-4 text-gray-600 leading-relaxed">
            For the sectors below, you get our advice and our client portal, which our clients use every day for onboarding, screening and reporting. For capital markets and virtual asset businesses, we offer advice only.
        </p>
    </div>

    <div class="mt-14 grid gap-6">
        @foreach ($services as $service)
            <div id="{{ $service['slug'] }}" class="scroll-mt-28 flex flex-col sm:flex-row gap-6 rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
                <div class="text-4xl shrink-0">{{ $service['icon'] }}</div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-xl font-bold text-gray-900">{{ $service['title'] }}</h2>
                        @if ($service['portal_backed'])
                            <span class="text-[11px] font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded">Includes client portal</span>
                        @else
                            <span class="text-[11px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-500 px-2 py-0.5 rounded">Advice only</span>
                        @endif
                    </div>
                    <p class="mt-3 text-gray-600 leading-relaxed max-w-2xl">{{ $service['summary'] }}</p>

                    @if ($service['portal_backed'])
                        <p class="mt-3 text-sm text-gray-400">Comes with customer onboarding, sanctions screening, risk assessment and goAML reporting support, all in the Blue Arrow client portal.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-16 rounded-2xl bg-brand-600 px-8 py-10 text-center">
        <h2 class="text-2xl font-bold text-white">Not sure which service fits your business?</h2>
        <p class="mt-2 text-brand-50">Talk it through with us. The first consultation is free.</p>
        <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center justify-center bg-white text-brand-700 font-semibold px-6 py-3 rounded-xl">
            Book a free consultation
        </a>
    </div>
</section>

@endsection
