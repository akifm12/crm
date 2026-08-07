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
        .individuals-row td { background: #f7f7f7; }
        .generated { margin-top: 20px; font-size: 8px; color: #999; }
    </style>
</head>
<body>

<div class="report-header">
    <div class="title">{{ $tenant->name }}</div>
    @if($tenant->address)<div class="muted">{{ $tenant->address }}</div>@endif
    <div class="report-title">Client Balances</div>
    <div class="muted">Generated {{ now()->format('d M Y H:i') }}</div>
</div>

<table>
    <thead>
        <tr>
            <th>Client</th>
            <th>Type</th>
            <th class="text-right">Balance (AED)</th>
            <th class="text-right">Sales Revenue (AED)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($corporate as $row)
        <tr>
            <td>{{ $row['client']->displayName() }}</td>
            <td class="capitalize">{{ str_replace('_', ' ', $row['client']->client_type) }}</td>
            <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
            <td class="text-right">{{ number_format($row['sales_revenue'], 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="4">No corporate client activity posted yet.</td></tr>
        @endforelse
        @if($individuals['balance'] != 0 || $individuals['sales_revenue'] != 0)
        <tr class="individuals-row">
            <td>Individuals (combined)</td>
            <td>—</td>
            <td class="text-right">{{ number_format($individuals['balance'], 2) }}</td>
            <td class="text-right">{{ number_format($individuals['sales_revenue'], 2) }}</td>
        </tr>
        @endif
    </tbody>
    <tfoot>
        <tr class="totals-row">
            <td colspan="2">Totals</td>
            <td class="text-right">{{ number_format($corporate->sum('balance') + $individuals['balance'], 2) }}</td>
            <td class="text-right">{{ number_format($corporate->sum('sales_revenue') + $individuals['sales_revenue'], 2) }}</td>
        </tr>
    </tfoot>
</table>

<div class="generated">Balance: positive means the client owes the dealer; negative means the dealer owes the client.</div>

</body>
</html>
