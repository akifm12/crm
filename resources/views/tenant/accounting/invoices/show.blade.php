@extends('layouts.tenant')
@section('title', $invoice->invoice_number . ' — ' . $tenant->name)
@section('page-title', $invoice->invoice_number)
@section('page-subtitle', ucfirst($invoice->invoice_type) . ' invoice — ' . $invoice->client->displayName())

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4 flex items-center justify-between">
    <span>{{ session('success') }}</span>
    @if(session('receipt_payment_id'))
    <a href="{{ route('tenant.accounting.payments.receipt', [$tenant->slug, session('receipt_payment_id')]) }}" target="_blank"
       class="ml-4 px-3 py-1 text-xs font-semibold bg-green-700 text-white rounded hover:bg-green-800 whitespace-nowrap">
        Print Receipt
    </a>
    @endif
</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

<div class="flex items-center gap-2 mb-5">
    <span class="text-xs font-medium px-2 py-0.5 rounded-full
        {{ $invoice->status === 'posted' ? 'bg-green-50 text-green-700' : ($invoice->status === 'void' ? 'bg-red-50 text-red-600' : ($invoice->status === 'pending_fix' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-500')) }}">
        {{ $invoice->status === 'pending_fix' ? 'Pending Fix' : ucfirst($invoice->status) }}
    </span>
    @if($invoice->pricing_type === 'unfixed')
    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">Unfixed</span>
    @endif

    @if($invoice->status === 'draft')
    <a href="{{ route('tenant.accounting.invoices.edit', [$tenant->slug, $invoice->id]) }}"
       class="px-4 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Edit draft
    </a>
    <form method="POST" action="{{ route('tenant.accounting.invoices.post', [$tenant->slug, $invoice->id]) }}"
          onsubmit="return confirm('Post this invoice? This moves inventory{{ $invoice->pricing_type === 'fixed' ? ' and posts the journal entry' : '' }}.')">
        @csrf
        <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Post invoice
        </button>
    </form>
    <form method="POST" action="{{ route('tenant.accounting.invoices.destroy', [$tenant->slug, $invoice->id]) }}"
          onsubmit="return confirm('Delete this draft?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-3 py-1.5 text-xs text-red-500 hover:text-red-700">Delete draft</button>
    </form>
    @endif

    @if($invoice->status === 'pending_fix')
    <a href="{{ route('tenant.accounting.invoices.fix.form', [$tenant->slug, $invoice->id]) }}"
       class="px-4 py-1.5 text-xs font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700">
        Fix Price
    </a>
    <form method="POST" action="{{ route('tenant.accounting.invoices.void', [$tenant->slug, $invoice->id]) }}"
          onsubmit="return confirm('Void this invoice? The inventory movement will be reversed. This cannot be undone.')" class="inline-flex items-center gap-1">
        @csrf
        <input type="text" name="reason" placeholder="Reason (optional)" class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg w-32">
        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50">
            Void
        </button>
    </form>
    @endif

    @if($invoice->status === 'posted')
    <a href="{{ route('tenant.accounting.invoices.edit', [$tenant->slug, $invoice->id]) }}"
       class="px-4 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Edit
    </a>
    <a href="{{ route('tenant.accounting.invoices.pdf', [$tenant->slug, $invoice->id]) }}" target="_blank"
       class="px-4 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Download PDF
    </a>
    @if($invoice->journalEntry)
    <a href="{{ route('tenant.accounting.journal.show', [$tenant->slug, $invoice->journalEntry->id]) }}"
       class="px-4 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        View journal entry
    </a>
    @endif
    <form method="POST" action="{{ route('tenant.accounting.invoices.void', [$tenant->slug, $invoice->id]) }}"
          onsubmit="return confirm('Void this invoice? Inventory movements and the journal entry will be reversed. This cannot be undone.')" class="inline-flex items-center gap-1">
        @csrf
        <input type="text" name="reason" placeholder="Reason (optional)" class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg w-32">
        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50">
            Void
        </button>
    </form>
    @endif
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5 max-w-3xl">
    <div class="grid grid-cols-4 gap-4 text-sm mb-4">
        <div><p class="text-xs text-gray-400 mb-0.5">Date</p><p class="text-gray-800">{{ $invoice->invoice_date->format('d M Y') }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Currency</p><p class="text-gray-800">{{ $invoice->currency_code }} @ {{ $invoice->exchange_rate }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Amount paid</p><p class="text-gray-800">{{ number_format($invoice->amount_paid, 2) }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Outstanding</p><p class="text-gray-800">{{ number_format($invoice->outstandingBalance(), 2) }}</p></div>
    </div>
    @if($invoice->party_reference)
    <div class="grid grid-cols-4 gap-4 text-sm mb-4">
        <div><p class="text-xs text-gray-400 mb-0.5">Party reference</p><p class="text-gray-800">{{ $invoice->party_reference }}</p></div>
    </div>
    @endif
    @if(! empty($invoice->metal_rates))
    <div class="space-y-2 mb-4">
        @foreach($invoice->metal_rates as $metal => $rate)
            @php
                $ozRate = (float) ($rate['usd_per_oz'] ?? 0);
                $aedRate = (float) ($rate['usd_aed_rate'] ?? 0);
                $perGram = $ozRate > 0 ? ($ozRate / 31.1035) * $aedRate : 0;
            @endphp
            @if($ozRate > 0)
            <div class="grid grid-cols-4 gap-4 text-sm">
                <div><p class="text-xs text-gray-400 mb-0.5">{{ ucfirst($metal) }} price/oz (USD)</p><p class="text-gray-800">{{ number_format($ozRate, 2) }}</p></div>
                <div><p class="text-xs text-gray-400 mb-0.5">USD → AED rate</p><p class="text-gray-800">{{ number_format($aedRate, 4) }}</p></div>
                <div><p class="text-xs text-gray-400 mb-0.5">{{ ucfirst($metal) }} rate/gram (AED)</p><p class="text-gray-800">{{ number_format($perGram, 5) }}</p></div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    <table class="w-full text-sm mb-3">
        <thead>
            <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                <th class="py-2">Type</th>
                <th class="py-2">Description</th>
                <th class="py-2 text-right">Pure (g)</th>
                <th class="py-2 text-right">Price</th>
                <th class="py-2 text-right">Metal VAT</th>
                <th class="py-2 text-right">Making</th>
                <th class="py-2 text-right">Making VAT</th>
                <th class="py-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($invoice->lines as $line)
            <tr>
                <td class="py-2 text-gray-500">{{ str_replace('_', ' ', $line->line_type) }}</td>
                <td class="py-2">{{ $line->description }}</td>
                <td class="py-2 text-right font-mono">{{ $line->quantity_grams ? number_format($line->quantity_grams, 3) : '—' }}</td>
                <td class="py-2 text-right font-mono">{{ $line->unit_price ? number_format($line->unit_price, 4) : '—' }}</td>
                <td class="py-2 text-right font-mono">{{ number_format($line->metal_vat_amount, 2) }}</td>
                <td class="py-2 text-right font-mono">{{ $line->making_charge_amount > 0 ? number_format($line->making_charge_amount, 2) : '—' }}</td>
                <td class="py-2 text-right font-mono">{{ $line->making_vat_amount > 0 ? number_format($line->making_vat_amount, 2) : '—' }}</td>
                <td class="py-2 text-right font-mono">{{ number_format($line->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-100 text-sm">
                <td colspan="7" class="pt-2 text-right text-gray-500 pr-2">{{ $invoice->invoice_type === 'exchange' ? 'Subtotal (making charges — metal legs are a swap, not owed)' : 'Subtotal (metal + making)' }}</td>
                <td class="pt-2 text-right font-mono">{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr class="text-sm">
                <td colspan="7" class="text-right text-gray-500 pr-2">VAT</td>
                <td class="text-right font-mono">{{ number_format($invoice->vat_total, 2) }}</td>
            </tr>
            @if($invoice->premium_amount > 0)
            <tr class="text-sm">
                <td colspan="7" class="text-right text-gray-500 pr-2">Premium</td>
                <td class="text-right font-mono">{{ number_format($invoice->premium_amount, 2) }}</td>
            </tr>
            @endif
            @if($invoice->discount_amount > 0)
            <tr class="text-sm">
                <td colspan="7" class="text-right text-gray-500 pr-2">Discount</td>
                <td class="text-right font-mono">-{{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="text-sm font-semibold text-gray-800">
                <td colspan="7" class="text-right pr-2">Total</td>
                <td class="text-right font-mono">{{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($invoice->notes)
    <p class="text-xs text-gray-500 mt-3">{{ $invoice->notes }}</p>
    @endif
</div>

@if(in_array($invoice->status, ['posted', 'pending_fix']))
<div class="bg-white rounded-xl border border-gray-200 p-5 max-w-3xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ $invoice->status === 'pending_fix' ? 'Deposits' : 'Payments' }}</h3>
    @if($invoice->status === 'pending_fix')
    <p class="text-xs text-gray-500 mb-3">No price has been fixed yet, so there's no balance to pay down — any amount recorded here is held as a deposit and automatically applied once you fix the price.</p>
    @endif

    <table class="w-full text-sm mb-4">
        <tbody class="divide-y divide-gray-50">
            @forelse($invoice->payments as $payment)
            <tr>
                <td class="py-2 text-gray-500">{{ $payment->payment_date->format('d M Y') }}</td>
                <td class="py-2 text-gray-500">{{ str_replace('_', ' ', $payment->method ?? '—') }}</td>
                <td class="py-2 text-gray-500">{{ $payment->purpose ?: $payment->reference }}</td>
                <td class="py-2 text-right font-mono">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-3 text-center text-gray-400">No {{ $invoice->status === 'pending_fix' ? 'deposits' : 'payments' }} recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($invoice->status === 'pending_fix' || ! $invoice->isFullyPaid())
    <form method="POST" action="{{ route('tenant.accounting.invoices.payments.store', [$tenant->slug, $invoice->id]) }}" class="flex flex-wrap gap-2 items-end">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
            <input type="date" name="payment_date" required value="{{ now()->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
            <input type="number" step="0.01" min="0.01" name="amount" required value="{{ $invoice->status === 'posted' ? $invoice->outstandingBalance() : '' }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg w-32">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Method</label>
            <select name="method" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank transfer</option>
                <option value="card">Card</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Reference</label>
            <input type="text" name="reference" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        @if($invoice->status === 'pending_fix')
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Purpose</label>
            <input type="text" name="purpose" value="Deposit" class="px-3 py-2 text-sm border border-gray-200 rounded-lg w-32">
        </div>
        @endif
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            {{ $invoice->status === 'pending_fix' ? 'Record deposit' : 'Record payment' }}
        </button>
    </form>
    @endif
</div>
@endif

@endsection
