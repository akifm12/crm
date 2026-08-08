@extends('layouts.public')

@section('title', 'Services — Blue Arrow Management Consultants')
@section('meta_description', 'Specialized AML & regulatory compliance services for bullion dealers, real estate, company service providers, accountants, capital markets and virtual asset businesses in the UAE.')

@section('content')

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="max-w-2xl">
        <span class="text-brand-600 font-semibold text-sm">🏢 Industry Expertise</span>
        <h1 class="mt-2 text-4xl font-extrabold text-gray-900">Specialized Compliance Solutions</h1>
        <p class="mt-4 text-gray-600 leading-relaxed">
            Blue Arrow operates a dedicated AML/CFT compliance platform used directly by DNFBP clients across five regulated sectors. We also provide advisory-only guidance for capital markets and virtual asset businesses.
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
                            <span class="text-[11px] font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded">Portal-Backed Onboarding</span>
                        @else
                            <span class="text-[11px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-500 px-2 py-0.5 rounded">Advisory Only</span>
                        @endif
                    </div>
                    <p class="mt-3 text-gray-600 leading-relaxed max-w-2xl">{{ $service['summary'] }}</p>

                    @if ($service['portal_backed'])
                        <p class="mt-3 text-sm text-gray-400">Includes KYC/KYB onboarding, sanctions screening, risk assessment and goAML reporting support through the Blue Arrow client portal.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-16 rounded-2xl bg-brand-600 px-8 py-10 text-center">
        <h2 class="text-2xl font-bold text-white">Not sure which service fits your business?</h2>
        <p class="mt-2 text-brand-50">Talk to our compliance team for a free consultation.</p>
        <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center justify-center bg-white text-brand-700 font-semibold px-6 py-3 rounded-xl">
            Get Free Consultation
        </a>
    </div>
</section>

@endsection
