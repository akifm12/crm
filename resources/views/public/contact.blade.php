@extends('layouts.public')

@section('title', 'Contact — Blue Arrow Management Consultants')
@section('meta_description', 'Get in touch with Blue Arrow Management Consultants for a free AML & regulatory compliance consultation.')

@section('content')

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="max-w-2xl">
        <span class="text-brand-600 font-semibold text-sm">Get in touch</span>
        <h1 class="mt-2 text-4xl font-extrabold text-gray-900">Ready to Simplify Your Compliance?</h1>
        <p class="mt-4 text-gray-600">Get started today and ensure your business stays compliant. Our team typically responds within one business day.</p>
    </div>

    <div class="mt-12 grid lg:grid-cols-5 gap-10">
        <div class="lg:col-span-3">
            @if (session('status'))
                <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5 bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
                @csrf
                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone (optional)</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">How can we help?</label>
                    <textarea name="message" rows="5" required
                              class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="w-full sm:w-auto bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                    Send Message
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-5">
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6">
                <h3 class="font-semibold text-gray-900 text-sm">📍 Office</h3>
                <p class="mt-2 text-sm text-gray-600">SRTI Park, University Road, Sharjah, UAE</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6">
                <h3 class="font-semibold text-gray-900 text-sm">✉️ Email</h3>
                <p class="mt-2 text-sm"><a href="mailto:info@bluearrow.ae" class="text-brand-600 hover:text-brand-700">info@bluearrow.ae</a></p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6">
                <h3 class="font-semibold text-gray-900 text-sm">📞 Phone</h3>
                <p class="mt-2 text-sm"><a href="tel:+971526461499" class="text-brand-600 hover:text-brand-700">+971 52 6461499</a></p>
            </div>
            <div class="rounded-2xl border border-brand-100 bg-brand-50 p-6">
                <h3 class="font-semibold text-brand-800 text-sm">Already a client?</h3>
                <p class="mt-2 text-sm text-brand-700">Log in to your portal instead.</p>
                <a href="{{ route('login') }}" class="mt-3 inline-block text-sm font-semibold text-brand-700 hover:text-brand-800">Client Login →</a>
            </div>
        </div>
    </div>
</section>

@endsection
