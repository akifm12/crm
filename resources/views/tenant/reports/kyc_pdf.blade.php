<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>KYC File — {{ $client->displayName() }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

@page {
    margin-top: 46mm;
    margin-bottom: 18mm;
    margin-left: 18mm;
    margin-right: 18mm;
}

body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; background: #fff; }

/* ── Layout ── */
.page       { padding: 0; }
.page-break { page-break-before: always; }

/* ── Fixed header — repeats on every page ── */
.fixed-header {
    position: fixed;
    top: -43mm;
    left: 0;
    right: 0;
    background: #fff;
}
.header-table { width: 100%; border-collapse: collapse; }
.header-table td { vertical-align: middle; padding: 0; }
.header-logo  { width: 120px; }
.header-logo img { max-width: 110px; max-height: 50px; }
.header-center { text-align: center; }
.header-center .doc-title { font-size: 15pt; font-weight: bold; color: #000; letter-spacing: 1px; text-transform: uppercase; }
.header-center .doc-sub   { font-size: 8.5pt; color: #444; margin-top: 2px; letter-spacing: 0.5px; text-transform: uppercase; }
.header-right { text-align: right; font-size: 8pt; color: #333; width: 120px; }
.header-right .ref-num { font-size: 9.5pt; font-weight: bold; color: #000; }
.header-rule     { border: none; border-top: 3px solid #000; margin: 6px 0 0; }
.header-sub-rule { border: none; border-top: 1px solid #aaa; margin: 2px 0 4px; }
.classification  { background: #000; color: #fff; text-align: center; padding: 4px 0; font-size: 7.5pt; letter-spacing: 2px; text-transform: uppercase; font-weight: bold; }

/* ── Fixed footer — repeats on every page ── */
.fixed-footer {
    position: fixed;
    bottom: -15mm;
    left: 0;
    right: 0;
    background: #fff;
    border-top: 1px solid #aaa;
    padding-top: 4px;
}
.fixed-footer table { width: 100%; border-collapse: collapse; }
.fixed-footer td { font-size: 7.5pt; color: #555; vertical-align: top; }
.fixed-footer td:last-child { text-align: right; }

/* ── Client identity block ── */
.client-identity { background: #f2f2f2; border: 1px solid #999; padding: 10px 14px; margin-bottom: 18px; }
.client-identity table { width: 100%; border-collapse: collapse; }
.client-identity td { vertical-align: top; padding: 2px 0; }
.client-name { font-size: 14pt; font-weight: bold; color: #000; }
.client-meta { font-size: 9pt; color: #333; margin-top: 2px; }
.badge       { display: inline-block; padding: 2px 8px; font-size: 8pt; font-weight: bold; border: 1px solid #000; }

/* ── Section heading ── */
.section      { margin-bottom: 18px; }
.section-head { background: #222; color: #fff; padding: 5px 10px; font-size: 9.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0; }

/* ── Info table ── */
.info-table { width: 100%; border-collapse: collapse; border: 1px solid #bbb; }
.info-table tr:nth-child(even) td { background: #f7f7f7; }
.info-table td { padding: 5px 9px; font-size: 9.5pt; vertical-align: top; border-bottom: 1px solid #ddd; }
.info-table td.label { color: #333; width: 38%; font-weight: bold; font-size: 9pt; border-right: 1px solid #ddd; white-space: nowrap; }
.info-table td.value { color: #000; }
.info-table td.value em { color: #777; font-style: italic; }

/* Two-column layout */
.two-col { width: 100%; border-collapse: collapse; }
.two-col td { vertical-align: top; width: 50%; }
.two-col td:first-child { padding-right: 6px; }
.two-col td:last-child  { padding-left: 6px; }

/* ── Data table ── */
.data-table { width: 100%; border-collapse: collapse; border: 1px solid #bbb; }
.data-table th { background: #333; color: #fff; padding: 6px 9px; font-size: 8.5pt; text-align: left; }
.data-table td { padding: 5px 9px; font-size: 9pt; border-bottom: 1px solid #ddd; vertical-align: top; }
.data-table tr:nth-child(even) td { background: #f7f7f7; }
.data-table td em { color: #777; font-style: italic; }

/* ── Risk rating box ── */
.risk-box { border: 2px solid #000; padding: 10px 14px; margin-bottom: 10px; background: #f2f2f2; }
.risk-box .risk-label { font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #000; }
.risk-box p { font-size: 9pt; color: #333; margin-top: 4px; }

/* ── Sanctions lists ── */
.lists-box { border: 1px solid #bbb; padding: 8px 12px; background: #f7f7f7; margin-top: 8px; }
.lists-box table { width: 100%; border-collapse: collapse; }
.lists-box td { font-size: 9pt; color: #000; padding: 2px 6px; vertical-align: top; width: 50%; }

/* ── Signature block ── */
.sig-section { margin-top: 24px; }
.sig-table   { width: 100%; border-collapse: collapse; }
.sig-table td { width: 33%; vertical-align: bottom; padding: 0 12px; text-align: center; }
.sig-table td:first-child { padding-left: 0; text-align: left; }
.sig-table td:last-child  { padding-right: 0; text-align: right; }
.sig-line  { border-top: 1px solid #000; padding-top: 6px; font-size: 9pt; color: #333; margin-top: 40px; }
.sig-name  { font-weight: bold; font-size: 9.5pt; color: #000; }
.sig-title { font-size: 8.5pt; color: #444; }

/* ── Utility ── */
.text-muted { color: #777; font-style: italic; }
.nowrap { white-space: nowrap; }
.mt-8  { margin-top: 8px; }
.mt-12 { margin-top: 12px; }
.bold  { font-weight: bold; }
</style>
</head>
<body>
@php
$isCorp  = !in_array($client->client_type, ['individual']);
$ref     = 'KYC-' . str_pad($client->id, 5, '0', STR_PAD_LEFT);
$generated = now()->format('d M Y, H:i');
$sig     = $client->signatories->first();

$sofLabels = [
    'salary'           => 'Salary / Employment Income',
    'business_profits' => 'Business Profits',
    'investments'      => 'Investment Returns',
    'inheritance'      => 'Inheritance / Gift',
    'loan'             => 'Bank Loan / Financing',
    'savings'          => 'Personal Savings',
    'other'            => 'Other',
];
$purposeLabels = [
    'investment'   => 'Investment',
    'trading'      => 'Trading',
    'personal_use' => 'Personal Use',
    'gift'         => 'Gift',
    'business'     => 'Business Operations',
    'other'        => 'Other',
];

$sanctionsLists = [
    'UN Security Council Consolidated List',
    'UAE Cabinet Resolution No. 83 — Terror List',
    'OFAC SDN & Consolidated Sanctions List (USA)',
    'UK HM Treasury Financial Sanctions List',
    'EU Consolidated Sanctions List',
    'FATF High-Risk & Monitored Jurisdictions',
    'Interpol Wanted Persons (Red Notices)',
    'World-Check / Dow Jones Risk & Compliance',
    'UAE Central Bank — Designated Persons List',
    'Basel AML Index',
];

// Section numbering — shifts down for corporate (has signatories + shareholders sections)
$sn = fn(int $corpN, int $indN) => $isCorp ? $corpN : $indN;
@endphp

{{-- ════════════ FIXED HEADER (repeats every page) ════════════ --}}
<div class="fixed-header">
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if($tenant->logo_url)
                    <img src="{{ public_path('storage/' . ltrim(str_replace(url('/storage'), '', $tenant->logo_url), '/')) }}" alt="{{ $tenant->name }}">
                @else
                    <span style="font-size:12pt; font-weight:bold;">{{ $tenant->name }}</span>
                @endif
            </td>
            <td class="header-center">
                <div class="doc-title">Know Your Customer</div>
                <div class="doc-sub">Client Due Diligence File</div>
            </td>
            <td class="header-right">
                <div class="ref-num">{{ $ref }}</div>
                <div>Generated: {{ $generated }}</div>
                <div style="margin-top:2px;">{{ $tenant->name }}</div>
                @if($tenant->dnfbp_reg_no)<div>DNFBP: {{ $tenant->dnfbp_reg_no }}</div>@endif
            </td>
        </tr>
    </table>
    <hr class="header-rule">
    <hr class="header-sub-rule">
    <div class="classification">Confidential — For Compliance Purposes Only</div>
</div>

{{-- ════════════ FIXED FOOTER (repeats every page) ════════════ --}}
<div class="fixed-footer">
    <table>
        <tr>
            <td>
                {{ $tenant->name }}
                @if($tenant->address) &nbsp;·&nbsp; {{ $tenant->address }} @endif
                @if($tenant->contact_email) &nbsp;·&nbsp; {{ $tenant->contact_email }} @endif
            </td>
            <td>{{ $ref }} &nbsp;·&nbsp; {{ $generated }}</td>
        </tr>
    </table>
</div>

<div class="page">

    {{-- ════════════ CLIENT IDENTITY BLOCK ════════════ --}}
    <div class="client-identity">
        <table>
            <tr>
                <td>
                    <div class="client-name">{{ $client->displayName() }}</div>
                    <div class="client-meta">
                        {{ ucwords(str_replace('_', ' ', $client->client_type)) }}
                        &nbsp;·&nbsp; Status: <strong>{{ ucfirst($client->status) }}</strong>
                        &nbsp;·&nbsp; CDD: <strong>{{ strtoupper($client->cdd_type ?? 'Standard') }}</strong>
                        @if($client->trade_license_no)
                            &nbsp;·&nbsp; Trade Lic: <strong>{{ $client->trade_license_no }}</strong>
                        @elseif($client->passport_number)
                            &nbsp;·&nbsp; Passport: <strong>{{ $client->passport_number }}</strong>
                        @endif
                    </div>
                </td>
                <td style="text-align:right; width:130px; vertical-align:top;">
                    <span class="badge">{{ strtoupper($client->risk_rating ?? 'UNRATED') }} RISK</span>
                    @if($client->next_review_date)
                        <div style="font-size:8pt; color:#444; margin-top:4px;">
                            Review by {{ $client->next_review_date->format('d M Y') }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ════════════ SECTION 1 — CLIENT IDENTIFICATION ════════════ --}}
    <div class="section">
        <div class="section-head">1. Client Identification</div>
        @if($isCorp)
        <table class="info-table">
            <tr><td class="label">Company Name</td><td class="value">{{ $client->company_name ?? '—' }}</td></tr>
            <tr><td class="label">Legal Form</td><td class="value">{{ $client->legal_form ?? '—' }}</td></tr>
            <tr><td class="label">Country of Incorporation</td><td class="value">{{ $client->country_of_incorporation ?? '—' }}</td></tr>
            <tr><td class="label">Trade Licence No.</td><td class="value">{{ $client->trade_license_no ?? '—' }}</td></tr>
            <tr>
                <td class="label">Trade Licence Validity</td>
                <td class="value">
                    @if($client->trade_license_issue || $client->trade_license_expiry)
                        {{ $client->trade_license_issue?->format('d M Y') ?? '—' }} &nbsp;to&nbsp; {{ $client->trade_license_expiry?->format('d M Y') ?? '—' }}
                        @if($client->isLicenseExpired()) &nbsp;<strong>[EXPIRED]</strong> @endif
                        @if($client->isLicenseExpiringSoon() && !$client->isLicenseExpired()) &nbsp;<strong>[EXPIRING SOON]</strong> @endif
                    @else
                        <em>Not provided</em>
                    @endif
                </td>
            </tr>
            <tr><td class="label">VAT / TRN</td><td class="value">{{ $client->trn_number ?? '—' }}</td></tr>
            <tr><td class="label">Ejari No.</td><td class="value">{{ $client->ejari_number ?? '—' }}</td></tr>
            @if($client->ejari_expiry)
            <tr>
                <td class="label">Ejari Expiry</td>
                <td class="value">
                    {{ $client->ejari_expiry->format('d M Y') }}
                    @if($client->isEjariExpired()) &nbsp;<strong>[EXPIRED]</strong> @endif
                    @if($client->isEjariExpiringSoon() && !$client->isEjariExpired()) &nbsp;<strong>[EXPIRING SOON]</strong> @endif
                </td>
            </tr>
            @endif
            <tr><td class="label">Business Activity</td><td class="value">{{ $client->business_activity ?? '—' }}</td></tr>
            <tr><td class="label">Nature of Business</td><td class="value">{{ $client->nature_of_business ?? '—' }}</td></tr>
            <tr><td class="label">Registered Address</td><td class="value">{{ $client->registered_address ?? '—' }}</td></tr>
            @if($client->operating_address)
            <tr><td class="label">Operating Address</td><td class="value">{{ $client->operating_address }}</td></tr>
            @endif
            <tr><td class="label">Email</td><td class="value">{{ $client->email ?? '—' }}</td></tr>
            <tr><td class="label">Phone</td><td class="value">{{ $client->phone ?? '—' }}</td></tr>
            @if($client->website)
            <tr><td class="label">Website</td><td class="value">{{ $client->website }}</td></tr>
            @endif
        </table>
        @else
        <table class="info-table">
            <tr><td class="label">Full Name</td><td class="value">{{ $client->full_name ?? '—' }}</td></tr>
            @if($client->name_arabic)
            <tr><td class="label">Name (Arabic)</td><td class="value">{{ $client->name_arabic }}</td></tr>
            @endif
            <tr><td class="label">Nationality</td><td class="value">{{ $client->nationality ?? '—' }}</td></tr>
            <tr><td class="label">Date of Birth</td><td class="value">{{ $client->dob?->format('d M Y') ?? '—' }}</td></tr>
            <tr><td class="label">Passport No.</td><td class="value">{{ $client->passport_number ?? '—' }}</td></tr>
            <tr>
                <td class="label">Passport Expiry</td>
                <td class="value">
                    {{ $client->passport_expiry?->format('d M Y') ?? '—' }}
                    @if($client->isPassportExpired()) &nbsp;<strong>[EXPIRED]</strong> @endif
                    @if($client->isPassportExpiringSoon() && !$client->isPassportExpired()) &nbsp;<strong>[EXPIRING SOON]</strong> @endif
                </td>
            </tr>
            <tr><td class="label">Emirates ID No.</td><td class="value">{{ $client->eid_number ?? '—' }}</td></tr>
            @if($client->eid_expiry)
            <tr><td class="label">Emirates ID Expiry</td><td class="value">{{ $client->eid_expiry->format('d M Y') }}</td></tr>
            @endif
            <tr><td class="label">Occupation</td><td class="value">{{ $client->occupation ?? '—' }}</td></tr>
            <tr><td class="label">Employer</td><td class="value">{{ $client->employer_name ?? '—' }}</td></tr>
            <tr>
                <td class="label">PEP Status</td>
                <td class="value">
                    @if($client->pep_status)
                        <strong>YES — Politically Exposed Person</strong>
                        @if($client->pep_details) <br><span style="font-size:9pt;">{{ $client->pep_details }}</span> @endif
                    @else
                        No
                    @endif
                </td>
            </tr>
            <tr><td class="label">Email</td><td class="value">{{ $client->email ?? '—' }}</td></tr>
            <tr><td class="label">Phone</td><td class="value">{{ $client->phone ?? '—' }}</td></tr>
        </table>
        @endif
    </div>

    {{-- ════════════ SECTION 2 — SIGNATORIES (corporate only) ════════════ --}}
    @if($isCorp && $client->signatories->count())
    <div class="section">
        <div class="section-head">2. Authorised Signatories</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>Full Name</th><th>Position</th><th>Nationality</th>
                    <th>Date of Birth</th><th>Passport No.</th><th>Passport Expiry</th><th>Emirates ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($client->signatories as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s->full_name ?? '—' }}</td>
                    <td>{{ $s->position ?? '—' }}</td>
                    <td>{{ $s->nationality ?? '—' }}</td>
                    <td>{{ $s->dob?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $s->passport_number ?? '—' }}</td>
                    <td>{{ $s->passport_expiry?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $s->eid_number ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ════════════ SECTION 3 — SHAREHOLDERS / UBOs (corporate only) ════════════ --}}
    @if($isCorp && ($client->shareholders->count() || $client->ubos->count()))
    <div class="section">
        <div class="section-head">3. Shareholders &amp; Ultimate Beneficial Owners</div>
        @if($client->shareholders->count())
        <table class="data-table">
            <thead>
                <tr>
                    <th>Shareholder</th><th>Type</th><th>Nationality</th>
                    <th>Ownership %</th><th>UBO</th><th>Passport / ID</th><th>DOB</th>
                </tr>
            </thead>
            <tbody>
                @foreach($client->shareholders as $sh)
                <tr>
                    <td>{{ $sh->name ?? '—' }}</td>
                    <td>{{ ucfirst($sh->shareholder_type ?? '—') }}</td>
                    <td>{{ $sh->nationality ?? '—' }}</td>
                    <td>{{ $sh->ownership_percentage ? number_format($sh->ownership_percentage, 1) . '%' : '—' }}</td>
                    <td>{{ $sh->is_ubo ? 'Yes' : 'No' }}</td>
                    <td>{{ $sh->passport_number ?? $sh->eid_number ?? '—' }}</td>
                    <td>{{ $sh->dob?->format('d M Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($client->ubos->count() && $client->ubos->count() !== $client->shareholders->where('is_ubo', true)->count())
        <div class="mt-8">
        <table class="data-table">
            <thead>
                <tr><th colspan="5">Separately Declared UBOs</th></tr>
                <tr><th>Full Name</th><th>Nationality</th><th>Ownership %</th><th>Passport No.</th><th>DOB</th></tr>
            </thead>
            <tbody>
                @foreach($client->ubos as $u)
                <tr>
                    <td>{{ $u->full_name ?? '—' }}</td>
                    <td>{{ $u->nationality ?? '—' }}</td>
                    <td>{{ $u->ownership_percentage ? number_format($u->ownership_percentage, 1) . '%' : '—' }}</td>
                    <td>{{ $u->passport_number ?? '—' }}</td>
                    <td>{{ $u->dob?->format('d M Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
    @endif

    {{-- ════════════ SECTION 4/2 — AML RISK PROFILE ════════════ --}}
    <div class="section">
        <div class="section-head">{{ $sn(4,2) }}. AML Risk Profile &amp; Business Relationship</div>
        <table class="two-col">
            <tr>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="label">Source of Funds</td>
                            <td class="value">
                                @if($client->source_of_funds)
                                    @foreach((array)$client->source_of_funds as $sof)
                                        {{ $sofLabels[$sof] ?? ucwords(str_replace('_', ' ', $sof)) }}<br>
                                    @endforeach
                                    @if($client->source_of_funds_other)<em>{{ $client->source_of_funds_other }}</em>@endif
                                @else
                                    <em>Not specified</em>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Source of Wealth</td>
                            <td class="value">
                                @if($client->source_of_wealth)
                                    @foreach((array)$client->source_of_wealth as $sow)
                                        {{ $sofLabels[$sow] ?? ucwords(str_replace('_', ' ', $sow)) }}<br>
                                    @endforeach
                                    @if($client->source_of_wealth_other)<em>{{ $client->source_of_wealth_other }}</em>@endif
                                @else
                                    <em>Not specified</em>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Purpose of Relationship</td>
                            <td class="value">{{ $purposeLabels[$client->purpose_of_relationship ?? ''] ?? ucwords(str_replace('_',' ',$client->purpose_of_relationship ?? '')) ?: '—' }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="label">Expected Monthly Volume</td>
                            <td class="value">{{ $client->expected_monthly_volume ? 'AED ' . number_format($client->expected_monthly_volume) : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Expected Frequency</td>
                            <td class="value">{{ $client->expected_monthly_frequency ? $client->expected_monthly_frequency . ' transactions/month' : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Countries Involved</td>
                            <td class="value">
                                @if($client->countries_involved && count($client->countries_involved))
                                    {{ implode(', ', $client->countries_involved) }}
                                @else
                                    <em>None declared</em>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ════════════ SECTION 5/3 — RISK ASSESSMENT ════════════ --}}
    <div class="section">
        <div class="section-head">{{ $sn(5,3) }}. Risk Assessment</div>
        <div class="risk-box">
            <span class="risk-label">{{ strtoupper($client->risk_rating ?? 'Unrated') }} Risk</span>
            @if($client->risk_assessed_at)
                <p>Assessed on {{ $client->risk_assessed_at->format('d M Y') }}
                @if($client->risk_assessed_by) by {{ $client->risk_assessed_by }} @endif</p>
            @endif
            @if($client->next_review_date)
                <p>Next periodic review due: <strong>{{ $client->next_review_date->format('d M Y') }}</strong></p>
            @endif
        </div>
        @if($client->risk_notes)
        <table class="info-table">
            <tr><td class="label">Risk Notes</td><td class="value">{{ $client->risk_notes }}</td></tr>
        </table>
        @endif
        @if($client->risk_assessment_data && count($client->risk_assessment_data))
        <div class="mt-8">
        <table class="data-table">
            <thead><tr><th>Risk Factor</th><th>Score</th><th>Weight</th></tr></thead>
            <tbody>
                @foreach($client->risk_assessment_data as $factor => $val)
                @if(is_array($val) && isset($val['score']))
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $factor)) }}</td>
                    <td>{{ $val['score'] ?? '—' }}</td>
                    <td>{{ isset($val['weight']) ? $val['weight'] . '%' : '—' }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

    {{-- ════════════ SECTION 6/4 — SANCTIONS & AML SCREENING ════════════ --}}
    <div class="section">
        <div class="section-head">{{ $sn(6,4) }}. Sanctions &amp; AML Screening</div>
        <table class="info-table">
            <tr>
                <td class="label">Screening Status</td>
                <td class="value bold">
                    {{ $client->screening_status ? strtoupper(str_replace('_', ' ', $client->screening_status)) : 'NOT SCREENED' }}
                </td>
            </tr>
            @if($client->screening_date)
            <tr>
                <td class="label">Last Screened</td>
                <td class="value">{{ $client->screening_date->format('d M Y, H:i') }}</td>
            </tr>
            @endif
            @if($client->screening_reference)
            <tr>
                <td class="label">Screening Reference</td>
                <td class="value">{{ $client->screening_reference }}</td>
            </tr>
            @endif
            @if(is_array($client->screening_result) && isset($client->screening_result['status']))
            <tr>
                <td class="label">Result</td>
                <td class="value">
                    {{ $client->screening_result['hits_count'] ?? 0 }} hit(s) found
                    @if(isset($client->screening_result['notes'])) — {{ $client->screening_result['notes'] }} @endif
                </td>
            </tr>
            @endif
        </table>

        <div style="margin-top:10px; margin-bottom:4px; font-size:9pt; font-weight:bold;">Sanctions &amp; Watch-Lists Screened:</div>
        <div class="lists-box">
            <table>
                @foreach(array_chunk($sanctionsLists, 2) as $row)
                <tr>
                    <td>&#10003;&nbsp; {{ $row[0] }}</td>
                    @if(isset($row[1]))<td>&#10003;&nbsp; {{ $row[1] }}</td>@else<td></td>@endif
                </tr>
                @endforeach
            </table>
        </div>
    </div>

    {{-- ════════════ SIGNATURE BLOCK ════════════ --}}
    <div class="sig-section">
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-line">
                        <div class="sig-name">{{ $sig?->full_name ?? $client->displayName() }}</div>
                        <div class="sig-title">{{ $sig?->position ?? 'Client / Authorised Signatory' }}</div>
                    </div>
                </td>
                <td style="text-align:center;">
                    <div class="sig-line">
                        <div class="sig-name">{{ $tenant->mlro_name ?? '___________________________' }}</div>
                        <div class="sig-title">MLRO / Compliance Officer</div>
                    </div>
                </td>
                <td style="text-align:right;">
                    <div class="sig-line">
                        <div class="sig-name">Date: ___________________</div>
                        <div class="sig-title">Date of Approval</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>


</div><!-- /.page -->
</body>
</html>
