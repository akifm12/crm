<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php
$fmt = fn($v, $d=2) => number_format(abs($v), $d);
$bal = fn($v, $d=2) => $v < 0 ? '('.$fmt($v,$d).')' : ($v > 0 ? $fmt($v,$d) : '—');
?>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .title { font-size: 15px; font-weight: bold; color: #111; }
        .muted { color: #777; font-size: 8px; }
        .report-title { font-size: 12px; font-weight: bold; margin: 4px 0 2px; }
        th { background: #f3f3f3; text-align: left; padding: 4px 5px; font-size: 7.5px; text-transform: uppercase; border-bottom: 1px solid #ccc; }
        th.r { text-align: right; }
        td { padding: 3.5px 5px; border-bottom: 1px solid #efefef; font-size: 8.5px; }
        td.r { text-align: right; font-family: monospace; }
        .totals td { font-weight: bold; border-top: 1.5px solid #bbb; border-bottom: none; }
        .generated { margin-top: 16px; font-size: 7.5px; color: #aaa; }
    </style>
</head>
<body>

<div class="report-header" style="margin-bottom:10px;">
    <div class="title">{{ $tenant->name }}</div>
    @if($tenant->address)<div class="muted">{{ $tenant->address }}</div>@endif
    @if($tenant->trn_number)<div class="muted">TRN: {{ $tenant->trn_number }}</div>@endif
    <div class="report-title">Statement of Accounts</div>
    <div class="muted">{{ $client->displayName() }}@if($client->trn_number) · TRN: {{ $client->trn_number }}@endif &nbsp;—&nbsp; as of {{ $asOf->format('d M Y') }}</div>
</div>

{{-- Summary row --}}
<table style="margin-bottom:12px;">
    <tr>
        <td style="width:40%; border:1px solid #ddd; padding:6px 8px;">
            <div style="font-size:7.5px;color:#888;text-transform:uppercase;margin-bottom:2px;">Balance</div>
            <div style="font-size:13px;font-weight:bold;">{{ $bal($statement['balance']) }} AED</div>
            <div style="font-size:7.5px;color:#888;">
                @if($statement['balance'] > 0)Client owes the business
                @elseif($statement['balance'] < 0)Business owes the client
                @else Settled@endif
            </div>
        </td>
        <td style="border:none;"></td>
    </tr>
</table>

{{-- Ledger --}}
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th class="r">Debit</th>
            <th class="r">Credit</th>
            <th class="r">Balance</th>
        </tr>
    </thead>
    <tbody>
        @if($from)
        <tr>
            <td>{{ $from->format('d M Y') }}</td>
            <td><em>Opening balance</em></td>
            <td></td>
            <td></td>
            <td class="r">{{ $bal($statement['opening_balance']) }}</td>
        </tr>
        @endif
        @forelse($statement['rows'] as $row)
        <tr>
            <td>{{ $row['date']->format('d M Y') }}</td>
            <td>{{ $row['description'] }}</td>
            <td class="r">{{ $row['amount'] > 0 ? $fmt($row['amount']) : '' }}</td>
            <td class="r">{{ $row['amount'] < 0 ? $fmt($row['amount']) : '' }}</td>
            <td class="r">{{ $bal($row['balance']) }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#999;">No posted transactions for this client yet.</td></tr>
        @endforelse
    </tbody>
    @if(collect($statement['rows'])->isNotEmpty())
    <tfoot>
        <tr class="totals">
            <td colspan="2" style="text-align:right;">Closing balance</td>
            <td></td>
            <td></td>
            <td class="r">{{ $bal($statement['balance']) }}</td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="generated">Generated {{ now()->format('d M Y H:i') }}</div>

</body>
</html>
