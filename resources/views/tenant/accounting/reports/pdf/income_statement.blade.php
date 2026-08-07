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
        .net-row td { font-weight: bold; font-size: 12px; border-top: 2px solid #333; padding-top: 8px; }
        .generated { margin-top: 20px; font-size: 8px; color: #999; }
    </style>
</head>
<body>

<div class="report-header">
    <div class="title">{{ $tenant->name }}</div>
    @if($tenant->address)<div class="muted">{{ $tenant->address }}</div>@endif
    <div class="report-title">Income Statement</div>
    <div class="muted">{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</div>
</div>

<div class="section-title">Income</div>
<table>
    <tbody>
        @foreach($income as $row)
        <tr><td>{{ $row['account']->name }}</td><td class="text-right">{{ number_format($row['balance'], 2) }}</td></tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totals-row"><td>Total Income</td><td class="text-right">{{ number_format($totalIncome, 2) }}</td></tr>
    </tfoot>
</table>

<div class="section-title">Expenses</div>
<table>
    <tbody>
        @foreach($expense as $row)
        <tr><td>{{ $row['account']->name }}</td><td class="text-right">{{ number_format($row['balance'], 2) }}</td></tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totals-row"><td>Total Expenses</td><td class="text-right">{{ number_format($totalExpense, 2) }}</td></tr>
    </tfoot>
</table>

<table>
    <tr class="net-row">
        <td>Net {{ $netIncome >= 0 ? 'Profit' : 'Loss' }}</td>
        <td class="text-right">{{ number_format(abs($netIncome), 2) }} AED</td>
    </tr>
</table>

<div class="generated">Generated {{ now()->format('d M Y H:i') }}</div>

</body>
</html>
