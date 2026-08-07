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
        .capitalize { text-transform: capitalize; }
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
    <div class="report-title">VAT Summary</div>
    <div class="muted">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</div>
</div>

<table>
    <thead>
        <tr>
            <th>VAT Treatment</th>
            <th class="text-right">Subtotal (AED)</th>
            <th class="text-right">VAT (AED)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
        <tr>
            <td class="capitalize">{{ str_replace('_', ' ', $row['vat_treatment']) }}</td>
            <td class="text-right">{{ number_format($row['subtotal'], 2) }}</td>
            <td class="text-right">{{ number_format($row['vat'], 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="3">No posted invoices in this period.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="totals-row">
            <td>Totals</td>
            <td class="text-right">{{ number_format($totalSubtotal, 2) }}</td>
            <td class="text-right">{{ number_format($totalVat, 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="generated">Generated {{ now()->format('d M Y H:i') }}</div>

</body>
</html>
