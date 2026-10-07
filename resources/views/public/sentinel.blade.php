@extends('layouts.public')

@section('title', 'Sentinel AML Screening Software — Blue Arrow')
@section('meta_description', 'Sentinel screens customers against sanctions, PEP, law-enforcement, debarment and adverse-media sources — ' . $stats['total'] . ' records, updated nightly. Built in the UAE by Blue Arrow for DNFBPs and regulated businesses.')

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
                Sentinel screens individuals, companies, vessels and crypto wallets against sanctions, PEP,
                law-enforcement, debarment and adverse-media sources in one search — then takes the case
                through review to a report your auditor can read.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#try-free" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-3 rounded-xl shadow-sm">Try 5 free screenings</a>
                <a href="#enquire" class="border border-white/20 hover:border-white/40 text-white font-semibold px-6 py-3 rounded-xl">Talk to us about a plan</a>
            </div>
            <p class="mt-4 text-sm text-slate-400">Already a customer? <a href="https://aml.bluearrow.ae" class="text-sky-300 hover:text-sky-200 font-medium">Sign in to Sentinel →</a></p>
        </div>

        <div class="lg:col-span-2 grid grid-cols-2 gap-px rounded-2xl overflow-hidden border border-white/10 bg-white/10">
            @foreach ([
                [$stats['total'], 'records checked in every search'],
                [$stats['sanctions'], 'sanctions designations'],
                [$stats['peps'], 'politically exposed persons'],
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
            ['🔍', 'Sanctions & PEP screening', 'One search covers sanctions, PEPs, wanted lists, debarment and regulatory warnings. Fuzzy matching catches spelling variants, aliases and transliterations, with a match threshold your admin controls.'],
            ['📰', 'Adverse media in the same search', 'Every screening also checks news coverage for financial crime, fraud, corruption and terrorism-financing signals, so reputational risk shows up alongside the list hits.'],
            ['🗂️', 'Case management & review', 'Each screening becomes a case. Hits above the threshold, or any adverse media, are marked for enhanced due diligence and go to review, where your MLRO records the decision and the reasons.'],
            ['📄', 'Audit-ready case reports', 'One report per case with the subject, every hit, the decision and the reviewer\'s notes — open it in the browser and save it as a PDF for your file.'],
            ['🔁', 'Ongoing monitoring', 'Put existing customers on daily, weekly or monthly re-screening. Sentinel re-runs the check automatically and updates the case if anything changes.'],
            ['📋', 'Batch screening', 'Paste up to 500 names at once to screen an existing customer book or a supplier list in one go.'],
            ['₿', 'Crypto wallet screening', 'Check BTC, ETH, TRX (incl. USDT-TRC20) and XRP addresses against known sanctioned, ransomware and seized wallets.'],
            ['🚢', 'Vessel screening', 'Screen ships against sanctioned and shadow-fleet vessels, port-state detentions and regulatory warnings — useful for trade and shipping counterparties.'],
            ['👥', 'Teams & roles', 'Analyst, MLRO and admin roles, per-user screening quotas, and one active session per login so accounts can\'t be shared.'],
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
            <h2 class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total'] }} records, refreshed every night</h2>
            <p class="mt-3 text-gray-600">
                Lists are downloaded and checked every night before they go live — if a source looks incomplete, the previous day's data stays in place and our team is alerted.
                Sanctions data comes from the OpenSanctions consolidated collection, with the UN Security Council list taken directly from the UN.
            </p>
        </div>

        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                [$stats['sanctions'], 'Sanctions', '100+ national and international programmes — incl. OFAC, EU, UK, UN Security Council and the UAE Local Terrorist List.'],
                [$stats['peps'], 'Politically exposed persons', 'Global PEP data covering current and former officials.'],
                [$stats['wanted'], 'Wanted & law enforcement', 'Wanted-person lists published by law-enforcement agencies, plus ' . $stats['interpol'] . ' Interpol red notices.'],
                [$stats['debarment'], 'Debarment', 'Firms and individuals barred by development banks and procurement authorities.'],
                [$stats['regulatory'], 'Regulatory warnings', 'Warnings and enforcement notices published by financial regulators.'],
                [$stats['maritime'], 'Vessels', 'Sanctioned and shadow-fleet vessels, port-state detentions and other flagged ships.'],
                [$stats['crypto'], 'Crypto wallets', 'Addresses linked to sanctions, ransomware and law-enforcement seizures.'],
                ['Live', 'Adverse media', 'News search run at the moment of screening — not a stored snapshot.'],
            ] as [$num, $title, $text])
                <div class="rounded-2xl bg-white border border-gray-100 p-5 shadow-sm">
                    <div class="text-xl font-extrabold text-brand-600">{{ $num }}</div>
                    <div class="mt-1 font-semibold text-gray-900 text-sm">{{ $title }}</div>
                    <p class="mt-1 text-xs text-gray-500 leading-relaxed">{{ $text }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-xs text-gray-400">Counts are from the latest nightly import and rounded down.</p>
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
            ['Enter the subject', 'An individual or a company — name plus whatever you have: nationality, date of birth, country, identifiers. Or paste a batch.'],
            ['Sentinel screens', 'Lists and adverse media are searched together and every hit is scored for how closely it matches.'],
            ['Review the case', 'Clear results are recorded as such. Strong matches or adverse media go to EDD for your MLRO to review and decide.'],
            ['Keep the record', 'Save the case report for your file and, if you want, put the customer on ongoing monitoring.'],
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
            <p class="mt-3 text-gray-600">Under the UAE's AML/CFT framework, financial institutions and DNFBPs must screen customers and counterparties — at onboarding and on an ongoing basis.</p>
        </div>
        <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ([
                ['💎', 'Dealers in precious metals & stones', 'Screen buyers and suppliers, especially on cash transactions of AED 55,000 and above.'],
                ['🏠', 'Real estate agents & developers', 'Screen buyers, sellers and investors before the deal — real estate is a high-risk sector in the UAE.'],
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
<section id="pricing" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="text-center max-w-2xl mx-auto">
        <span class="text-brand-600 font-semibold text-sm">Pricing</span>
        <h2 class="mt-2 text-3xl font-bold text-gray-900">Two plans, unlimited screening</h2>
        <p class="mt-3 text-gray-600">No per-screen charges. Billed annually in AED.</p>
    </div>

    @php($common = ['Unlimited screenings', 'Sanctions, PEP & adverse media in every screen', 'Individuals, companies, vessels & crypto wallets', 'Batch screening', 'Case management & MLRO review', 'Case reports (save as PDF)', 'Multi-user teams with roles'])
    <div class="mt-12 grid md:grid-cols-2 gap-6">
        @foreach ([
            ['Essential', 'AED 4,000', 'Everything you need to screen at onboarding and keep a clean record.', false],
            ['Complete', 'AED 5,000', 'Everything in Essential, plus automatic ongoing monitoring of your customer book.', true],
        ] as [$plan, $price, $desc, $featured])
            <div class="relative rounded-2xl bg-white p-8 {{ $featured ? 'border-2 border-brand-600 shadow-lg' : 'border border-gray-200 shadow-sm' }}">
                @if ($featured)
                    <span class="absolute -top-3 left-8 bg-brand-600 text-white text-[11px] font-semibold tracking-wide px-3 py-1 rounded-full">RECOMMENDED</span>
                @endif
                <h3 class="text-sm font-semibold tracking-wide text-gray-500 uppercase">{{ $plan }}</h3>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold text-gray-900">{{ $price }}</span>
                    <span class="text-sm text-gray-500">per year</span>
                </div>
                <p class="mt-3 text-sm text-gray-600">{{ $desc }}</p>
                <ul class="mt-6 space-y-2 text-sm">
                    @foreach ($common as $f)
                        <li class="flex gap-2 text-gray-700"><span class="text-emerald-500">✓</span>{{ $f }}</li>
                    @endforeach
                    <li class="flex gap-2 {{ $featured ? 'text-gray-900 font-semibold' : 'text-gray-400' }}">
                        <span class="{{ $featured ? 'text-emerald-500' : '' }}">{{ $featured ? '✓' : '—' }}</span>Ongoing monitoring (daily / weekly / monthly)
                    </li>
                </ul>
                <a href="#enquire" @click="$dispatch('pick-plan', '{{ $plan }}')"
                   class="mt-8 block text-center font-semibold px-6 py-3 rounded-xl {{ $featured ? 'bg-brand-600 hover:bg-brand-700 text-white' : 'border border-gray-300 hover:border-brand-600 text-gray-800' }}">
                    Get {{ $plan }}
                </a>
            </div>
        @endforeach
    </div>
    <p class="mt-6 text-center text-sm text-gray-500">
        Want to screen from your own system? <a href="#enquire" @click="$dispatch('pick-plan', 'API')" class="text-brand-600 font-semibold hover:text-brand-700">Ask us about API access</a>.
    </p>
</section>

{{-- ── TRY FREE + ENQUIRE ───────────────────────────────────────────────── --}}
<section class="bg-slate-950 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-8">

        {{-- Free trial --}}
        <div id="try-free" class="scroll-mt-24 rounded-2xl bg-white p-8">
            <h2 class="text-2xl font-bold text-gray-900">Try Sentinel free</h2>
            <p class="mt-2 text-sm text-gray-600">5 real screenings against the live data. No card, no commitment — your login is emailed straight away.</p>

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
                            @foreach (['Essential', 'Complete', 'API', 'Not sure'] as $p)
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
    {{-- Validation redirects drop the #anchor — bring the visitor back to the form they used --}}
    <script>
        document.getElementById('{{ ($trial || $errors->trial->any()) ? 'try-free' : 'enquire' }}').scrollIntoView();
    </script>
@endif

@endsection
