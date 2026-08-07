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
        .balance-box { border: 1px solid #ccc; padding: 8px; margin-bottom: 14px; }
        .balance-amount { font-size: 16px; font-weight: bold; }
        th { background: #f0f0f0; text-align: left; padding: 5px 6px; font-size: 8px; text-transform: uppercase; border: 1px solid #ddd; }
        td { padding: 5px 6px; border: 1px solid #eee; font-size: 9px; }
        .generated { margin-top: 20px; font-size: 8px; color: #999; }
    </style>
</head>
<body>

<div class="report-header">
    <div class="title">{{ $tenant->name }}</div>
    @if($tenant->address)<div class="muted">{{ $tenant->address }}</div>@endif
    <div class="report-title">Statement of Accounts</div>
    <div class="muted">{{ $client->displayName() }} — as of {{ $asOf->format('d M Y') }}</div>
</div>

<div class="balance-box">
    <div class="muted">Net balance as of {{ $asOf->format('d M Y') }}</div>
    <div class="balance-amount">{{ number_format(abs($statement['balance']), 2) }} AED</div>
    <div class="muted">
        @if($statement['balance'] > 0) Client owes the business
        @elseif($statement['balance'] < 0) Business owes the client
        @else Settled
        @endif
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th class="text-right">Amount</th>
            <th class="text-right">Balance</th>
        </tr>
    </thead>
    <tbody>
        @forelse($statement['rows'] as $row)
        <tr>
            <td>{{ $row['date']->format('d M Y') }}</td>
            <td>{{ $row['description'] }}</td>
            <td class="text-right">{{ $row['amount'] > 0 ? '+' : '' }}{{ number_format($row['amount'], 2) }}</td>
            <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="4">No posted transactions for this client yet.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="generated">Generated {{ now()->format('d M Y H:i') }}</div>

</body>
</html>
