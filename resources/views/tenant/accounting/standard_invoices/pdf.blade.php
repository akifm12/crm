<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; padding-bottom: 12px; }
        .title { font-size: 18px; font-weight: bold; color: #111; }
        .doc-type { font-size: 14px; font-weight: bold; text-align: center; margin: 4px 0; letter-spacing: 1px; }
        .muted { color: #777; font-size: 9px; }
        .text-right { text-align: right; }
        .party-box { border: 1px solid #ccc; padding: 8px; margin-bottom: 10px; }
        .items th { background: #f0f0f0; text-align: left; padding: 5px 6px; font-size: 8px; text-transform: uppercase; border: 1px solid #ddd; }
        .items td { padding: 5px 6px; border: 1px solid #eee; font-size: 9px; }
        .totals-table td { padding: 3px 6px; }
        .totals-table .grand { font-weight: bold; font-size: 12px; border-top: 1px solid #333; }
        .vat-table th { background: #f0f0f0; padding: 4px 6px; font-size: 8px; text-transform: uppercase; border: 1px solid #ddd; }
        .vat-table td { padding: 4px 6px; font-size: 9px; border: 1px solid #eee; }
        .words-box { border: 1px solid #ccc; padding: 8px; margin-top: 10px; font-size: 9px; }
        .signature-line { border-top: 1px solid #333; padding-top: 4px; font-size: 9px; text-align: center; }
    </style>
</head>
<body>

@php
    $vatBuckets = [
        'standard'       => ['label' => 'Standard Rate (5%)', 'taxable' => 0, 'tax' => 0],
        'reverse_charge' => ['label' => 'Reverse Charge', 'taxable' => 0, 'tax' => 0],
        'zero_rated'     => ['label' => 'Zero Rated', 'taxable' => 0, 'tax' => 0],
        'exempt'         => ['label' => 'Exempt', 'taxable' => 0, 'tax' => 0],
    ];
    foreach ($invoice->lines as $line) {
        $t = $line->vat_treatment ?? 'standard';
        if (isset($vatBuckets[$t])) {
            $vatBuckets[$t]['taxable'] += $line->amount;
            $vatBuckets[$t]['tax']     += $line->vat_amount;
        }
    }
    $docType = $moduleType === 'real_estate' ? 'RENT INVOICE' : 'TAX INVOICE';
    $settings = $tenant->settings ?? [];
    $trn = $settings['trn'] ?? '';
@endphp

{{-- Top header --}}
<table class="header-table">
    <tr>
        <td style="width:55%">
            <div class="title">{{ $tenant->name }}</div>
            @if($trn)
                <div class="muted">TRN: {{ $trn }}</div>
            @endif
            @if(!empty($settings['address']))<div class="muted">{{ $settings['address'] }}</div>@endif
            @if(!empty($settings['phone']))<div class="muted">{{ $settings['phone'] }}</div>@endif
            @if(!empty($settings['email']))<div class="muted">{{ $settings['email'] }}</div>@endif
        </td>
        <td style="width:45%; text-align:right">
            <div class="doc-type">{{ $docType }}</div>
            <table style="width:auto; margin-left:auto; font-size:9px">
                <tr><td style="color:#777; padding:2px 4px">Invoice No.:</td><td style="font-weight:bold; padding:2px 4px">{{ $invoice->invoice_number }}</td></tr>
                <tr><td style="color:#777; padding:2px 4px">Date:</td><td style="padding:2px 4px">{{ $invoice->invoice_date->format('d M Y') }}</td></tr>
                @if($invoice->due_date)
                <tr><td style="color:#777; padding:2px 4px">Due:</td><td style="padding:2px 4px">{{ $invoice->due_date->format('d M Y') }}</td></tr>
                @endif
                @if($invoice->reference)
                <tr><td style="color:#777; padding:2px 4px">Ref:</td><td style="padding:2px 4px">{{ $invoice->reference }}</td></tr>
                @endif
            </table>
        </td>
    </tr>
</table>

{{-- Party boxes --}}
<table style="margin-bottom:12px">
    <tr>
        <td style="width:48%; vertical-align:top; padding-right:10px">
            <div class="party-box">
                <div style="font-size:8px; color:#777; text-transform:uppercase; margin-bottom:4px">Billed To</div>
                <div style="font-weight:bold">{{ $invoice->client_name }}</div>
                @if($invoice->client_vat_number)
                    <div class="muted">TRN: {{ $invoice->client_vat_number }}</div>
                @endif
            </div>
        </td>
        <td style="width:52%; vertical-align:top">
            <div class="party-box">
                <div style="font-size:8px; color:#777; text-transform:uppercase; margin-bottom:4px">From</div>
                <div style="font-weight:bold">{{ $tenant->name }}</div>
                @if($trn)<div class="muted">TRN: {{ $trn }}</div>@endif
                @if(!empty($settings['address']))<div class="muted">{{ $settings['address'] }}</div>@endif
            </div>
        </td>
    </tr>
</table>

{{-- Line items --}}
<table class="items" style="margin-bottom:12px">
    <thead>
        <tr>
            <th style="width:45%">Description</th>
            <th style="width:8%; text-align:right">Qty</th>
            <th style="width:14%; text-align:right">Unit Price</th>
            <th style="width:12%; text-align:center">VAT</th>
            <th style="width:10%; text-align:right">Net</th>
            <th style="width:11%; text-align:right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->lines as $line)
        <tr>
            <td>{{ $line->description }}</td>
            <td style="text-align:right">{{ rtrim(rtrim(number_format($line->quantity, 4), '0'), '.') }}</td>
            <td style="text-align:right">{{ number_format($line->unit_price, 2) }}</td>
            <td style="text-align:center">
                @if($line->vat_treatment === 'standard') {{ $line->vat_rate }}%
                @elseif($line->vat_treatment === 'zero_rated') 0%
                @elseif($line->vat_treatment === 'exempt') Exempt
                @elseif($line->vat_treatment === 'reverse_charge') RC
                @endif
            </td>
            <td style="text-align:right">{{ number_format($line->amount, 2) }}</td>
            <td style="text-align:right; font-weight:bold">{{ number_format($line->line_total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- VAT breakdown + totals --}}
<table style="margin-bottom:10px">
    <tr>
        <td style="width:55%; vertical-align:top; padding-right:10px">
            <table class="vat-table">
                <thead>
                    <tr>
                        <th style="text-align:left">VAT Category</th>
                        <th style="text-align:right">Taxable (AED)</th>
                        <th style="text-align:right">VAT (AED)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vatBuckets as $bucket)
                        @if($bucket['taxable'] > 0 || $bucket['tax'] > 0)
                        <tr>
                            <td>{{ $bucket['label'] }}</td>
                            <td style="text-align:right">{{ number_format($bucket['taxable'], 2) }}</td>
                            <td style="text-align:right">{{ number_format($bucket['tax'], 2) }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </td>
        <td style="width:45%; vertical-align:top">
            <table class="totals-table" style="width:100%">
                <tr>
                    <td style="color:#555">Subtotal</td>
                    <td class="text-right">AED {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td style="color:#555">VAT</td>
                    <td class="text-right">AED {{ number_format($invoice->vat_total, 2) }}</td>
                </tr>
                <tr class="grand">
                    <td>Total Due</td>
                    <td class="text-right">AED {{ number_format($invoice->total, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@if($invoice->notes)
<div class="words-box">
    <strong>Notes:</strong> {{ $invoice->notes }}
</div>
@endif

{{-- Signatures --}}
<table style="margin-top:40px">
    <tr>
        <td style="width:40%; padding-right:20px">
            <div class="signature-line">Authorised Signature</div>
        </td>
        <td style="width:40%">
            <div class="signature-line">Received By</div>
        </td>
        <td style="width:20%"></td>
    </tr>
</table>

</body>
</html>
