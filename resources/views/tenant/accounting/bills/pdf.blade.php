<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $bill->bill_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
        .total-row { font-weight: bold; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>BILL: {{ $bill->bill_number }}</h1>
    <p><strong>Vendor:</strong> {{ $bill->vendor_name }} &nbsp; <strong>Date:</strong> {{ $bill->bill_date }} &nbsp; <strong>Due:</strong> {{ $bill->due_date }}</p>
    <p><strong>Status:</strong> {{ $bill->status }} &nbsp; <strong>Ref:</strong> {{ $bill->vendor_ref ?: '—' }}</p>
    <table>
        <thead>
            <tr><th>Account</th><th>Description</th><th class="right">Qty</th><th class="right">Price</th><th class="right">VAT</th><th class="right">Total</th></tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
            <tr>
                <td>{{ $line->account->code ?? '' }} {{ $line->account->name ?? '' }}</td>
                <td>{{ $line->description }}</td>
                <td class="right">{{ $line->quantity }}</td>
                <td class="right">{{ number_format($line->unit_price, 2) }}</td>
                <td class="right">{{ number_format($line->vat_amount, 2) }}</td>
                <td class="right">{{ number_format($line->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="5" class="right">Subtotal</td><td class="right">{{ number_format($bill->subtotal, 2) }}</td></tr>
            <tr><td colspan="5" class="right">VAT</td><td class="right">{{ number_format($bill->total_vat_amount, 2) }}</td></tr>
            <tr class="total-row"><td colspan="5" class="right">TOTAL (AED)</td><td class="right">{{ number_format($bill->total_amount, 2) }}</td></tr>
        </tfoot>
    </table>
    @if($bill->notes)<p><em>Notes: {{ $bill->notes }}</em></p>@endif
</body>
</html>
