@extends('layouts.public')

@section('title', 'Sentinel AML Screening Software | Blue Arrow')
@section('meta_description', 'Sentinel screens customers against official sanctions lists (UN, UAE, OFAC, UK, EU and more), ' . $stats['peps'] . ' politically exposed persons and live adverse media, with ongoing monitoring and an API. Built in the UAE by Blue Arrow. From AED 4,000 a year.')

@php
    $trial   = session('sentinel_trial');
    $enquiry = session('sentinel_enquiry');
    // Both forms share field names, so only refill the one that was submitted
    $tOld = fn ($k) => ($trial || $errors->trial->any()) ? old($k) : '';
    $eOld = fn ($k, $d = '') => ($enquiry || $errors->enquiry->any()) ? old($k, $d) : $d;
    $input = 'w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500';
@endphp

@section('content')

{{-- ── HERO ─────────────────────────────────────────────────────────────── --}}
<section class="bg-slate-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-24 grid lg:grid-cols-5 gap-12 items-center">
        <div class="lg:col-span-3">
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 border border-emerald-400/20 px-3 py-1 text-xs font-semibold tracking-wide text-emerald-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> SENTINEL AML · BY BLUE ARROW
            </span>
            <h1 class="mt-6 text-4xl sm:text-5xl font-extrabold leading-tight">
                Know exactly who you're<br class="hidden sm:block"> doing business with.
            </h1>
            <p class="mt-5 text-lg text-slate-300 max-w-xl">
                One search checks a person, company, ship or crypto wallet against the official sanctions lists,
                politically exposed persons and live adverse media. Then Sentinel takes the case through review and
                gives you a report your auditor can read.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#try-free" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-3 rounded-xl shadow-sm">Try 5 free screenings</a>
                <a href="#pricing" class="border border-white/20 hover:border-white/40 text-white font-semibold px-6 py-3 rounded-xl">See plans</a>
            </div>
            <p class="mt-4 text-sm text-slate-400">Already a customer? <a href="https://aml.bluearrow.ae" class="text-sky-300 hover:text-sky-200 font-medium">Sign in to Sentinel →</a></p>
            @if ($listStatus)
                <a href="{{ $listStatus['latest_verified']['url'] }}" target="_blank" rel="noopener"
                   class="mt-6 inline-flex items-start gap-2 rounded-xl bg-emerald-500/10 border border-emerald-400/30 px-4 py-2.5 text-sm text-emerald-200 hover:border-emerald-400/60">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>Lists up to date: every entry in the latest UN/EOCN notice
                        ({{ $listStatus['latest_verified']['reference'] ?? 'update' }},
                        {{ \Carbon\Carbon::parse($listStatus['latest_verified']['change_date'])->format('j M Y') }})
                        is verified in Sentinel.</span>
                </a>
            @endif
        </div>

        <div class="lg:col-span-2 grid grid-cols-2 gap-px rounded-2xl overflow-hidden border border-white/10 bg-white/10">
            @foreach ([
                [$stats['sanctions'], 'sanctions designations'],
                [$stats['peps'], 'politically exposed persons'],
                ['10', 'official sources, from the UN to the UAE'],
                ['Nightly', 'list updates'],
            ] as [$num, $label])
                <div class="bg-slate-900 p-6">
                    <div class="text-2xl sm:text-3xl font-extrabold text-sky-300">{{ $num }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── FEATURES ─────────────────────────────────────────────────────────── --}}
<section id="features" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="max-w-2xl">
        <span class="text-brand-600 font-semibold text-sm">What it does</span>
        <h2 class="mt-2 text-3xl font-bold text-gray-900">Screening, review and records in one place</h2>
        <p class="mt-3 text-gray-600">Built for MLROs and compliance officers at UAE businesses who need to screen properly without a bank-sized budget.</p>
    </div>

    <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ([
            ['🔍', 'Sanctions & PEP screening', 'One search covers the UN, UAE, US, UK, EU, Swiss, Canadian and Australian sanctions lists, US export controls and politically exposed persons. Fuzzy matching catches spelling variants, aliases and transliterations.'],
            ['📰', 'Adverse media in the same search', 'Every screening also checks news coverage for financial crime, fraud, corruption and terrorism-financing signals, so reputational risk shows up alongside the list hits.'],
            ['🗂️', 'Case management & review', 'Each screening becomes a case. Hits above the threshold, or any adverse media, are marked for enhanced due diligence and go to review, where your MLRO records the decision and the reasons.'],
            ['📄', 'Audit-ready case reports', 'One report per case with the subject, the lists checked, every hit, the decision and the reviewer\'s notes. Open it in your browser and save it as a PDF for your file.'],
            ['🔁', 'Ongoing monitoring', 'On the Complete plan, existing customers are re-screened daily, weekly or monthly. You are alerted only when a new match appears, and a cleared false positive stays cleared.'],
            ['🔌', 'API & webhooks', 'On the Complete plan, screen, monitor and fetch reports from your own onboarding system, with test and live keys and signed webhooks.'],
            ['📋', 'Batch screening', 'Paste up to 500 names at once to screen an existing customer book or a supplier list in one go.'],
            ['₿', 'Crypto wallet screening', 'Check BTC, ETH, TRX (incl. USDT-TRC20) and XRP addresses against wallets named in US Treasury (OFAC) sanctions listings.'],
            ['🚢', 'Vessel screening', 'Screen ships by name or IMO number against vessels on the official sanctions lists. Handy if you deal with traders or shipping companies.'],
        ] as [$icon, $title, $text])
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="text-2xl">{{ $icon }}</div>
                <h3 class="mt-3 font-semibold text-gray-900">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $text }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-2xl bg-brand-50 border border-brand-100 p-6 flex flex-col sm:flex-row gap-4 sm:items-center">
        <div class="text-2xl">🔗</div>
        <p class="text-sm text-gray-700 flex-1">
            <strong class="text-gray-900">The same engine runs inside our KYC portal.</strong>
            If Blue Arrow handles your onboarding, your clients are screened by Sentinel and the results, decisions and reports are identical in both places.
            <a href="{{ route('technology') }}" class="text-brand-600 font-semibold hover:text-brand-700 whitespace-nowrap">See the KYC portal →</a>
        </p>
    </div>
</section>

{{-- ── DATA COVERAGE ────────────────────────────────────────────────────── --}}
<section id="coverage" class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <span class="text-brand-600 font-semibold text-sm">Data coverage</span>
            <h2 class="mt-2 text-3xl font-bold text-gray-900">Straight from the official publishers, refreshed every night</h2>
            <p class="mt-3 text-gray-600">
                Every night we download each list from the authority that publishes it and check it before it goes live.
                If a source looks incomplete, yesterday's data stays in place and our team gets an alert.
            </p>
            <p class="mt-3 text-gray-600">
                We also check every sanctions notice the UAE's Executive Office (EOCN) issues: each name it adds, amends or removes is
                verified in Sentinel after the next update, and our team is alerted if anything doesn't match.
            </p>
        </div>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['UN', 'UN Security Council', 'The Consolidated List, taken directly from the UN.'],
                [$stats['uae'], 'UAE Local Terrorist List', 'Published by the Executive Office for Control & Non-Proliferation (EOCN).'],
                ['OFAC', 'US Treasury', 'The SDN list and the other OFAC lists, including sanctioned crypto wallets and vessels.'],
                ['UK · EU', 'UK and European Union', 'The UK Sanctions List (FCDO) and the EU Financial Sanctions list.'],
                ['CH · CA · AU', 'Switzerland, Canada, Australia', 'SECO, the Canadian SEMA list and the Australian DFAT Consolidated List.'],
                [$stats['export'], 'US export controls', 'The BIS Entity List, Denied Persons and the other US export-control lists.'],
                [$stats['peps'], 'Politically exposed persons', 'Heads of state and government, ministers, parliamentarians, ambassadors, senior judges and rulers worldwide, updated weekly.'],
                ['Live', 'Adverse media', 'Searched live every time you screen, not from an old snapshot.'],
            ] as [$num, $title, $text])
                <div class="rounded-2xl bg-white border border-gray-100 p-5 shadow-sm">
                    <div class="text-xl font-extrabold text-brand-600">{{ $num }}</div>
                    <div class="mt-1 font-semibold text-gray-900 text-sm">{{ $title }}</div>
                    <p class="mt-1 text-xs text-gray-500 leading-relaxed">{{ $text }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-xs text-gray-500">
            Need wider coverage? Premium data extends screening into more industries, such as crypto (ransomware-linked and seized wallets) and shipping (shadow-fleet and detained vessels), and adds 100+ further national lists, wanted and debarment lists and regulatory warnings. Available on request.
            Counts are from the latest nightly import and rounded down.
        </p>
    </div>
</section>

{{-- ── HOW IT WORKS ─────────────────────────────────────────────────────── --}}
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center max-w-2xl mx-auto">
        <span class="text-brand-600 font-semibold text-sm">How it works</span>
        <h2 class="mt-2 text-3xl font-bold text-gray-900">From name to signed-off case</h2>
    </div>
    <ol class="mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ([
            ['Enter the subject', 'A person or a company. Add whatever else you have, like nationality, date of birth or ID numbers. Or paste in a whole list.'],
            ['Sentinel screens', 'Lists and adverse media are searched together and every hit is scored for how closely it matches.'],
            ['Review the case', 'Clear results are recorded as such. Strong matches or adverse media go to EDD for your MLRO to review and decide.'],
            ['Keep the record', 'Save the case report for your file and, on Complete, put the customer on ongoing monitoring.'],
        ] as $i => [$title, $text])
            <li class="relative rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-bold">{{ $i + 1 }}</span>
                <h3 class="mt-4 font-semibold text-gray-900">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $text }}</p>
            </li>
        @endforeach
    </ol>
</section>

{{-- ── WHO IT'S FOR ─────────────────────────────────────────────────────── --}}
<section class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <span class="text-brand-600 font-semibold text-sm">Who it's for</span>
            <h2 class="mt-2 text-3xl font-bold text-gray-900">Businesses with a screening obligation</h2>
            <p class="mt-3 text-gray-600">Under the UAE's AML/CFT framework, financial institutions and DNFBPs must screen customers and counterparties when they take them on, and keep screening them after that.</p>
        </div>
        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ([
                ['💎', 'Dealers in precious metals & stones', 'Screen buyers and suppliers, especially on cash transactions of AED 55,000 and above.'],
                ['🏠', 'Real estate agents & developers', 'Screen buyers, sellers and investors before the deal. Real estate is a high-risk sector in the UAE.'],
                ['🏢', 'Corporate service providers', 'Screen shareholders, directors and beneficial owners of the companies you form or administer.'],
                ['⚖️', 'Accountants, auditors & lawyers', 'Screen clients when you manage their funds or assets, or act for them in transactions.'],
                ['₿', 'Virtual asset service providers', 'Screen customers and check counterparty wallet addresses.'],
                ['💳', 'Payment, fintech & finance firms', 'Screen customers at onboarding and monitor them for changes in status.'],
            ] as [$icon, $title, $text])
                <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm flex gap-4">
                    <div class="text-2xl shrink-0">{{ $icon }}</div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-6 text-xs text-gray-500">Sentinel is a screening tool that supports your compliance programme; it does not replace your own risk assessment or constitute legal advice.</p>
    </div>
</section>

{{-- ── PRICING ──────────────────────────────────────────────────────────── --}}
<section id="pricing" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center max-w-2xl mx-auto">
        <span class="text-brand-600 font-semibold text-sm">Pricing</span>
        <h2 class="mt-2 text-3xl font-bold text-gray-900">Three plans, unlimited screening</h2>
        <p class="mt-3 text-gray-600">No per-screen charges. Billed annually in AED.</p>
    </div>

    @php($common = ['Unlimited screenings*', 'Official sanctions lists & PEPs', 'Adverse media in every screen', 'Individuals, companies, vessels & crypto wallets', 'Batch screening', 'Case management & MLRO review', 'Case reports (save as PDF)', 'Multi-user teams with roles'])
    <div class="mt-12 grid md:grid-cols-3 gap-6">
        @foreach ([
            ['Essential', 'AED 4,000', 'per year', 'Screening at onboarding and a clean record, in the Sentinel app.', false, [['Ongoing monitoring', false], ['API access', false]]],
            ['Complete', 'AED 5,000', 'per year', 'Everything in Essential, plus ongoing monitoring and the Sentinel API.', true, [['Ongoing monitoring (daily / weekly / monthly)', true], ['API access with webhooks*', true]]],
            ['Corporate API', 'Contact us', '', 'High-volume screening built into your platform, priced to your volumes.', false, [['Everything in Complete', true], ['Volume pricing and higher API limits', true], ['Premium data on request (crypto, shipping and more)', true]]],
        ] as [$plan, $price, $per, $desc, $featured, $extras])
            <div class="relative rounded-2xl bg-white p-8 flex flex-col {{ $featured ? 'border-2 border-brand-600 shadow-lg' : 'border border-gray-200 shadow-sm' }}">
                @if ($featured)
                    <span class="absolute -top-3 left-8 bg-brand-600 text-white text-[11px] font-semibold tracking-wide px-3 py-1 rounded-full">RECOMMENDED</span>
                @endif
                <h3 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">{{ $plan }}</h3>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold text-gray-900">{{ $price }}</span>
                    @if ($per)<span class="text-sm text-gray-500">{{ $per }}</span>@endif
                </div>
                <p class="mt-3 text-sm text-gray-600">{{ $desc }}</p>
                <ul class="mt-6 space-y-2 text-sm flex-1">
                    @if ($plan !== 'Corporate API')
                        @foreach ($common as $f)
                            <li class="flex gap-2 text-gray-700"><span class="text-emerald-500">✓</span>{{ $f }}</li>
                        @endforeach
                    @endif
                    @foreach ($extras as [$f, $yes])
                        <li class="flex gap-2 {{ $yes ? 'text-gray-900 font-semibold' : 'text-gray-400' }}">
                            <span class="{{ $yes ? 'text-emerald-500' : '' }}">{{ $yes ? '✓' : '✕' }}</span>{{ $f }}
                        </li>
                    @endforeach
                </ul>
                @if ($plan === 'Corporate API')
                    <a href="#enquire" @click="$dispatch('pick-plan', 'Corporate API')"
                       class="mt-8 block text-center font-semibold px-6 py-3 rounded-xl border border-gray-300 hover:border-brand-600 text-gray-800">Talk to us</a>
                @else
                    <a href="#try-free"
                       class="mt-8 block text-center font-semibold px-6 py-3 rounded-xl {{ $featured ? 'bg-brand-600 hover:bg-brand-700 text-white' : 'border border-gray-300 hover:border-brand-600 text-gray-800' }}">
                        Start free, then choose {{ $plan }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-2xl bg-gray-50 border border-gray-100 p-5 text-xs text-gray-500 leading-relaxed">
        <strong class="text-gray-700">* Fair use.</strong>
        Unlimited screenings on Essential and Complete, and API access on Complete, are for screening the subscriber's own customers,
        counterparties and staff in the normal course of a single business. They may not be resold, shared with other businesses or used
        to screen on behalf of third parties. API keys allow up to 60 screenings a minute. Where usage regularly goes well beyond normal levels
        for a business of the subscriber's size, we may ask to move the account to the Corporate API plan.
        Prices exclude 5% VAT. Plans are billed annually in advance and activate on payment.
    </div>
</section>

{{-- ── GET STARTED / API ────────────────────────────────────────────────── --}}
<section id="api" class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12 items-start">
        <div>
            <span class="text-brand-600 font-semibold text-sm">Getting started</span>
            <h2 class="mt-2 text-3xl font-bold text-gray-900">From free trial to live, on your own schedule</h2>
            <ol class="mt-8 space-y-5">
                @foreach ([
                    ['Try it free', '5 real screenings against the live data. Your login arrives by email straight away.'],
                    ['Test the API', 'Building an integration? Create a sandbox key in Sentinel: 14 days and 50 test screenings, free.'],
                    ['Choose a plan', 'Pick Essential or Complete in Sentinel and enter your billing details. We email the invoice.'],
                    ['Go live', 'Your plan switches on when the invoice is paid. On Complete, create your live API key and you\'re done.'],
                ] as $i => [$title, $text])
                    <li class="flex gap-4">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-bold">{{ $i + 1 }}</span>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
        <div class="rounded-2xl bg-slate-950 text-white p-8">
            <h3 class="text-xl font-bold">The Sentinel API</h3>
            <p class="mt-2 text-sm text-slate-300">Screen customers from your own onboarding flow, keep them under monitoring and pull the report for your records. Included in Complete.</p>
            <ul class="mt-5 space-y-2 text-sm text-slate-200">
                <li class="flex gap-2"><span class="text-emerald-400">✓</span>Clear or review required in seconds, with every match</li>
                <li class="flex gap-2"><span class="text-emerald-400">✓</span>Record your MLRO's review decisions</li>
                <li class="flex gap-2"><span class="text-emerald-400">✓</span>Monitoring alerts by signed webhook</li>
                <li class="flex gap-2"><span class="text-emerald-400">✓</span>Compliance reports and secure report links</li>
                <li class="flex gap-2"><span class="text-emerald-400">✓</span>Test and live keys, safe retries, Postman-ready reference</li>
            </ul>
            <pre class="mt-6 overflow-x-auto rounded-xl bg-slate-900 border border-white/10 p-4 text-xs text-sky-200"><code>curl https://aml.bluearrow.ae/api/v1/screenings \
  -H "Authorization: Bearer sk_test_..." \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane Doe","country":"UAE"}'</code></pre>
            <a href="https://aml.bluearrow.ae/api/v1/docs" target="_blank" rel="noopener" class="mt-6 inline-block font-semibold text-sky-300 hover:text-sky-200">Read the API documentation →</a>
        </div>
    </div>
</section>

{{-- ── TRY FREE + ENQUIRE ───────────────────────────────────────────────── --}}
<section class="bg-slate-950 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-8">

        {{-- Free trial --}}
        <div id="try-free" class="scroll-mt-24 rounded-2xl bg-white p-8">
            <h2 class="text-2xl font-bold text-gray-900">Try Sentinel free</h2>
            <p class="mt-2 text-sm text-gray-600">5 real screenings against the live data, plus a free API sandbox. No card, no commitment. We email your login straight away.</p>

            @if ($trial && $trial['status'] === 'ok')
                <div class="mt-6 rounded-xl bg-emerald-50 border border-emerald-200 p-5 text-sm text-emerald-800">
                    <strong class="block text-emerald-900">Account created ✓</strong>
                    {{ $trial['message'] }}
                    <a href="https://aml.bluearrow.ae" class="mt-3 inline-block font-semibold text-emerald-700 hover:text-emerald-900">Go to Sentinel →</a>
                </div>
            @else
                @if ($trial)
                    <div class="mt-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $trial['message'] }}</div>
                @endif
                <form method="POST" action="{{ route('sentinel.trial') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="t-name" class="block text-sm font-medium text-gray-700">Full name *</label>
                        <input id="t-name" name="name" type="text" required maxlength="150" value="{{ $tOld('name') }}" class="mt-1 {{ $input }}">
                        @error('name', 'trial')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="t-email" class="block text-sm font-medium text-gray-700">Work email *</label>
                        <input id="t-email" name="email" type="email" required maxlength="150" value="{{ $tOld('email') }}" class="mt-1 {{ $input }}">
                        @error('email', 'trial')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="t-company" class="block text-sm font-medium text-gray-700">Company</label>
                            <input id="t-company" name="company" type="text" maxlength="150" value="{{ $tOld('company') }}" class="mt-1 {{ $input }}">
                        </div>
                        <div>
                            <label for="t-phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input id="t-phone" name="phone" type="tel" maxlength="40" value="{{ $tOld('phone') }}" placeholder="+971" class="mt-1 {{ $input }}">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-3 rounded-xl">Get 5 free screenings</button>
                    <p class="text-xs text-gray-500 text-center">By signing up you agree to our <a href="https://sentinel.bluearrow.ae/privacy.html" class="underline">Sentinel privacy policy</a>.</p>
                </form>
            @endif
        </div>

        {{-- Enquiry --}}
        <div id="enquire" class="scroll-mt-24 rounded-2xl bg-white p-8"
             x-data="{ plan: '{{ $eOld('plan', 'Complete') }}' }" @pick-plan.window="plan = $event.detail">
            <h2 class="text-2xl font-bold text-gray-900">Talk to us about a plan</h2>
            <p class="mt-2 text-sm text-gray-600">Tell us a little about your business and we'll get back to you within one business day to set up your account and walk your team through it.</p>

            @if ($enquiry && $enquiry['status'] === 'ok')
                <div class="mt-6 rounded-xl bg-emerald-50 border border-emerald-200 p-5 text-sm text-emerald-800">
                    <strong class="block text-emerald-900">Request received ✓</strong>
                    {{ $enquiry['message'] }}
                </div>
            @else
                <form method="POST" action="{{ route('sentinel.enquire') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <span class="block text-sm font-medium text-gray-700">Interested in</span>
                        <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach (['Essential', 'Complete', 'Corporate API', 'Not sure'] as $p)
                                <label class="cursor-pointer rounded-lg border px-3 py-2 text-center text-sm font-medium transition"
                                       :class="plan === '{{ $p }}' ? 'border-brand-600 bg-brand-50 text-brand-700' : 'border-gray-300 text-gray-600 hover:border-gray-400'">
                                    <input type="radio" name="plan" value="{{ $p }}" x-model="plan" class="sr-only">{{ $p }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="e-name" class="block text-sm font-medium text-gray-700">Full name *</label>
                            <input id="e-name" name="name" type="text" required maxlength="150" value="{{ $eOld('name') }}" class="mt-1 {{ $input }}">
                            @error('name', 'enquiry')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="e-email" class="block text-sm font-medium text-gray-700">Work email *</label>
                            <input id="e-email" name="email" type="email" required maxlength="150" value="{{ $eOld('email') }}" class="mt-1 {{ $input }}">
                            @error('email', 'enquiry')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="e-company" class="block text-sm font-medium text-gray-700">Company *</label>
                            <input id="e-company" name="company" type="text" required maxlength="150" value="{{ $eOld('company') }}" class="mt-1 {{ $input }}">
                            @error('company', 'enquiry')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="e-phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input id="e-phone" name="phone" type="tel" maxlength="40" value="{{ $eOld('phone') }}" placeholder="+971" class="mt-1 {{ $input }}">
                        </div>
                    </div>
                    <div>
                        <label for="e-sector" class="block text-sm font-medium text-gray-700">Sector</label>
                        <select id="e-sector" name="sector" class="mt-1 {{ $input }}">
                            <option value="">Select…</option>
                            @foreach (['Precious metals & stones', 'Real estate', 'Corporate services', 'Accounting / audit / legal', 'Virtual assets', 'Payments / fintech / finance', 'Other'] as $s)
                                <option @selected($eOld('sector') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="e-message" class="block text-sm font-medium text-gray-700">Anything else?</label>
                        <textarea id="e-message" name="message" rows="3" maxlength="5000" placeholder="Team size, how many customers you screen, current setup…" class="mt-1 {{ $input }}">{{ $eOld('message') }}</textarea>
                    </div>
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl">Send request</button>
                </form>
            @endif
        </div>
    </div>
</section>

@if ($trial || $enquiry || $errors->trial->any() || $errors->enquiry->any())
    {{-- Validation redirects drop the #anchor, so bring the visitor back to the form they used --}}
    <script>
        // Smooth scrolling started during parsing gets cut short as the page loads, so jump once it has
        window.addEventListener('load', () => document.getElementById('{{ ($trial || $errors->trial->any()) ? 'try-free' : 'enquire' }}')
            .scrollIntoView({ behavior: 'instant', block: 'start' }));
    </script>
@endif

@endsection
