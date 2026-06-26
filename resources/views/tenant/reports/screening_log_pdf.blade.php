<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Screening Report — {{ $log->query }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #1a1a1a; background: #fff; }
.page { max-width: 210mm; margin: 0 auto; padding: 20mm; }
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0e4d8a; padding-bottom: 12px; margin-bottom: 20px; }
.header-left h1 { font-size: 16pt; color: #0e4d8a; font-weight: bold; }
.header-left p { font-size: 9pt; color: #666; margin-top: 2px; }
.header-right { text-align: right; font-size: 9pt; color: #444; }
.header-right .ref { font-weight: bold; color: #0e4d8a; font-size: 10pt; }
.title-bar { background: #0e4d8a; color: #fff; padding: 10px 16px; margin-bottom: 20px; }
.title-bar h2 { font-size: 13pt; font-weight: bold; }
.title-bar p { font-size: 9pt; opacity: 0.85; margin-top: 2px; }
.result-clear { background: #f0fdf4; border: 1.5px solid #16a34a; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px; }
.result-match  { background: #fef2f2; border: 1.5px solid #dc2626; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px; }
.result-clear h3 { color: #16a34a; font-size: 12pt; }
.result-match h3 { color: #dc2626; font-size: 12pt; }
.result-clear p, .result-match p { font-size: 9pt; margin-top: 4px; color: #444; }
.section { margin-bottom: 20px; }
.section-title { font-size: 10pt; font-weight: bold; color: #0e4d8a; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #dde3ea; padding-bottom: 4px; margin-bottom: 10px; }
.info-grid { width: 100%; border-collapse: collapse; }
.info-grid td { padding: 5px 8px; font-size: 10pt; vertical-align: top; }
.info-grid td:first-child { color: #555; width: 40%; }
.info-grid td:last-child { font-weight: 500; }
.info-grid tr:nth-child(even) td { background: #f8fafc; }
.hits-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
.hits-table th { background: #1e3a5f; color: #fff; padding: 7px 10px; font-size: 9pt; text-align: left; }
.hits-table td { padding: 7px 10px; font-size: 9pt; border-bottom: 1px solid #e8edf2; vertical-align: top; }
.hits-table tr:nth-child(even) td { background: #f8fafc; }
.badge-critical { background: #dc2626; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
.badge-high     { background: #f97316; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
.badge-medium   { background: #f59e0b; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
.lists-box { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: 4px; margin-top: 8px; }
.lists-box p { font-size: 9pt; color: #334155; margin-bottom: 3px; }
.lists-box p::before { content: "✓ "; color: #0e4d8a; font-weight: bold; }
.sig-section { margin-top: 30px; display: flex; gap: 40px; }
.sig-box { flex: 1; border-top: 1px solid #333; padding-top: 8px; font-size: 9pt; color: #444; }
.sig-box .name { font-weight: bold; font-size: 10pt; color: #1a1a1a; }
.footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #dde3ea; font-size: 8pt; color: #888; text-align: center; }
@media print {
    body { font-size: 10pt; }
    .page { padding: 15mm; max-width: none; }
    .no-print { display: none; }
    .result-match, .result-clear { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .hits-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .title-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>

@php
$summary      = $log->result ?? [];
$isMatch      = ($summary['status'] ?? '') === 'match';
$totalHits    = $summary['total_hits'] ?? 0;
$hits         = $summary['hits'] ?? [];
$ref          = $log->reference ?? 'SCR-' . strtoupper(substr(md5($log->id), 0, 8));
$screenedOn   = $log->created_at->format('d F Y, H:i');
@endphp

<div class="page">

    <div class="no-print" style="margin-bottom:16px;text-align:right;">
        <button onclick="window.print()" style="background:#0e4d8a;color:#fff;border:none;padding:8px 20px;border-radius:5px;font-size:11pt;cursor:pointer;font-weight:bold;">
            ↓ Print / Save as PDF
        </button>
    </div>

    <div class="header">
        <div class="header-left">
            <h1>Blue Arrow Management Consultants</h1>
            <p>AML Compliance Services — bluearrow.ae</p>
        </div>
        <div class="header-right">
            <div class="ref">{{ $ref }}</div>
            <div>Screened: {{ $screenedOn }}</div>
            <div>Generated: {{ now()->format('d F Y, H:i') }}</div>
        </div>
    </div>

    <div class="title-bar">
        <h2>AML Screening Report — Ad-hoc</h2>
        <p>{{ $tenant->name }} — Compliance Portal</p>
    </div>

    @if($isMatch)
    <div class="result-match">
        <h3>⚠ Potential Match Found</h3>
        <p>{{ $totalHits }} potential match(es) identified for "{{ $log->query }}". Review and disposition required before proceeding.</p>
    </div>
    @else
    <div class="result-clear">
        <h3>✓ Clear — No Matches Found</h3>
        <p>No sanctions, PEP, or adverse media hits found for "{{ $log->query }}".</p>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Subject details</div>
        <table class="info-grid">
            <tr><td>Name screened</td><td>{{ $log->query }}</td></tr>
            <tr><td>Type</td><td>{{ $log->entity_type === 'entity' ? 'Corporate entity' : 'Individual' }}</td></tr>
            <tr><td>Screening reference</td><td>{{ $ref }}</td></tr>
            <tr><td>Screening date</td><td>{{ $screenedOn }}</td></tr>
            <tr><td>Screened by</td><td>{{ $log->screener?->name ?? '—' }}</td></tr>
            <tr><td>Source</td><td>Ad-hoc (not linked to a client profile)</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Sanctions &amp; watchlists screened</div>
        <div class="lists-box">
            <p>UAE Targeted Financial Sanctions (Cabinet Resolution No. 74 of 2020)</p>
            <p>UN Security Council Consolidated Sanctions List</p>
            <p>OFAC — SDN (Specially Designated Nationals) List</p>
            <p>EU Consolidated Sanctions List</p>
            <p>UK HM Treasury Financial Sanctions List</p>
            <p>FATF High-Risk &amp; Other Monitored Jurisdictions</p>
            <p>World Bank Debarred Parties List</p>
            <p>Politically Exposed Persons (PEP) Database</p>
            <p>Adverse Media Database</p>
        </div>
        <p style="font-size:8.5pt;color:#666;margin-top:6px;">Screening performed via Blue Arrow Sentinel AML platform (aml.bluearrow.ae)</p>
    </div>

    <div class="section">
        <div class="section-title">
            {{ $log->entity_type === 'entity' ? 'Company' : 'Individual' }}: {{ $log->query }}
            @if($isMatch) — <span style="color:#dc2626;">⚠ MATCH</span>
            @else — <span style="color:#16a34a;">✓ CLEAR</span>
            @endif
        </div>
        @if(!empty($hits))
        <table class="hits-table">
            <thead>
                <tr><th>#</th><th>Name</th><th>Type</th><th>Risk level</th><th>List</th><th>Match score</th></tr>
            </thead>
            <tbody>
                @foreach($hits as $i => $hit)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $hit['name'] ?? 'Unknown' }}</strong>
                        @if(!empty($hit['programs']) && is_array($hit['programs']))
                        <br><small style="color:#666">{{ implode(', ', array_slice($hit['programs'], 0, 3)) }}</small>
                        @endif
                    </td>
                    <td>{{ $hit['type'] ?? '—' }}</td>
                    <td>
                        @if(!empty($hit['riskLevel']))
                        <span class="badge-{{ strtolower($hit['riskLevel']) === 'critical' ? 'critical' : (strtolower($hit['riskLevel']) === 'high' ? 'high' : 'medium') }}">
                            {{ $hit['riskLevel'] }}
                        </span>
                        @else —
                        @endif
                    </td>
                    <td>{{ $hit['list']['name'] ?? '—' }}</td>
                    <td>{{ isset($hit['matchScore']) ? $hit['matchScore'].'%' : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="color:#16a34a;font-size:9.5pt;padding:8px 0;">✓ No matches found.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Compliance notes</div>
        <p style="font-size:9.5pt;color:#334155;line-height:1.6;">
            This screening report has been generated in accordance with the UAE Federal Decree-Law No. 20 of 2018 on Anti-Money Laundering and the Cabinet Decision No. 10 of 2019. The screening was conducted as part of the Customer Due Diligence (CDD) / Know Your Customer (KYC) process required for Designated Non-Financial Businesses and Professions (DNFBPs) under CBUAE supervision.
        </p>
    </div>

    <div class="sig-section">
        <div class="sig-box">
            <div style="height:40px;"></div>
            <div class="name">{{ $tenant->mlro_name ?? '________________________' }}</div>
            <div>{{ $tenant->mlro_name ? 'Money Laundering Reporting Officer' : 'MLRO / Compliance Officer' }}</div>
            <div>{{ $tenant->name }}</div>
            <div style="margin-top:4px;color:#666;">Date: ___________________</div>
        </div>
        <div class="sig-box">
            <div style="height:40px;"></div>
            <div class="name">________________________</div>
            <div>Senior Management</div>
            <div>{{ $tenant->name }}</div>
            <div style="margin-top:4px;color:#666;">Date: ___________________</div>
        </div>
    </div>

    <div class="footer">
        <p>CONFIDENTIAL — This document contains information subject to AML/CFT regulations. Unauthorised disclosure is prohibited.</p>
        <p style="margin-top:3px;">{{ $tenant->name }} · {{ $tenant->contact_email ?? '' }} · Generated {{ now()->format('d M Y H:i') }}</p>
    </div>

</div>
</body>
</html>
