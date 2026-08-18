<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $payment->direction === 'in' ? 'Cash Receipt' : 'Payment Voucher' }} — {{ $tenant->name }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: system-ui, -apple-system, sans-serif;
    font-size: 13px;
    color: #1a1a2e;
    background: #f4f4f8;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 1rem 4rem;
  }

  /* ── Screen toolbar ── */
  .toolbar {
    width: 100%;
    max-width: 680px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
  }
  .toolbar a {
    font-size: 0.8rem;
    color: #555;
    text-decoration: none;
  }
  .toolbar a:hover { color: #1a1a2e; }
  .btn-print {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: #1a1a2e;
    color: #fff;
    border: none;
    padding: 0.45rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    border-radius: 5px;
    letter-spacing: 0.02em;
  }
  .btn-print:hover { background: #2d3154; }

  /* ── Document ── */
  .doc {
    width: 100%;
    max-width: 680px;
    background: #fff;
    border: 1px solid #dde0ea;
    border-radius: 8px;
    padding: 3rem 3.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  }

  /* ── Header ── */
  .doc-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #1a1a2e;
    margin-bottom: 1.75rem;
  }
  .company-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
    letter-spacing: 0.01em;
  }
  .company-meta {
    font-size: 0.75rem;
    color: #666;
    margin-top: 3px;
    line-height: 1.6;
  }
  .doc-title-block { text-align: right; }
  .doc-type {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a2e;
    letter-spacing: -0.01em;
  }
  .doc-ref {
    font-size: 0.75rem;
    color: #888;
    margin-top: 4px;
    font-family: ui-monospace, monospace;
  }

  /* ── Meta grid ── */
  .meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem 2rem;
    margin-bottom: 2rem;
  }
  .meta-item label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-weight: 600;
    color: #999;
    display: block;
    margin-bottom: 3px;
  }
  .meta-item .val {
    font-size: 0.9rem;
    color: #1a1a2e;
    font-weight: 500;
  }

  /* ── Amount box ── */
  .amount-box {
    background: #f7f8fc;
    border: 1px solid #dde0ea;
    border-radius: 6px;
    padding: 1.5rem 2rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .amount-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-weight: 600;
    color: #888;
    margin-bottom: 4px;
  }
  .amount-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a2e;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
  }
  .amount-currency {
    font-size: 0.85rem;
    font-weight: 400;
    color: #888;
    margin-left: 4px;
  }
  .direction-badge {
    padding: 0.35rem 0.85rem;
    border-radius: 4px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .badge-in  { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
  .badge-out { background: #fef3f2; color: #dc2626; border: 1px solid #fecaca; }

  /* ── Invoice reference ── */
  .invoice-ref {
    border: 1px solid #dde0ea;
    border-radius: 6px;
    padding: 0.85rem 1.25rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
  }
  .invoice-ref .inv-label { color: #888; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; }
  .invoice-ref .inv-val   { color: #1a1a2e; font-weight: 600; font-family: ui-monospace, monospace; }

  /* ── Footer ── */
  .doc-footer {
    border-top: 1px solid #eee;
    padding-top: 1.5rem;
    margin-top: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    font-size: 0.75rem;
    color: #aaa;
  }
  .sig-line {
    border-top: 1px solid #bbb;
    padding-top: 0.4rem;
    width: 180px;
    text-align: center;
    font-size: 0.72rem;
    color: #999;
  }

  /* ── Print ── */
  @media print {
    @page { size: A4; margin: 20mm 22mm; }
    body { background: #fff; padding: 0; display: block; }
    .toolbar { display: none; }
    .doc {
      border: none;
      border-radius: 0;
      box-shadow: none;
      padding: 0;
      max-width: 100%;
    }
    .doc-header { border-bottom: 2px solid #000; }
    .amount-box { background: #f7f8fc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .badge-in  { background: #ecfdf5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .badge-out { background: #fef3f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body>

{{-- Screen toolbar --}}
<div class="toolbar">
  <a href="javascript:history.back()">← Back</a>
  <button class="btn-print" onclick="window.print()">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
    Print / Save PDF
  </button>
</div>

{{-- Document --}}
<div class="doc">

  {{-- Header --}}
  <div class="doc-header">
    <div>
      <div class="company-name">{{ $tenant->name }}</div>
      @if($tenant->address)
      <div class="company-meta">{{ $tenant->address }}</div>
      @endif
      @if($tenant->trn_number)
      <div class="company-meta">TRN: {{ $tenant->trn_number }}</div>
      @endif
    </div>
    <div class="doc-title-block">
      <div class="doc-type">{{ $payment->direction === 'in' ? 'Cash Receipt' : 'Payment Voucher' }}</div>
      <div class="doc-ref">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>
  </div>

  {{-- Meta --}}
  <div class="meta-grid">
    <div class="meta-item">
      <label>{{ $payment->direction === 'in' ? 'Received from' : 'Paid to' }}</label>
      <div class="val">{{ $payment->client?->displayName() ?? '—' }}</div>
    </div>
    <div class="meta-item">
      <label>Date</label>
      <div class="val">{{ $payment->payment_date->format('d M Y') }}</div>
    </div>
    <div class="meta-item">
      <label>Payment method</label>
      <div class="val">{{ match($payment->method) {
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card'          => 'Card',
        default         => ucfirst($payment->method ?? 'Other'),
      } }}</div>
    </div>
    @if($payment->reference)
    <div class="meta-item">
      <label>Reference / Cheque no.</label>
      <div class="val">{{ $payment->reference }}</div>
    </div>
    @endif
    @if($payment->purpose)
    <div class="meta-item">
      <label>Purpose / Narration</label>
      <div class="val">{{ $payment->purpose }}</div>
    </div>
    @endif
    <div class="meta-item">
      <label>Recorded by</label>
      <div class="val">{{ $payment->creator?->name ?? '—' }}</div>
    </div>
  </div>

  {{-- Amount --}}
  <div class="amount-box">
    <div>
      <div class="amount-label">Amount {{ $payment->direction === 'in' ? 'received' : 'paid' }}</div>
      <div class="amount-value">
        {{ number_format($payment->amount, 2) }}<span class="amount-currency">AED</span>
      </div>
    </div>
    <span class="direction-badge {{ $payment->direction === 'in' ? 'badge-in' : 'badge-out' }}">
      {{ $payment->direction === 'in' ? 'Receipt' : 'Payment' }}
    </span>
  </div>

  {{-- Linked invoice --}}
  @if($payment->invoice)
  <div class="invoice-ref">
    <div>
      <div class="inv-label">Applied to invoice</div>
      <div class="inv-val">{{ $payment->invoice->invoice_number }}</div>
    </div>
    <div style="text-align:right">
      <div class="inv-label">Invoice type</div>
      <div class="inv-val" style="font-family:system-ui">{{ ucfirst($payment->invoice->invoice_type) }}</div>
    </div>
  </div>
  @endif

  {{-- Footer / signatures --}}
  <div class="doc-footer">
    <div>
      <div style="margin-bottom: 1.5rem"></div>
      <div class="sig-line">Received by</div>
    </div>
    <div style="text-align:right">
      <div style="margin-bottom: 1.5rem"></div>
      <div class="sig-line">Authorised signatory</div>
    </div>
  </div>

  <div style="margin-top: 1.5rem; font-size: 0.7rem; color: #bbb; text-align:center;">
    Generated {{ now()->format('d M Y, H:i') }} · {{ $tenant->name }}
  </div>

</div>
</body>
</html>
