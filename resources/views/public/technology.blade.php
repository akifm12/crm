@extends('layouts.public')

@section('title', 'Technology Services — Blue Arrow Management Consultants')
@section('meta_description', 'Blue Arrow\'s technology arm: a KYC/client onboarding portal, bullion accounting software, and custom software development.')

@section('content')

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <span class="text-brand-600 font-semibold text-sm">💻 Technology Services</span>
    <h1 class="mt-2 text-4xl font-extrabold text-gray-900">Software That Powers Compliance</h1>
    <p class="mt-4 text-gray-600 max-w-2xl">
        Blue Arrow isn't just consultants — we build and run the software behind our own compliance service, and we
        make it available to clients who want the technology, the advisory, or both.
    </p>

    <div class="mt-14 grid gap-6">
        @foreach ($techServices as $service)
            <div class="flex flex-col sm:flex-row gap-6 rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
                <div class="text-4xl shrink-0">{{ $service['icon'] }}</div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-xl font-bold text-gray-900">{{ $service['title'] }}</h2>
                        <span class="text-[11px] font-semibold uppercase tracking-wide {{ $service['badge'] === 'Core Product' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }} px-2 py-0.5 rounded">
                            {{ $service['badge'] }}
                        </span>
                    </div>
                    <p class="mt-3 text-gray-600 leading-relaxed max-w-2xl">{{ $service['summary'] }}</p>

                    <ul class="mt-4 grid sm:grid-cols-2 gap-x-6 gap-y-2">
                        @foreach ($service['features'] as $feature)
                            <li class="text-sm text-gray-500 flex gap-2">
                                <span class="text-brand-500">✓</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-16 rounded-2xl bg-brand-600 px-8 py-10 text-center">
        <h2 class="text-2xl font-bold text-white">Want to see the platform, or discuss a custom build?</h2>
        <p class="mt-2 text-brand-50">Talk to our team about the right fit for your business.</p>
        <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center justify-center bg-white text-brand-700 font-semibold px-6 py-3 rounded-xl">
            Contact Us
        </a>
    </div>
</section>

@endsection
