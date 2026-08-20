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
        .text-center { text-align: center; }
        .report-header { margin-bottom: 14px; }
        .report-title { font-size: 13px; font-weight: bold; margin-top: 6px; }
        th { background: #f0f0f0; text-align: left; padding: 5px 6px; font-size: 8px; text-transform: uppercase; border: 1px solid #ddd; }
        td { padding: 5px 6px; border: 1px solid #eee; font-size: 9px; }
        .totals-row td { font-weight: bold; border-top: 2px solid #333; }
        .generated { margin-top: 20px; font-size: 8px; color: #999; }
        .bucket-grid { display: table; width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .bucket-cell { display: table-cell; width: 20%; padding: 6px 8px; border: 1px solid #ddd; text-align: center; }
        .bucket-label { font-size: 8px; color: #777; margin-bottom: 3px; }
        .bucket-amount { font-size: 11px; font-weight: bold; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-orange { background: #fed7aa; color: #9a3412; }
        .badge-red { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="report-header">
    <div class="title">{{ $tenant->name }}</div>
    @if($tenant->trn_number)<div class="muted">TRN: {{ $tenant->trn_number }}</div>@endif
    <div class="report-title">Accounts Receivable Aging</div>
    <div class="muted">As of {{ now()->format('d M Y') }}</div>
</div>

{{-- Bucket summary --}}
<div class="bucket-grid">
    @php
        $buckets = ['current'=>'Current','1_30'=>'1–30 days','31_60'=>'31–60 days','61_90'=>'61–90 days','90_plus'=>'90+ days'];
    @endphp
    @foreach($buckets as $key => $label)
    <div class="bucket-cell">
        <div class="bucket-label">{{ $label }}</div>
        <div class="bucket-amount">{{ number_format($totals[$key], 2) }}</div>
    </div>
    @endforeach
</div>

@if($rows->isEmpty())
    <p style="color:#777;font-size:9px">No outstanding receivables.</p>
@else
<table>
    <thead>
        <tr>
            <th>Invoice #</th><th>Client</th><th>Invoice Date</th><th>Due Date</th>
            <th class="text-right">Total</th><th class="text-right">Paid</th>
            <th class="text-right">Balance</th><th class="text-center">Age</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        @php
            $over = $row['days_over'];
            $ageLabel = ($over === null || $over <= 0) ? 'Current' : $over . 'd';
            $ageClass = match($row['bucket']) {
                'current' => 'badge-green',
                '1_30'    => 'badge-yellow',
                '31_60'   => 'badge-orange',
                default   => 'badge-red',
            };
        @endphp
        <tr>
            <td>{{ $row['invoice']->invoice_number }}</td>
            <td>{{ $row['invoice']->client_name }}</td>
            <td>{{ $row['invoice']->invoice_date->format('d M Y') }}</td>
            <td>{{ $row['invoice']->due_date?->format('d M Y') ?? '—' }}</td>
            <td class="text-right">{{ number_format($row['invoice']->total, 2) }}</td>
            <td class="text-right">{{ number_format($row['invoice']->amount_paid, 2) }}</td>
            <td class="text-right">{{ number_format($row['balance'], 2) }}</td>
            <td class="text-center"><span class="badge {{ $ageClass }}">{{ $ageLabel }}</span></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totals-row">
            <td colspan="6">Total Outstanding</td>
            <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endif

<div class="generated">Generated {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
