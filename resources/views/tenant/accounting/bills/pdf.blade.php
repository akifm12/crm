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
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; }
    </style>
</head>
<body>

@php
    $vatBuckets = [
        'standard'       => ['label' => 'Input VAT (Standard)', 'taxable' => 0, 'tax' => 0],
        'reverse_charge' => ['label' => 'Input VAT (Reverse Charge)', 'taxable' => 0, 'tax' => 0],
        'zero_rated'     => ['label' => 'Zero Rated', 'taxable' => 0, 'tax' => 0],
        'exempt'         => ['label' => 'Exempt', 'taxable' => 0, 'tax' => 0],
    ];
    foreach ($bill->lines as $line) {
        $key = $line->vat_treatment ?? 'standard';
        $vatBuckets[$key]['taxable'] += (float) $line->amount;
        $vatBuckets[$key]['tax']     += (float) $line->vat_amount;
    }
@endphp

<table class="header-table">
    <tr>
        <td width="55%">
            <div class="title">{{ $tenant->name }}</div>
            @if($tenant->address)<div class="muted">{{ $tenant->address }}</div>@endif
            @if($tenant->phone)<div class="muted">{{ $tenant->phone }}</div>@endif
            @if($tenant->vat_trn)<div class="muted" style="margin-top:4px"><strong>TRN:</strong> {{ $tenant->vat_trn }}</div>@endif
        </td>
        <td width="45%" class="text-right">
            <div class="doc-type">EXPENSE BILL</div>
            <table style="margin-top:6px">
                <tr><td class="muted">Bill Number</td><td class="text-right"><strong>{{ $bill->bill_number }}</strong></td></tr>
                <tr><td class="muted">Bill Date</td><td class="text-right">{{ $bill->bill_date->format('d/m/Y') }}</td></tr>
                @if($bill->due_date)
                <tr><td class="muted">Due Date</td><td class="text-right">{{ $bill->due_date->format('d/m/Y') }}</td></tr>
                @endif
                @if($bill->reference)
                <tr><td class="muted">Supplier Ref</td><td class="text-right">{{ $bill->reference }}</td></tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<table>
    <tr>
        <td width="55%" style="vertical-align: top;">
            <div class="party-box">
                <strong>{{ $bill->supplier_name }}</strong>
                @if($bill->supplier_vat_number)<br>TRN: {{ $bill->supplier_vat_number }}@endif
            </div>
        </td>
        <td width="45%" style="vertical-align: top; padding-left: 10px;">
            <div class="party-box">
                <strong>{{ $tenant->name }}</strong>
                @if($tenant->vat_trn)<br>TRN: {{ $tenant->vat_trn }}@endif
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>#</th>
            <th>Description</th>
            <th>Category</th>
            <th class="text-right">Amount</th>
            <th class="text-right">VAT %</th>
            <th class="text-right">VAT</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bill->lines as $line)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $line->description }}</td>
            <td>{{ \App\Models\Bill::CATEGORIES[$line->category] ?? $line->category }}</td>
            <td class="text-right">{{ number_format($line->amount, 2) }}</td>
            <td class="text-right">{{ $line->vat_rate > 0 ? number_format($line->vat_rate, 0).'%' : '—' }}</td>
            <td class="text-right">{{ number_format($line->vat_amount, 2) }}</td>
            <td class="text-right">{{ number_format($line->line_total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table style="margin-top: 10px;">
    <tr>
        <td width="55%" style="vertical-align: top;">
            <table class="vat-table">
                <thead>
                    <tr>
                        <th>Tax Type</th>
                        <th class="text-right">Taxable Amount</th>
                        <th class="text-right">Tax Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vatBuckets as $bucket)
                    @continue($bucket['taxable'] == 0 && $bucket['tax'] == 0)
                    <tr>
                        <td>{{ $bucket['label'] }}</td>
                        <td class="text-right">{{ number_format($bucket['taxable'], 2) }}</td>
                        <td class="text-right">{{ number_format($bucket['tax'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </td>
        <td width="45%" style="vertical-align: top; padding-left: 10px;">
            <table class="totals-table">
                <tr><td>Subtotal</td><td class="text-right">{{ number_format($bill->subtotal, 2) }}</td></tr>
                <tr><td>Total VAT</td><td class="text-right">{{ number_format($bill->vat_total, 2) }}</td></tr>
                <tr class="grand"><td>Total</td><td class="text-right">AED {{ number_format($bill->total, 2) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="words-box">
    <strong>AED:</strong> {{ \App\Support\NumberToWords::words($bill->total) }} Only
</div>

@if($bill->notes)
<p style="margin-top: 14px; font-size: 9px;"><strong>Notes:</strong> {{ $bill->notes }}</p>
@endif

<div class="page-footer">
    <table style="margin-top: 55px;">
        <tr>
            <td width="45%" class="signature-line">{{ $bill->supplier_name }}</td>
            <td width="10%"></td>
            <td width="45%" class="signature-line">{{ $tenant->name }}</td>
        </tr>
    </table>
</div>

</body>
</html>
