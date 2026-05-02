@extends('layouts.tenant')

@section('title', 'Risk Assessment — ' . $client->displayName())
@section('page-title', 'Risk Assessment')
@section('page-subtitle', $client->displayName())

@section('content')

@php
$isCorporate = $client->client_type !== 'individual';
$saved = $client->risk_assessment_data ?? [];
$fv = fn($key, $default = 0) => $saved['factors'][$key] ?? $default;

// FATF high-risk countries (simplified list)
$highRiskCountries = ['Afghanistan','Belarus','Burkina Faso','Cayman Islands','Democratic Republic of Congo','Haiti','Iran','Iraq','Libya','Mali','Myanmar','Nicaragua','North Korea','Panama','Russia','Somalia','South Sudan','Sudan','Syria','Uganda','Vanuatu','Yemen'];
$mediumRiskCountries = ['Albania','Barbados','Botswana','Cambodia','Ghana','Jamaica','Jordan','Morocco','Pakistan','Philippines','Senegal','Turkey','UAE (certain exposure)'];

$clientCountry = $client->country_of_incorporation ?? $client->nationality ?? '';
$countryRisk = in_array($clientCountry, $highRiskCountries) ? 3 : (in_array($clientCountry, $mediumRiskCountries) ? 2 : 1);
@endphp

<form method="POST" action="{{ route('tenant.risk.save', [$tenant->slug, $client->id]) }}" x-data="riskForm()" @submit.prevent="submitForm">
@csrf

{{-- Client info header --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5 flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-lg font-bold text-blue-700">
            {{ strtoupper(substr($client->displayName(), 0, 1)) }}
        </div>
        <div>
            <h2 class="text-base font-bold text-gray-900">{{ $client->displayName() }}</h2>
            <p class="text-sm text-gray-500">
                {{ $isCorporate ? ($client->trade_license_no ?? 'No licence') : ($client->nationality ?? '') }}
                @if($client->risk_assessment_data)
                · Last assessed {{ $client->risk_assessed_at?->format('d M Y') }} by {{ $client->risk_assessment_data['assessed_by'] ?? '—' }}
                @endif
            </p>
        </div>
    </div>
    @if($client->risk_rating)
    <span class="inline-flex px-3 py-1.5 rounded-full text-sm font-bold {{ $client->riskBadgeColor() }}">
        Current: {{ ucfirst($client->risk_rating) }} risk
    </span>
    @endif
</div>

{{-- Score display --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Overall risk score</p>
            <div class="flex items-center gap-3">
                <p class="text-4xl font-bold font-mono" :class="scoreColor" x-text="score.toFixed(2)">—</p>
                <div>
                    <p class="text-sm font-bold" :class="scoreColor" x-text="suggestedRating.toUpperCase() + ' RISK'"></p>
                    <p class="text-xs text-gray-400">Suggested rating</p>
                </div>
            </div>
        </div>
        <div class="flex gap-6 text-center">
            <div><p class="text-xs text-gray-400 mb-1">Customer (30%)</p><p class="text-lg font-bold font-mono text-gray-700" x-text="catScores.customer.toFixed(1)">—</p></div>
            <div><p class="text-xs text-gray-400 mb-1">Geographic (25%)</p><p class="text-lg font-bold font-mono text-gray-700" x-text="catScores.geographic.toFixed(1)">—</p></div>
            <div><p class="text-xs text-gray-400 mb-1">Product (20%)</p><p class="text-lg font-bold font-mono text-gray-700" x-text="catScores.product.toFixed(1)">—</p></div>
            <div><p class="text-xs text-gray-400 mb-1">Transaction (15%)</p><p class="text-lg font-bold font-mono text-gray-700" x-text="catScores.transaction.toFixed(1)">—</p></div>
            <div><p class="text-xs text-gray-400 mb-1">Channel (5%)</p><p class="text-lg font-bold font-mono text-gray-700" x-text="catScores.channel.toFixed(1)">—</p></div>
            <div><p class="text-xs text-gray-400 mb-1">Supply chain (5%)</p><p class="text-lg font-bold font-mono text-gray-700" x-text="catScores.supply_chain.toFixed(1)">—</p></div>
        </div>
    </div>
    {{-- Score bar --}}
    <div class="mt-4 bg-gradient-to-r from-green-200 via-amber-200 to-red-300 rounded-full h-3 relative">
        <div class="absolute top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-white border-2 border-gray-700 shadow transition-all"
             :style="'left: calc(' + ((score - 1) / 2 * 100) + '% - 8px)'"></div>
        <div class="absolute -bottom-5 left-0 text-xs text-gray-400">1.0 Low</div>
        <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-xs text-gray-400">2.0 Medium</div>
        <div class="absolute -bottom-5 right-0 text-xs text-gray-400">3.0 High</div>
    </div>
</div>

{{-- Risk factor sections --}}
<div class="space-y-5">

    {{-- ── 1. CUSTOMER RISK ────────────────────────────────────────────── --}}
    @include('tenant._risk_section', [
        'title'    => '1. Customer risk',
        'weight'   => '30%',
        'category' => 'customer',
        'color'    => 'blue',
        'factors'  => [
            ['customer_type',        'Customer type',
                [1 => 'Regulated entity / Listed company', 2 => 'Private company / Partnership', 3 => 'Individual / Complex structure / Trust'],
                $isCorporate ? ($client->legal_form === 'LLC' ? 2 : 2) : 3
            ],
            ['customer_pep',         'PEP status',
                [1 => 'No PEP links', 2 => 'Former PEP or close associate', 3 => 'Current PEP or direct family'],
                $client->pep_status ? 3 : 1
            ],
            ['customer_nationality', 'Nationality / country of incorporation risk',
                [1 => 'Low risk country (FATF compliant)', 2 => 'Medium risk country (enhanced monitoring)', 3 => 'High risk / sanctioned country (FATF grey/black list)'],
                $countryRisk
            ],
            ['customer_transparency','Ownership transparency',
                [1 => 'Clear, simple ownership — full UBO identified', 2 => 'Some complexity — multiple layers but disclosed', 3 => 'Complex / opaque — nominee, bearer shares, or unverified UBO'],
                2
            ],
            ['customer_adverse',     'Adverse media / prior issues',
                [1 => 'None found', 2 => 'Minor / historical issues', 3 => 'Significant adverse media or prior regulatory action'],
                1
            ],
        ],
        'saved' => $fv,
    ])

    {{-- ── 2. GEOGRAPHIC RISK ──────────────────────────────────────────── --}}
    @include('tenant._risk_section', [
        'title'    => '2. Geographic risk',
        'weight'   => '25%',
        'category' => 'geographic',
        'color'    => 'purple',
        'factors'  => [
            ['geographic_country',     'Country of incorporation / residence',
                [1 => 'UAE or FATF member state (low risk)', 2 => 'Medium risk jurisdiction (increased monitoring)', 3 => 'FATF grey/black list, sanctioned, or high-risk jurisdiction'],
                $countryRisk
            ],
            ['geographic_transactions','Countries involved in transactions',
                [1 => 'UAE only', 2 => 'Other GCC / FATF member states', 3 => 'High-risk or sanctioned jurisdictions'],
                1
            ],
            ['geographic_exposure',    'Exposure to high-risk jurisdictions',
                [1 => 'No exposure', 2 => 'Limited / historical exposure', 3 => 'Regular transactions or strong connections'],
                1
            ],
        ],
        'saved' => $fv,
    ])

    {{-- ── 3. PRODUCT / SERVICE RISK ──────────────────────────────────── --}}
    @include('tenant._risk_section', [
        'title'    => '3. Product / service risk',
        'weight'   => '20%',
        'category' => 'product',
        'color'    => 'amber',
        'factors'  => [
            ['product_type',       'Type of precious metals / stones dealt',
                [1 => 'Finished jewellery / retail', 2 => 'Gold bars, coins, or mixed', 3 => 'Raw / unrefined gold or conflict minerals'],
                2
            ],
            ['product_purpose',    'Purpose of business relationship',
                [1 => 'Personal / retail use (small amounts)', 2 => 'Investment / trading (standard)', 3 => 'Large-scale trading / export / import'],
                $isCorporate ? 2 : 1
            ],
            ['product_anonymity',  'Product anonymity risk',
                [1 => 'Low anonymity — full documentation required', 2 => 'Some anonymity — partial cash component', 3 => 'High anonymity — cash, bearer instruments, or no documentation'],
                1
            ],
        ],
        'saved' => $fv,
    ])

    {{-- ── 4. TRANSACTION RISK ─────────────────────────────────────────── --}}
    @include('tenant._risk_section', [
        'title'    => '4. Transaction risk',
        'weight'   => '15%',
        'category' => 'transaction',
        'color'    => 'orange',
        'factors'  => [
            ['transaction_volume',    'Expected monthly volume',
                [1 => 'Low (under AED 50,000)', 2 => 'Medium (AED 50,000 – 500,000)', 3 => 'High (over AED 500,000)'],
                $client->expected_monthly_volume ? ($client->expected_monthly_volume < 50000 ? 1 : ($client->expected_monthly_volume < 500000 ? 2 : 3)) : 1
            ],
            ['transaction_frequency', 'Transaction frequency',
                [1 => '1–5 per month', 2 => '6–15 per month', 3 => 'More than 15 per month'],
                match($client->expected_monthly_frequency) { '1-5' => 1, '6-15' => 2, default => 3 }
            ],
            ['transaction_cash',      'Cash component',
                [1 => 'No cash — bank transfers only', 2 => 'Occasional cash (below AED 55,000 threshold)', 3 => 'Frequent or large cash transactions'],
                1
            ],
            ['transaction_pattern',   'Transaction pattern consistency',
                [1 => 'Consistent with stated purpose', 2 => 'Minor deviations — explainable', 3 => 'Unusual / inconsistent with business profile'],
                1
            ],
        ],
        'saved' => $fv,
    ])

    {{-- ── 5. CHANNEL / DELIVERY RISK ─────────────────────────────────── --}}
    @include('tenant._risk_section', [
        'title'    => '5. Channel / delivery risk',
        'weight'   => '5%',
        'category' => 'channel',
        'color'    => 'teal',
        'factors'  => [
            ['channel_relationship', 'Business relationship channel',
                [1 => 'Face-to-face — identity verified in person', 2 => 'Mixed — some remote interaction', 3 => 'Entirely non-face-to-face'],
                1
            ],
            ['channel_intermediary', 'Intermediary involvement',
                [1 => 'Direct relationship — no intermediary', 2 => 'Regulated intermediary (lawyer, broker)', 3 => 'Unknown or unregulated intermediary'],
                1
            ],
        ],
        'saved' => $fv,
    ])

    {{-- ── 6. SUPPLY CHAIN RISK ───────────────────────────────────────── --}}
    @include('tenant._risk_section', [
        'title'    => '6. Supply chain risk (DNFBP specific)',
        'weight'   => '5%',
        'category' => 'supply_chain',
        'color'    => 'red',
        'factors'  => [
            ['supply_chain_cahra',  'CAHRA exposure (Conflict-Affected & High-Risk Areas)',
                [1 => 'No CAHRA exposure — declared and verified', 2 => 'Possible CAHRA links — needs monitoring', 3 => 'CAHRA exposure confirmed or suspected'],
                $client->decl_cahra ? 1 : 2
            ],
            ['supply_chain_source', 'Gold / precious metal sourcing',
                [1 => 'UAE licensed supplier — documented supply chain', 2 => 'Mixed or partially documented supply chain', 3 => 'Undocumented, unknown, or high-risk origin'],
                1
            ],
            ['supply_chain_tfs',    'TFS (Targeted Financial Sanctions) exposure',
                [1 => 'No TFS links identified', 2 => 'Possible indirect exposure', 3 => 'Direct TFS exposure or confirmed sanctions link'],
                $client->screening_status === 'match' ? 3 : 1
            ],
        ],
        'saved' => $fv,
    ])

</div>

{{-- Override + notes section --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mt-5 space-y-4">
    <h3 class="text-sm font-semibold text-gray-700 pb-2 border-b border-gray-100">Assessment conclusion</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-2">Final risk rating</label>
            <p class="text-xs text-gray-400 mb-2">System suggests: <span class="font-semibold" :class="scoreColor" x-text="suggestedRating.toUpperCase()"></span>. Override below if justified.</p>
            <div class="flex gap-3">
                @foreach(['low'=>['Low risk','border-green-200 bg-green-50','text-green-700'],'medium'=>['Medium risk','border-amber-200 bg-amber-50','text-amber-700'],'high'=>['High risk','border-red-200 bg-red-50','text-red-700']] as $v=>[$l,$border,$tc])
                <label class="flex-1 border {{ $border }} rounded-xl p-3 cursor-pointer text-center hover:opacity-80 transition">
                    <input type="radio" name="rating_override" value="{{ $v }}"
                           {{ ($saved['final_rating'] ?? '') === $v ? 'checked' : '' }}
                           class="sr-only" x-model="overrideRating">
                    <span class="text-sm font-semibold {{ $tc }}" x-bind:class="overrideRating === '{{ $v }}' ? 'ring-2 ring-offset-1 ring-current rounded' : ''">{{ $l }}</span>
                </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Next KYC review date</label>
            <input type="date" name="next_review_date"
                   value="{{ $client->next_review_date?->format('Y-m-d') }}"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-400 mt-1">High risk: 1 year · Medium: 2 years · Low: 3 years</p>
        </div>
    </div>

    <div x-show="overrideRating && overrideRating !== suggestedRating" x-cloak>
        <label class="block text-xs font-medium text-gray-600 mb-1">Override justification <span class="text-red-500">*</span></label>
        <textarea name="override_reason" rows="2"
                  class="w-full px-3 py-2 text-sm border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 resize-none bg-amber-50"
                  placeholder="Document the reason for overriding the system-calculated rating…">{{ $saved['override_reason'] ?? '' }}</textarea>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Risk notes & observations</label>
        <textarea name="risk_notes" rows="3"
                  class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                  placeholder="Any additional observations, red flags, or notes for this assessment…">{{ $client->risk_notes }}</textarea>
    </div>
</div>

{{-- Actions --}}
<div class="flex items-center justify-between mt-5">
    <a href="{{ route('tenant.risk', $tenant->slug) }}"
       class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        ← Back to risk register
    </a>
    <button type="submit"
            class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Save risk assessment
    </button>
</div>

</form>

<script>
function riskForm() {
    return {
        factors: @json($saved['factors'] ?? []),
        overrideRating: '{{ $saved['final_rating'] ?? '' }}',

        weights: {
            customer: 0.30, geographic: 0.25, product: 0.20,
            transaction: 0.15, channel: 0.05, supply_chain: 0.05
        },

        get catScores() {
            const cats = {};
            for (const cat in this.weights) {
                const catFactors = Object.entries(this.factors)
                    .filter(([k]) => k.startsWith(cat + '_'))
                    .map(([, v]) => parseFloat(v) || 0);
                cats[cat] = catFactors.length
                    ? catFactors.reduce((a, b) => a + b, 0) / catFactors.length
                    : 0;
            }
            return cats;
        },

        get score() {
            const cats = this.catScores;
            let total = 0, weight = 0;
            for (const [cat, w] of Object.entries(this.weights)) {
                if (cats[cat] > 0) { total += cats[cat] * w; weight += w; }
            }
            return weight > 0 ? total / weight : 1;
        },

        get suggestedRating() {
            const s = this.score;
            return s >= 2.4 ? 'high' : s >= 1.7 ? 'medium' : 'low';
        },

        get scoreColor() {
            return this.score >= 2.4 ? 'text-red-600' : this.score >= 1.7 ? 'text-amber-600' : 'text-green-600';
        },

        setFactor(key, value) {
            this.factors[key] = parseInt(value);
            if (!this.overrideRating) this.overrideRating = this.suggestedRating;
        },

        submitForm() {
            // Inject factor values as hidden inputs
            const form = this.$el;
            for (const [k, v] of Object.entries(this.factors)) {
                let inp = form.querySelector(`input[name="factors[${k}]"]`);
                if (!inp) {
                    inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = `factors[${k}]`;
                    form.appendChild(inp);
                }
                inp.value = v;
            }
            form.submit();
        }
    }
}
</script>

@endsection
