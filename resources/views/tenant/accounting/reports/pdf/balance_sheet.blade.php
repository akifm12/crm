<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .title { font-size: 16px; font-weight: bold; color: #111; }
        .muted { color: #777; font-size: 9px; }
        .text-right { text-align: right; }
        .report-header { margin-bottom: 14px; }
        .report-title { font-size: 13px; font-weight: bold; margin-top: 6px; }
        .section-title { font-size: 10px; font-weight: bold; margin: 10px 0 4px; text-transform: uppercase; color: #444; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        .totals-row td { font-weight: bold; border-top: 2px solid #333; border-bottom: none; }
        .warning { margin-top: 10px; padding: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 9px; }
        .generated { margin-top: 20px; font-size: 8px; color: #999; }
    </style>
</head>
<body>

<div class="report-header">
    <div class="title">{{ $tenant->name }}</div>
    @if($tenant->address)<div class="muted">{{ $tenant->address }}</div>@endif
    <div class="report-title">Balance Sheet</div>
    <div class="muted">As of {{ $asOf->format('d M Y') }}</div>
</div>

<div class="section-title">Assets</div>
<table>
    <tbody>
        @foreach($assets as $row)
        <tr><td>{{ $row['account']->name }}</td><td class="text-right">{{ number_format($row['balance'], 2) }}</td></tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totals-row"><td>Total Assets</td><td class="text-right">{{ number_format($totalAssets, 2) }}</td></tr>
    </tfoot>
</table>

<div class="section-title">Liabilities</div>
<table>
    <tbody>
        @foreach($liabilities as $row)
        <tr><td>{{ $row['account']->name }}</td><td class="text-right">{{ number_format($row['balance'], 2) }}</td></tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totals-row"><td>Total Liabilities</td><td class="text-right">{{ number_format($totalLiabilities, 2) }}</td></tr>
    </tfoot>
</table>

<div class="section-title">Equity</div>
<table>
    <tbody>
        @foreach($equity as $row)
        <tr><td>{{ $row['account']->name }}</td><td class="text-right">{{ number_format($row['balance'], 2) }}</td></tr>
        @endforeach
        <tr><td>Current Period Earnings</td><td class="text-right">{{ number_format($netIncome, 2) }}</td></tr>
    </tbody>
    <tfoot>
        <tr class="totals-row"><td>Total Equity</td><td class="text-right">{{ number_format($totalEquity + $netIncome, 2) }}</td></tr>
    </tfoot>
</table>

@unless($balanced)
<div class="warning">Assets do not equal Liabilities + Equity — please review recent journal entries.</div>
@endunless

@if($metalSummary->isNotEmpty())
<div class="section-title">Physical Metal Inventory</div>
<table>
    <thead>
        <tr>
            <th>Metal</th>
            <th class="text-right">Quantity on Hand (g)</th>
            <th class="text-right">Book Value (AED)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($metalSummary as $row)
        <tr>
            <td>{{ ucfirst($row->metal_type) }}</td>
            <td class="text-right">{{ number_format($row->total_grams, 3) }}</td>
            <td class="text-right">{{ number_format($row->total_value, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totals-row">
            <td>Total</td>
            <td class="text-right">{{ number_format($metalSummary->sum('total_grams'), 3) }} g</td>
            <td class="text-right">{{ number_format($metalSummary->sum('total_value'), 2) }}</td>
        </tr>
    </tfoot>
</table>
<div class="muted" style="margin-bottom: 10px;">Supplementary — physical inventory already captured in asset account balances above.</div>
@endif

<div class="generated">Generated {{ now()->format('d M Y H:i') }}</div>

</body>
</html>
