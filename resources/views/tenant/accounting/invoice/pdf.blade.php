<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $invoice->invoice_number }}</title>
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 0; padding: 20px; }
    .header { display: flex; justify-content: space-between; margin-bottom: 24px; }
    .company-name { font-size: 20px; font-weight: bold; color: #1a1a1a; }
    .invoice-title { font-size: 22px; font-weight: bold; color: #2563eb; text-align: right; }
    .meta { display: flex; justify-content: space-between; margin-bottom: 20px; }
    .meta-block { line-height: 1.7; }
    .meta-label { font-weight: bold; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    thead tr { background: #f3f4f6; }
    th { padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: #555; border-bottom: 1px solid #ddd; }
    td { padding: 8px 10px; border-bottom: 1px solid #eee; }
    .text-right { text-align: right; }
    .totals { margin-left: auto; width: 240px; }
    .total-row { display: flex; justify-content: space-between; padding: 3px 0; }
    .total-final { font-weight: bold; font-size: 14px; border-top: 2px solid #111; padding-top: 4px; margin-top: 4px; }
    .footer { margin-top: 40px; border-top: 1px solid #eee; padding-top: 10px; font-size: 10px; color: #888; text-align: center; }
    .status-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
    .status-paid { background: #d1fae5; color: #065f46; }
    .status-approved { background: #dbeafe; color: #1e40af; }
    .status-draft { background: #f3f4f6; color: #374151; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="company-name">{{ $tenant->name }}</div>
        @if($tenant->trn_number ?? null)
        <div>TRN: {{ $tenant->trn_number }}</div>
        @endif
    </div>
    <div class="text-right">
        <div class="invoice-title">TAX INVOICE</div>
        <div style="font-size:14px;font-weight:bold;">{{ $invoice->invoice_number }}</div>
        <div class="status-badge status-{{ strtolower($invoice->attributes['status'] ?? 'draft') }}">{{ $invoice->STATUS }}</div>
    </div>
</div>

<div class="meta">
    <div class="meta-block">
        <div><span class="meta-label">Bill To:</span></div>
        <div style="font-weight:bold;">{{ $invoice->customer_name }}</div>
        @if($invoice->customer_trn)
        <div>TRN: {{ $invoice->customer_trn }}</div>
        @endif
        @if($invoice->customer_address)
        <div style="color:#555;">{{ $invoice->customer_address }}</div>
        @endif
    </div>
    <div class="meta-block text-right">
        <div><span class="meta-label">Invoice Date:</span> {{ $invoice->invoice_date }}</div>
        @if($invoice->due_date)
        <div><span class="meta-label">Due Date:</span> {{ $invoice->due_date }}</div>
        @endif
        @if($invoice->po_number)
        <div><span class="meta-label">PO Number:</span> {{ $invoice->po_number }}</div>
        @endif
        <div><span class="meta-label">Currency:</span> AED</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Description</th>
            <th class="text-right">Qty</th>
            <th>Unit</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Amount</th>
            <th class="text-right">VAT%</th>
            <th class="text-right">VAT</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lines as $i => $line)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>
                {{ $line->description }}
                @if($line->item && $line->item->gold_purity)
                <br><small style="color:#888;">Purity: {{ $line->item->gold_purity }}‰
                @if($line->gold_weight_grams) · {{ $line->gold_weight_grams }}g @endif
                </small>
                @endif
            </td>
            <td class="text-right">{{ $line->quantity }}</td>
            <td>{{ $line->unit }}</td>
            <td class="text-right">{{ number_format($line->unit_price, 4) }}</td>
            <td class="text-right">{{ number_format($line->line_amount, 2) }}</td>
            <td class="text-right">{{ $line->vat_rate }}%</td>
            <td class="text-right">{{ number_format($line->vat_amount, 2) }}</td>
            <td class="text-right">{{ number_format($line->total_amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <div class="total-row"><span>Subtotal</span><span>AED {{ number_format($invoice->subtotal, 2) }}</span></div>
    @if($invoice->discount_amount > 0)
    <div class="total-row"><span>Discount</span><span>- AED {{ number_format($invoice->discount_amount, 2) }}</span></div>
    @endif
    <div class="total-row"><span>VAT</span><span>AED {{ number_format($invoice->vat_amount, 2) }}</span></div>
    <div class="total-row total-final"><span>TOTAL DUE</span><span>AED {{ number_format($invoice->amount_due, 2) }}</span></div>
</div>

@if($invoice->notes)
<div style="margin-top:20px;"><strong>Notes:</strong> {{ $invoice->notes }}</div>
@endif

<div class="footer">This is a computer-generated document. {{ $tenant->name }}</div>
</body>
</html>
