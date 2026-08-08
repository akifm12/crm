@extends('layouts.public')

@section('title', 'About Us — Blue Arrow Management Consultants')
@section('meta_description', 'Blue Arrow Management Consultants is a UAE-based AML and regulatory compliance consultancy and technology company — building compliance software, mobile apps and custom business systems.')

@section('content')

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <span class="text-brand-600 font-semibold text-sm">About Blue Arrow</span>
    <h1 class="mt-2 text-4xl font-extrabold text-gray-900">A trusted compliance partner in a complex regulatory landscape</h1>
    <p class="mt-6 text-gray-600 leading-relaxed text-lg">
        Blue Arrow Management Consultants is a UAE-based AML and regulatory compliance consultancy, helping Designated
        Non-Financial Businesses and Professions (DNFBPs) — bullion dealers, real estate brokers, company service
        providers and accounting firms — build and run effective AML/CFT compliance programs.
    </p>
    <p class="mt-4 text-gray-600 leading-relaxed text-lg">
        We're also a technology company. Blue Arrow designs and builds our own software — including the RegTech
        platform behind our compliance service — and we take on custom software and mobile app development for
        clients, whether the need is compliance-related, accounting-related, or in a completely different field.
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
                <li>• Our own RegTech platform — client onboarding, screening &amp; reporting</li>
                <li>• Custom accounting &amp; business software</li>
                <li>• Mobile app development, for compliance, accounting, or any other field</li>
                <li>• Web portals &amp; internal business tools</li>
                <li>• System integrations with existing business software</li>
                <li>• Ongoing support &amp; maintenance</li>
            </ul>
        </div>
    </div>

    <div class="mt-10 rounded-2xl border border-gray-100 bg-white p-6">
        <h2 class="text-lg font-bold text-gray-900">Certified &amp; regulator-aligned</h2>
        <p class="mt-3 text-gray-600 text-sm leading-relaxed">
            Our consultants hold recognized regulatory compliance certifications and work directly with UAE
            DNFBP supervisory requirements — including Ministry of Economy guidance and Financial Intelligence
            Unit (FIU) goAML reporting obligations — so our clients stay ahead of evolving requirements rather
            than reacting to them.
        </p>
    </div>

    <div class="mt-16 rounded-2xl bg-gray-50 border border-gray-100 p-8">
        <h2 class="text-xl font-bold text-gray-900">Visit us</h2>
        <p class="mt-3 text-gray-600 text-sm">SRTI Park, University Road, Sharjah, UAE</p>
        <p class="mt-1 text-gray-600 text-sm"><a href="mailto:info@bluearrow.ae" class="text-brand-600 hover:text-brand-700">info@bluearrow.ae</a> &nbsp;·&nbsp; <a href="tel:+971526461499" class="text-brand-600 hover:text-brand-700">+971 52 6461499</a></p>
    </div>
</section>

@endsection
