@extends('layouts.public')

@section('title', 'About Us | Blue Arrow Management Consultants')
@section('meta_description', 'Blue Arrow Management Consultants is a UAE AML and regulatory compliance consultancy that also builds compliance software, mobile apps and custom business systems.')

@section('content')

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <span class="text-brand-600 font-semibold text-sm">About Blue Arrow</span>
    <h1 class="mt-2 text-4xl font-extrabold text-gray-900">We help UAE businesses get AML compliance right</h1>
    <p class="mt-6 text-gray-600 leading-relaxed text-lg">
        Blue Arrow Management Consultants is a compliance consultancy based in Sharjah. We work with bullion dealers,
        real estate brokers, company service providers and accounting firms (the businesses the law calls DNFBPs)
        to set up AML programmes that actually work, and to keep them running.
    </p>
    <p class="mt-4 text-gray-600 leading-relaxed text-lg">
        We also write software. We built the platform we use for our own compliance clients, and we take on custom
        software and mobile app projects too, whether they're about compliance, accounting or something else
        entirely.
    </p>

    <div class="mt-12 grid sm:grid-cols-3 gap-5">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center">
            <div class="text-3xl font-extrabold text-brand-600">100%</div>
            <p class="mt-1 text-sm text-gray-500">Compliance Rate</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center">
            <div class="text-3xl font-extrabold text-brand-600">25+</div>
            <p class="mt-1 text-sm text-gray-500">Years of Combined Experience</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center">
            <div class="text-3xl font-extrabold text-brand-600">5</div>
            <p class="mt-1 text-sm text-gray-500">DNFBP Sectors Served</p>
        </div>
    </div>

    <div class="mt-16 grid sm:grid-cols-2 gap-8">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Compliance Advisory</h2>
            <ul class="mt-4 space-y-3 text-gray-600 text-sm leading-relaxed">
                <li>• AML/CFT policy design &amp; implementation for DNFBPs</li>
                <li>• KYC/KYB onboarding &amp; ongoing due diligence</li>
                <li>• Sanctions &amp; PEP screening against UN, OFAC, EU and UAE lists</li>
                <li>• goAML STR/SAR/DPMSR reporting support</li>
                <li>• Risk assessment &amp; UBO/beneficial ownership verification</li>
                <li>• Regulatory training &amp; ongoing compliance monitoring</li>
            </ul>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Technology &amp; Software Development</h2>
            <ul class="mt-4 space-y-3 text-gray-600 text-sm leading-relaxed">
                <li>• Our own compliance platform for onboarding, screening &amp; reporting</li>
                <li>• Custom accounting &amp; business software</li>
                <li>• Mobile app development, for compliance, accounting, or any other field</li>
                <li>• Web portals &amp; internal business tools</li>
                <li>• System integrations with existing business software</li>
                <li>• Ongoing support &amp; maintenance</li>
            </ul>
        </div>
    </div>

    <div class="mt-10 rounded-2xl border border-gray-100 bg-white p-6">
        <h2 class="text-lg font-bold text-gray-900">Qualified, and close to the rules</h2>
        <p class="mt-3 text-gray-600 text-sm leading-relaxed">
            Our consultants hold recognised compliance certifications and work with UAE supervisory requirements
            every day, from Ministry of Economy guidance to goAML reporting for the Financial Intelligence Unit.
            So when the rules change, our clients hear about it from us early, not from an inspector.
        </p>
    </div>

    <div class="mt-16 rounded-2xl bg-gray-50 border border-gray-100 p-8 text-center">
        <h2 class="text-xl font-bold text-gray-900">Want to get in touch?</h2>
        <p class="mt-2 text-gray-600 text-sm">Find our office, email and phone details on the contact page.</p>
        <a href="{{ route('contact') }}" class="mt-4 inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            Contact Us
        </a>
    </div>
</section>

@endsection
