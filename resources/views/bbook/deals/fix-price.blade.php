@extends('layouts.bbook')
@section('title', 'Fix Price — ' . $deal->deal_number . ' — ' . $tenant->name)
@section('page-title', 'Fix Price — ' . $deal->deal_number)
@section('page-subtitle', 'Metal already shipped at stock cost — set the sale price now to post the invoice')

@section('content')

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-950/40 border border-red-900/50 rounded-lg text-sm text-red-300">
    <ul class="list-disc pl-4">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('bbook.deals.fix-price.store', [$tenant->slug, $deal->id]) }}" x-data="fixPriceForm()" class="max-w-3xl">
    @csrf

    <div class="card p-5 mb-5">
        <div class="grid grid-cols-3 gap-4 text-sm mb-4">
            <div><p class="text-xs text-gray-500 mb-0.5">Counterparty</p><p class="text-gray-300">{{ $deal->counterparty_name }}</p></div>
            <div><p class="text-xs text-gray-500 mb-0.5">Deal date</p><p class="text-gray-300">{{ $deal->deal_date->format('d M Y') }}</p></div>
            <div><p class="text-xs text-gray-500 mb-0.5">Deposits collected</p><p class="text-gray-300">{{ number_format($deal->payments->sum('amount'), 2) }}</p></div>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-white/10">
                    <th class="py-2">Description</th>
                    <th class="py-2 text-right">Pure (g)</th>
                    <th class="py-2 text-right">Rate (AED/g)</th>
                    <th class="py-2 text-right">Making (AED/g)</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($deal->lines as $line)
                <tr>
                    <td class="py-2 text-gray-300">{{ $line->description }}</td>
                    <td class="py-2 text-right font-mono text-gray-400">{{ number_format($line->quantity_grams, 3) }}</td>
                    <td class="py-2 text-right">
                        <input type="number" step="0.0001" min="0" x-model.number="lines[{{ $line->id }}].unit_price"
                               name="lines[{{ $line->id }}][unit_price]" required
                               class="w-28 px-2 py-1 text-sm bg-black/40 border border-amber-900/40 rounded-lg text-gray-200 text-right">
                    </td>
                    <td class="py-2 text-right">
                        <input type="number" step="0.0001" min="0" x-model.number="lines[{{ $line->id }}].making_charge_rate"
                               name="lines[{{ $line->id }}][making_charge_rate]"
                               class="w-24 px-2 py-1 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                    </td>
                    <td class="py-2 text-right font-mono text-amber-400" x-text="fmt(lineTotal({{ $line->id }}, {{ $line->quantity_grams }}))"></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 pt-4 border-t border-white/10 flex justify-end text-sm">
            <div class="w-48 flex justify-between">
                <span class="text-gray-400">Total</span>
                <span class="font-mono font-semibold text-amber-400" x-text="fmt(grandTotal)"></span>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Premium/discount already set on this deal (if any) are added on top automatically.</p>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-600 rounded-lg hover:from-amber-300 hover:to-yellow-500 transition">
            Fix price &amp; post
        </button>
        <a href="{{ route('bbook.deals.show', [$tenant->slug, $deal->id]) }}" class="px-5 py-2.5 text-sm font-medium text-gray-400 border border-white/10 rounded-lg hover:bg-white/5">
            Cancel
        </a>
    </div>
</form>

<script>
function fixPriceForm() {
    return {
        lines: {
            @foreach($deal->lines as $line)
            {{ $line->id }}: { unit_price: {{ $line->unit_price ?? 0 }}, making_charge_rate: {{ $line->making_charge_rate ?? 0 }} },
            @endforeach
        },
        lineTotal(id, qty) {
            const l = this.lines[id];
            const rate = parseFloat(l.unit_price) || 0;
            const making = parseFloat(l.making_charge_rate) || 0;
            return qty * (rate + making);
        },
        get grandTotal() {
            let sum = 0;
            @foreach($deal->lines as $line)
            sum += this.lineTotal({{ $line->id }}, {{ $line->quantity_grams }});
            @endforeach
            return sum + {{ (float) $deal->premium_amount }} - {{ (float) $deal->discount_amount }};
        },
        fmt(n) { return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    };
}
</script>

@endsection
