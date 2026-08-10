@extends('layouts.public')

@section('title', 'Privacy Policy — Blue Arrow Management Consultants')
@section('meta_description', 'How Blue Arrow Management Consultants collects, uses and protects personal data across our website, client portal and mobile app.')

@section('content')

<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <span class="text-brand-600 font-semibold text-sm">⚖️ Legal &amp; Compliance</span>
    <h1 class="mt-2 text-4xl font-extrabold text-gray-900">Privacy Policy</h1>
    <p class="mt-6 text-gray-600 leading-relaxed text-lg">
        Read on to review how we handle your data, and know your rights.
    </p>

    <div class="mt-12 space-y-10">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Introduction</h2>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                Blue Arrow Management Consultants FZC ("we", "us", or "our") operates the bluearrow.ae website, the
                Blue Arrow mobile application, and associated services (the "Service"). This page informs you of our
                policies regarding the collection, use and protection of personal data when you use our Service.
            </p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900">Information We Collect</h2>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                We collect only the minimum information required to provide our compliance, screening and account
                features. Information collected may include:
            </p>
            <ul class="mt-4 space-y-3 text-gray-600 text-sm leading-relaxed">
                <li>• Email address (for optional account access and verification)</li>
                <li>• Screening inputs such as names, dates of birth, or nationality (entered voluntarily)</li>
                <li>• Compliance deadlines you choose to track (e.g. trade licence, Ejari, passport, Emirates ID renewal dates)</li>
                <li>• Basic device and application diagnostic data</li>
            </ul>
            <p class="mt-4 text-gray-600 text-sm leading-relaxed">
                We do not collect location data, contact lists, payment information, or government-issued
                identification numbers.
            </p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900">Use of Information</h2>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                Blue Arrow Management Consultants uses collected information strictly for the following purposes:
            </p>
            <ul class="mt-4 space-y-3 text-gray-600 text-sm leading-relaxed">
                <li>• To provide sanctions screening and compliance tools</li>
                <li>• To verify users where account/login functionality is enabled</li>
                <li>• To send compliance alerts and updates you've opted into</li>
                <li>• To maintain system security and prevent abuse</li>
                <li>• To improve application stability and performance</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900">Sanctions &amp; Screening Data</h2>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                Screening inputs are processed against consolidated sanctions and watchlists sourced from public and
                regulatory authorities.
            </p>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                Screening results are provided for informational and compliance-support purposes only and do not
                constitute legal, regulatory, or enforcement determinations. Users remain responsible for independent
                verification and due diligence.
            </p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900">Data Storage &amp; Security</h2>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                We apply reasonable technical and organizational safeguards to protect user data. Authentication
                tokens are stored securely on the user's device and transmitted using secure communication protocols.
            </p>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
                We do not sell, rent, or trade personal data to third parties.
            </p>
        </div>
    </div>

    <div class="mt-16 rounded-2xl bg-gray-50 border border-gray-100 p-8 text-center">
        <h2 class="text-xl font-bold text-gray-900">Questions about this policy?</h2>
        <p class="mt-2 text-gray-600 text-sm">
            <strong>Email:</strong> <a href="mailto:info@bluearrow.ae" class="text-brand-600 hover:text-brand-700">info@bluearrow.ae</a>
            &nbsp;·&nbsp;
            <strong>Website:</strong> <a href="{{ route('home') }}" class="text-brand-600 hover:text-brand-700">bluearrow.ae</a>
        </p>
        <a href="{{ route('contact') }}" class="mt-4 inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            Contact Us
        </a>
    </div>
</section>

@endsection
