@extends('layouts.bbook')
@section('title', $deal->deal_number . ' — ' . $tenant->name)
@section('page-title', $deal->deal_number)
@section('page-subtitle', ucfirst($deal->deal_type) . ' · ' . $deal->counterparty_name)

@section('content')

<div class="flex items-center gap-2 mb-5">
    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $deal->status === 'posted' ? 'bg-emerald-950/60 text-emerald-400' : ($deal->status === 'void' ? 'bg-red-950/60 text-red-400' : ($deal->status === 'pending_fix' ? 'bg-amber-950/60 text-amber-400' : 'bg-gray-800 text-gray-400')) }}">
        {{ $deal->status === 'pending_fix' ? 'Awaiting price fix' : ucfirst($deal->status) }}
    </span>

    @if($deal->status === 'draft')
    <a href="{{ route('bbook.deals.edit', [$tenant->slug, $deal->id]) }}"
       class="px-4 py-1.5 text-xs font-semibold text-gray-300 bg-white/5 border border-white/10 rounded-lg hover:bg-white/10">
        Edit draft
    </a>
    <form method="POST" action="{{ route('bbook.deals.post', [$tenant->slug, $deal->id]) }}"
          onsubmit="return confirm('Post this deal? This moves inventory and posts the B-Book journal entry.')">
        @csrf
        <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-600 rounded-lg hover:from-amber-300 hover:to-yellow-500">
            Post deal
        </button>
    </form>
    @endif

    @if($deal->status === 'pending_fix')
    <a href="{{ route('bbook.deals.fix-price.form', [$tenant->slug, $deal->id]) }}"
       class="px-4 py-1.5 text-xs font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-600 rounded-lg hover:from-amber-300 hover:to-yellow-500">
        Fix price
    </a>
    @endif

    @if(in_array($deal->status, ['draft', 'posted', 'pending_fix']))
    <form method="POST" action="{{ route('bbook.deals.void', [$tenant->slug, $deal->id]) }}"
          onsubmit="return confirm('Void this deal? This cannot be undone.')" class="inline-flex items-center gap-1">
        @csrf
        <input type="text" name="reason" placeholder="Reason (optional)" class="px-2 py-1.5 text-xs bg-black/40 border border-white/10 rounded-lg w-36 text-gray-200">
        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-red-400 bg-white/5 border border-red-900/40 rounded-lg hover:bg-red-950/30">
            Void
        </button>
    </form>
    @endif
</div>

<div class="card p-5 mb-5 max-w-3xl">
    <div class="grid grid-cols-4 gap-4 text-sm mb-4">
        <div><p class="text-xs text-gray-500 mb-0.5">Date</p><p class="text-gray-300">{{ $deal->deal_date->format('d M Y') }}</p></div>
        <div><p class="text-xs text-gray-500 mb-0.5">Type</p><p class="text-gray-300 capitalize">{{ $deal->deal_type }}{{ $deal->pricing_type === 'unfixed' ? ' · unfixed' : '' }}</p></div>
        <div><p class="text-xs text-gray-500 mb-0.5">Amount paid</p><p class="text-gray-300">{{ number_format($deal->amount_paid, 2) }}</p></div>
        <div><p class="text-xs text-gray-500 mb-0.5">Outstanding</p><p class="text-gray-300">{{ $deal->status === 'pending_fix' ? '—' : number_format($deal->outstandingBalance(), 2) }}</p></div>
    </div>
    <div class="grid grid-cols-4 gap-4 text-sm mb-4">
        @if($deal->client)
        <div><p class="text-xs text-gray-500 mb-0.5">Linked client</p><p class="text-gray-300">{{ $deal->client->displayName() }}</p></div>
        @endif
        @if($deal->party_reference)
        <div><p class="text-xs text-gray-500 mb-0.5">Party reference</p><p class="text-gray-300">{{ $deal->party_reference }}</p></div>
        @endif
    </div>

    <table class="w-full text-sm mb-3">
        <thead>
            <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-white/10">
                <th class="py-2">Description</th>
                @if($deal->deal_type === 'exchange')<th class="py-2">Leg</th>@endif
                <th class="py-2 text-right">Pure (g)</th>
                <th class="py-2 text-right">Rate</th>
                <th class="py-2 text-right">Making</th>
                <th class="py-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @foreach($deal->lines as $line)
            <tr>
                <td class="py-2 text-gray-300">{{ $line->description }}</td>
                @if($deal->deal_type === 'exchange')<td class="py-2 text-gray-500 text-xs">{{ str_replace('_', ' ', $line->line_type) }}</td>@endif
                <td class="py-2 text-right font-mono text-gray-400">{{ $line->quantity_grams && $line->line_type !== 'cash_topup' ? number_format($line->quantity_grams, 3) : '—' }}</td>
                <td class="py-2 text-right font-mono text-gray-400">{{ $line->unit_price ? number_format($line->unit_price, 4) : '—' }}</td>
                <td class="py-2 text-right font-mono text-gray-400">{{ $line->making_charge_amount > 0 ? number_format($line->making_charge_amount, 2) : '—' }}</td>
                <td class="py-2 text-right font-mono text-gray-300">{{ number_format($line->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if((float) $deal->premium_amount > 0)
            <tr class="text-xs">
                <td colspan="{{ $deal->deal_type === 'exchange' ? 5 : 4 }}" class="pt-2 text-right text-gray-500 pr-2">Premium</td>
                <td class="pt-2 text-right font-mono text-gray-400">{{ number_format($deal->premium_amount, 2) }}</td>
            </tr>
            @endif
            @if((float) $deal->discount_amount > 0)
            <tr class="text-xs">
                <td colspan="{{ $deal->deal_type === 'exchange' ? 5 : 4 }}" class="pt-1 text-right text-gray-500 pr-2">Discount</td>
                <td class="pt-1 text-right font-mono text-gray-400">-{{ number_format($deal->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="border-t border-white/10 text-sm font-semibold">
                <td colspan="{{ $deal->deal_type === 'exchange' ? 5 : 4 }}" class="pt-2 text-right text-gray-400 pr-2">Total</td>
                <td class="pt-2 text-right font-mono text-amber-400">{{ $deal->status === 'pending_fix' ? '— pending —' : number_format($deal->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($deal->notes)
    <p class="text-xs text-gray-500 mt-3">{{ $deal->notes }}</p>
    @endif
</div>

@if(in_array($deal->status, ['posted', 'pending_fix']))
<div class="card p-5 max-w-3xl">
    <h3 class="text-sm font-semibold text-gray-300 mb-3">{{ $deal->status === 'pending_fix' ? 'Deposits' : 'Payments' }}</h3>
    @if($deal->status === 'pending_fix')
    <p class="text-xs text-amber-600/70 mb-3">Booked as a deposit until the price is fixed, then applied against the invoice.</p>
    @endif
    <table class="w-full text-sm mb-4">
        <tbody class="divide-y divide-white/5">
            @forelse($deal->payments as $payment)
            <tr>
                <td class="py-2 text-gray-500">{{ $payment->payment_date->format('d M Y') }}</td>
                <td class="py-2 text-gray-500">{{ ucfirst($payment->direction === 'in' ? 'Receipt' : 'Payment') }}</td>
                <td class="py-2 text-gray-500">{{ str_replace('_', ' ', $payment->method ?? '—') }}</td>
                <td class="py-2 text-right font-mono text-gray-300">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-3 text-center text-gray-600">No payments recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(! $deal->isFullyPaid())
    <form method="POST" action="{{ route('bbook.deals.payments.store', [$tenant->slug, $deal->id]) }}" class="flex flex-wrap gap-2 items-end">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
            <input type="date" name="payment_date" required value="{{ now()->toDateString() }}" class="px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Amount</label>
            <input type="number" step="0.01" min="0.01" name="amount" required value="{{ $deal->outstandingBalance() }}" class="px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg w-32 text-gray-200">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Method</label>
            <select name="method" class="px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200">
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank transfer</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Reference</label>
            <input type="text" name="reference" class="px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-600 rounded-lg hover:from-amber-300 hover:to-yellow-500">
            Record {{ $deal->deal_type === 'sell' ? 'receipt' : 'payment' }}
        </button>
    </form>
    @endif
</div>
@endif

@endsection
