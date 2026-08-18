<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php
// Build the same combined rows used by the web view
$metals = $metalStatement ? $metalStatement['metals'] : [];
$metalByInvoice = [];
if ($metalStatement) {
    foreach ($metalStatement['rows'] as $mr) {
        $inv = $mr['invoice_number']; $metal = $mr['metal_type'];
        if (!isset($metalByInvoice[$inv][$metal])) {
            $metalByInvoice[$inv][$metal] = ['in' => 0.0, 'out' => 0.0, 'date' => $mr['date'], 'description' => $mr['description'], 'invoice_type' => $mr['invoice_type']];
        }
        $metalByInvoice[$inv][$metal]['in']  += $mr['grams_in']  ?? 0;
        $metalByInvoice[$inv][$metal]['out'] += $mr['grams_out'] ?? 0;
    }
}
$metalBalanceTimeline = []; $_rb = array_fill_keys($metals, 0.0);
if ($metalStatement) {
    foreach ($metalStatement['rows'] as $mr) {
        $_rb[$mr['metal_type']] = $mr['balance'];
        $metalBalanceTimeline[$mr['invoice_number']] = $_rb;
    }
}
$metalRunning = array_fill_keys($metals, 0.0);
$usedMetalInvoices = [];
$combinedRows = collect();
foreach ($statement['rows'] as $cashRow) {
    $invNum = $cashRow['invoice']?->invoice_number ?? null;
    $amount = $cashRow['amount'];
    $moves = [];
    if ($invNum && isset($metalByInvoice[$invNum]) && !in_array($invNum, $usedMetalInvoices)) {
        $moves = $metalByInvoice[$invNum];
        foreach ($metals as $m) { if (isset($metalBalanceTimeline[$invNum][$m])) $metalRunning[$m] = $metalBalanceTimeline[$invNum][$m]; }
        $usedMetalInvoices[] = $invNum;
    }
    $combinedRows->push(['date' => $cashRow['date'], 'description' => $cashRow['description'],
        'cash_dr' => $amount > 0 ? $amount : null, 'cash_cr' => $amount < 0 ? abs($amount) : null,
        'cash_bal' => $cashRow['balance'], 'metal_moves' => $moves, 'metal_bal' => $metalRunning]);
}
foreach ($metalByInvoice as $invNum => $moves) {
    if (in_array($invNum, $usedMetalInvoices)) continue;
    $fm = reset($moves);
    foreach ($metals as $m) { if (isset($metalBalanceTimeline[$invNum][$m])) $metalRunning[$m] = $metalBalanceTimeline[$invNum][$m]; }
    $combinedRows->push(['date' => $fm['date'], 'description' => $fm['description'] ?: ucfirst($fm['invoice_type']),
        'cash_dr' => null, 'cash_cr' => null, 'cash_bal' => $statement['balance'],
        'metal_moves' => $moves, 'metal_bal' => $metalRunning]);
}
$combinedRows = $combinedRows->sortBy('date')->values();
$fmt = fn($v, $d=2) => number_format(abs($v), $d);
$bal = fn($v, $d=2) => $v < 0 ? '('.$fmt($v,$d).')' : ($v > 0 ? $fmt($v,$d) : '—');
?>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .title { font-size: 15px; font-weight: bold; color: #111; }
        .muted { color: #777; font-size: 8px; }
        .report-title { font-size: 12px; font-weight: bold; margin: 4px 0 2px; }
        .summary { display: flex; gap: 12px; margin-bottom: 12px; }
        .summary-box { border: 1px solid #ddd; padding: 6px 10px; flex: 1; }
        .summary-label { font-size: 7.5px; color: #888; text-transform: uppercase; margin-bottom: 2px; }
        .summary-val { font-size: 13px; font-weight: bold; }
        th { background: #f3f3f3; text-align: left; padding: 4px 5px; font-size: 7.5px; text-transform: uppercase; border-bottom: 1px solid #ccc; }
        th.r { text-align: right; }
        td { padding: 3.5px 5px; border-bottom: 1px solid #efefef; font-size: 8.5px; }
        td.r { text-align: right; font-family: monospace; }
        td.sep { border-left: 1px solid #ddd; }
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
        <td style="width:30%; border:1px solid #ddd; padding:6px 8px;">
            <div style="font-size:7.5px;color:#888;text-transform:uppercase;margin-bottom:2px;">Cash balance</div>
            <div style="font-size:13px;font-weight:bold;">{{ $bal($statement['balance']) }} AED</div>
            <div style="font-size:7.5px;color:#888;">
                @if($statement['balance'] > 0)Client owes the business
                @elseif($statement['balance'] < 0)Business owes the client
                @else Settled@endif
            </div>
        </td>
        @foreach($metals as $metal)
        @php $mBal = $metalStatement['balances'][$metal] ?? 0; @endphp
        <td style="width:20%; border:1px solid #ddd; padding:6px 8px;">
            <div style="font-size:7.5px;color:#888;text-transform:uppercase;margin-bottom:2px;">{{ ucfirst($metal) }} held on account</div>
            <div style="font-size:13px;font-weight:bold;">{{ $bal($mBal, 3) }} g</div>
        </td>
        @endforeach
        <td style="border:none;"></td>
    </tr>
</table>

{{-- Combined ledger --}}
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th class="r">Cash Dr</th>
            <th class="r">Cash Cr</th>
            <th class="r">Cash Bal</th>
            @foreach($metals as $metal)
            <th class="r sep">{{ ucfirst($metal) }} In (g)</th>
            <th class="r">{{ ucfirst($metal) }} Out (g)</th>
            <th class="r">{{ ucfirst($metal) }} Bal</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($combinedRows as $row)
        <tr>
            <td style="white-space:nowrap;">{{ $row['date']->format('d M Y') }}</td>
            <td>{{ $row['description'] }}</td>
            <td class="r">{{ $row['cash_dr'] !== null ? number_format($row['cash_dr'], 2) : '' }}</td>
            <td class="r">{{ $row['cash_cr'] !== null ? number_format($row['cash_cr'], 2) : '' }}</td>
            <td class="r">{{ $bal($row['cash_bal']) }}</td>
            @foreach($metals as $metal)
            @php $mv = $row['metal_moves'][$metal] ?? null; $mb = $row['metal_bal'][$metal] ?? 0; @endphp
            <td class="r sep">{{ $mv && $mv['in'] > 0 ? number_format($mv['in'], 3) : '' }}</td>
            <td class="r">{{ $mv && $mv['out'] > 0 ? number_format($mv['out'], 3) : '' }}</td>
            <td class="r">{{ $bal($mb, 3) }}</td>
            @endforeach
        </tr>
        @empty
        <tr><td colspan="{{ 5 + count($metals)*3 }}">No posted transactions for this client yet.</td></tr>
        @endforelse
    </tbody>
    @if($combinedRows->isNotEmpty())
    <tfoot>
        <tr class="totals">
            <td colspan="2" class="r" style="font-size:7.5px;text-transform:uppercase;color:#666;">Closing balance</td>
            <td class="r"></td>
            <td class="r"></td>
            <td class="r">{{ $bal($statement['balance']) }}</td>
            @foreach($metals as $metal)
            @php $fb = $metalStatement['balances'][$metal] ?? 0; @endphp
            <td class="sep"></td><td></td>
            <td class="r">{{ $bal($fb, 3) }}</td>
            @endforeach
        </tr>
    </tfoot>
    @endif
</table>

<div class="generated">Generated {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
