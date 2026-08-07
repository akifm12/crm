<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; padding-bottom: 12px; }
        .title { font-size: 18px; font-weight: bold; color: #111; }
        .doc-type { font-size: 13px; font-weight: bold; text-align: center; margin: 4px 0; }
        .muted { color: #777; font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .party-box { border: 1px solid #ccc; padding: 8px; margin-bottom: 10px; }
        .items th { background: #f0f0f0; text-align: left; padding: 5px 6px; font-size: 8px; text-transform: uppercase; border: 1px solid #ddd; }
        .items td { padding: 5px 6px; border: 1px solid #eee; font-size: 9px; }
        .totals-table td { padding: 3px 6px; }
        .totals-table .grand { font-weight: bold; font-size: 12px; border-top: 1px solid #333; }
        .vat-table th { background: #f0f0f0; padding: 4px 6px; font-size: 8px; text-transform: uppercase; border: 1px solid #ddd; }
        .vat-table td { padding: 4px 6px; font-size: 9px; border: 1px solid #eee; }
        .words-box { border: 1px solid #ccc; padding: 8px; margin-top: 10px; font-size: 9px; }
        .declaration { font-size: 8px; color: #555; margin-top: 14px; line-height: 1.5; }
        .signature-line { border-top: 1px solid #333; padding-top: 4px; font-size: 9px; text-align: center; }
        .page-footer { position: fixed; bottom: 0; left: 0; right: 0; }
    </style>
</head>
<body>

@php
    $isFixed = $invoice->pricing_type === 'fixed';
    $docTypeLabel = strtoupper($invoice->invoice_type) . ' - ' . ($isFixed ? 'FIXED' : 'UNFIXED');

    // Bucket line amounts by VAT treatment, metal and making separately, matching the
    // reference invoice's "Output VAT" / "Output VAT on Making" / "Zero Rated" / "Exempt" rows.
    $vatBuckets = [
        'standard'       => ['label' => 'Output VAT', 'taxable' => 0, 'tax' => 0],
        'making'         => ['label' => 'Output VAT on Making', 'taxable' => 0, 'tax' => 0],
        'zero_rated'     => ['label' => 'Output VAT Zero Rated', 'taxable' => 0, 'tax' => 0],
        'exempt'         => ['label' => 'Output VAT Exempted', 'taxable' => 0, 'tax' => 0],
        'reverse_charge' => ['label' => 'Output VAT Reverse Charged', 'taxable' => 0, 'tax' => 0],
    ];
    $hasZeroRatedOrReverseCharge = false;
    foreach ($invoice->lines as $line) {
        $metalKey = $line->metal_vat_treatment === 'standard' ? 'standard' : $line->metal_vat_treatment;
        $vatBuckets[$metalKey]['taxable'] += (float) $line->line_subtotal;
        $vatBuckets[$metalKey]['tax'] += (float) $line->metal_vat_amount;

        if ((float) $line->making_charge_amount > 0) {
            $vatBuckets['making']['taxable'] += (float) $line->making_charge_amount;
            $vatBuckets['making']['tax'] += (float) $line->making_vat_amount;
        }

        if (in_array($line->metal_vat_treatment, ['zero_rated', 'reverse_charge'])) {
            $hasZeroRatedOrReverseCharge = true;
        }
    }
    $totalGrossWeight = $invoice->lines->sum('gross_weight_grams');
    $totalPureWeight = $invoice->lines->sum('quantity_grams');
    $totalPcs = $invoice->lines->sum('pcs');
    $totalMetalAmount = $invoice->lines->sum('line_subtotal');
    $totalMakingAmount = $invoice->lines->sum('making_charge_amount');
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
            <div class="doc-type">{{ $docTypeLabel }}<br>TAX INVOICE</div>
            <table style="margin-top:6px">
                <tr><td class="muted">Invoice Number</td><td class="text-right"><strong>{{ $invoice->invoice_number }}</strong></td></tr>
                <tr><td class="muted">Doc. Date</td><td class="text-right">{{ $invoice->invoice_date->format('d/m/Y') }}</td></tr>
                <tr><td class="muted">Currency</td><td class="text-right">{{ $invoice->currency_code }}</td></tr>
                @foreach($invoice->metal_rates ?? [] as $metal => $rate)
                    @php
                        $ozRate = (float) ($rate['usd_per_oz'] ?? 0);
                        $aedRate = (float) ($rate['usd_aed_rate'] ?? 0);
                        $perGram = $ozRate > 0 ? ($ozRate / 31.1035) * $aedRate : 0;
                    @endphp
                    @if($ozRate > 0)
                    <tr><td class="muted">{{ ucfirst($metal) }} Rate/Oz (USD)</td><td class="text-right">{{ number_format($ozRate, 2) }}</td></tr>
                    <tr><td class="muted">{{ ucfirst($metal) }} Rate/Gram (AED)</td><td class="text-right">{{ number_format($perGram, 5) }}</td></tr>
                    @endif
                @endforeach
            </table>
        </td>
    </tr>
</table>

<table>
    <tr>
        <td width="55%" style="vertical-align: top;">
            <div class="party-box">
                <strong>{{ $invoice->client->displayName() }}</strong><br>
                @if($invoice->client->trn_number)TRN: {{ $invoice->client->trn_number }}<br>@endif
                @if($invoice->client->registered_address){{ $invoice->client->registered_address }}<br>@endif
                @if($invoice->client->phone){{ $invoice->client->phone }}@endif
            </div>
        </td>
        <td width="45%" style="vertical-align: top; padding-left: 10px;">
            @if($invoice->party_reference)<div class="muted">Party Invoice Reference: {{ $invoice->party_reference }}</div>@endif
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>SNo</th>
            <th>Item Details</th>
            <th class="text-right">Pcs</th>
            <th class="text-right">Gross Wt</th>
            <th class="text-right">Purity</th>
            <th class="text-right">Pure Wt</th>
            <th class="text-right">Rate/g</th>
            <th class="text-right">Metal Amt</th>
            <th class="text-right">Making Amt</th>
            <th class="text-right">VAT</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->lines as $line)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $line->description }}</td>
            <td class="text-right">{{ $line->pcs ?? '—' }}</td>
            <td class="text-right">{{ $line->gross_weight_grams ? number_format($line->gross_weight_grams, 3) : '—' }}</td>
            <td class="text-right">{{ $line->purity ? number_format($line->purity, 3) : '—' }}</td>
            <td class="text-right">{{ $line->quantity_grams ? number_format($line->quantity_grams, 3) : '—' }}</td>
            <td class="text-right">{{ $line->unit_price ? number_format($line->unit_price, 4) : '—' }}</td>
            <td class="text-right">{{ number_format($line->line_subtotal, 2) }}</td>
            <td class="text-right">{{ $line->making_charge_amount > 0 ? number_format($line->making_charge_amount, 2) : '—' }}</td>
            <td class="text-right">{{ number_format($line->metal_vat_amount + $line->making_vat_amount, 2) }}</td>
            <td class="text-right">{{ number_format($line->line_total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table>
    <tr>
        <td width="60%" style="vertical-align: top;">
            <table class="vat-table" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Tax Type</th>
                        <th class="text-right">Taxable</th>
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
        <td width="40%" style="vertical-align: top; padding-left: 10px;">
            <table class="totals-table" style="margin-top: 10px;">
                @if($invoice->invoice_type !== 'exchange')
                <tr><td>Metal Amount</td><td class="text-right">{{ number_format($totalMetalAmount, 2) }}</td></tr>
                @endif
                @if($totalMakingAmount > 0)
                <tr><td>Making Amount</td><td class="text-right">{{ number_format($totalMakingAmount, 2) }}</td></tr>
                @endif
                <tr><td>Total Before VAT</td><td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                <tr><td>Total VAT</td><td class="text-right">{{ number_format($invoice->vat_total, 2) }}</td></tr>
                @if($invoice->premium_amount > 0)
                <tr><td>Premium</td><td class="text-right">{{ number_format($invoice->premium_amount, 2) }}</td></tr>
                @endif
                @if($invoice->discount_amount > 0)
                <tr><td>Discount</td><td class="text-right">-{{ number_format($invoice->discount_amount, 2) }}</td></tr>
                @endif
                <tr class="grand"><td>Total Invoice Amount</td><td class="text-right">{{ number_format($invoice->total, 2) }} {{ $invoice->currency_code }}</td></tr>
            </table>
            @if($invoice->invoice_type === 'exchange')
            <p class="muted" style="margin-top: 4px;">Metal-for-metal swap — see line items above. Total Invoice Amount is the cash payable (making charges @if($totalMakingAmount > 0)+ VAT @endif only).</p>
            @endif
        </td>
    </tr>
</table>

<div class="words-box">
    <strong>{{ $invoice->currency_code }}:</strong> {{ \App\Support\NumberToWords::words($invoice->total) }} Only<br>
    <strong>Gold Grams:</strong> {{ \App\Support\NumberToWords::words($totalPureWeight) }}
    @php $mg = round((fmod($totalPureWeight, 1)) * 1000); @endphp
    @if($mg > 0) and {{ \App\Support\NumberToWords::words($mg) }} Mg @endif Only
</div>

@if($invoice->notes)
<p style="margin-top: 14px; font-size: 9px;"><strong>Notes:</strong> {{ $invoice->notes }}</p>
@endif

<div class="page-footer">
    @if($hasZeroRatedOrReverseCharge)
    <div class="declaration">
        <strong>Declaration Note:</strong> This invoice contains the following declarations as defined in Clause 1 of
        Article (2) of Cabinet Decision No. (25) of 2018 dated 22nd May 2018 issued under Federal Decree Law, and
        Cabinet Decision No. (127) of 2024. This is a provisional declaration as the template of declaration is still
        to be provided by the FTA.<br>
        1) The acquisition of the Goods is for the purpose of resale or use to produce or manufacture any of the Goods
        by the Recipient Registrant in the State.<br>
        2) The Recipient is registered on the date of supply.<br>
        3) The Recipient shall calculate Tax on the value of the Goods supplied to him.<br>
        4) The Recipient of the Goods shall calculate the Tax on the value of the Goods supplied to him and shall be
        responsible for all applicable Tax obligations related to the supply and for calculating the Due Tax in respect
        of such supplies. This invoice does not contain any VAT liability as the above conditions are satisfied and
        hereby accepted and acknowledged by the Recipient Registrant in the State.<br>
        5) This invoice is issued in accordance with clause 1 and 2 of article of Cabinet Decision No. (127) of 2024
        and does not contain any VAT liability as the above conditions are satisfied and acknowledged by the recipient.
    </div>
    @endif

    <table style="margin-top: 55px;">
        <tr>
            <td width="45%" class="signature-line">{{ $invoice->client->displayName() }}</td>
            <td width="10%"></td>
            <td width="45%" class="signature-line">{{ $tenant->name }}</td>
        </tr>
    </table>
</div>

</body>
</html>
