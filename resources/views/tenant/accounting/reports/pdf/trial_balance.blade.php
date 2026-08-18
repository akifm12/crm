<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        .title { font-size: 16px; font-weight: bold; color: #111; }
        .muted { color: #777; font-size: 9px; }
        .text-right { text-align: right; }
        .report-header { margin-bottom: 14px; }
        .report-title { font-size: 13px; font-weight: bold; margin-top: 6px; }
        th { background: #f0f0f0; text-align: left; padding: 5px 6px; font-size: 8px; text-transform: uppercase; border: 1px solid #ddd; }
        td { padding: 5px 6px; border: 1px solid #eee; font-size: 9px; }
        .totals-row td { font-weight: bold; border-top: 2px solid #333; }
        .generated { margin-top: 20px; font-size: 8px; color: #999; }
    </style>
</head>
<body>

<div class="report-header">
    <div class="title">{{ $tenant->name }}</div>
    @if($tenant->address)<div class="muted">{{ $tenant->address }}</div>@endif
    <div class="report-title">Trial Balance</div>
    <div class="muted">As of {{ $asOf->format('d M Y') }}</div>
</div>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Account</th>
            <th class="text-right">Debit (AED)</th>
            <th class="text-right">Credit (AED)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        @continue($row['debit'] == 0 && $row['credit'] == 0)
        <tr>
            <td>{{ $row['account']->code }}</td>
            <td>{{ $row['account']->name }}</td>
            <td class="text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
            <td class="text-right">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totals-row">
            <td colspan="2">Totals</td>
            <td class="text-right">{{ number_format($totalDebit, 2) }}</td>
            <td class="text-right">{{ number_format($totalCredit, 2) }}</td>
        </tr>
    </tfoot>
</table>

@if($metalSummary->isNotEmpty())
<div style="margin-top: 20px;">
    <div class="report-title" style="font-size: 11px; margin-bottom: 4px;">Physical Metal Inventory Quantities</div>
    <div class="muted" style="margin-bottom: 6px;">On-hand positions — monetary values are in the trial balance above</div>
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
</div>
@endif

<div class="generated">Generated {{ now()->format('d M Y H:i') }}</div>

</body>
</html>
