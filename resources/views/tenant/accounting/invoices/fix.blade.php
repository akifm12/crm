@extends('layouts.tenant')
@section('title', 'Fix Price — ' . $invoice->invoice_number)
@section('page-title', 'Fix Price — ' . $invoice->invoice_number)
@section('page-subtitle', 'Metal already moved when this invoice was posted — set the final price to post the financial entry')

@section('content')

@if($errors->any())
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">
    <ul class="list-disc pl-4">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('tenant.accounting.invoices.fix', [$tenant->slug, $invoice->id]) }}"
      x-data="fixPriceForm()" class="max-w-5xl">
    @csrf

    @php
        $usedMetals = $invoice->lines->where('line_type', 'metal_out')->pluck('inventoryItem.metal_type')->filter()->unique()->values();
    @endphp

    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        @foreach($usedMetals as $metal)
        <div class="grid grid-cols-3 gap-4 p-3 border border-gray-100 rounded-lg {{ ! $loop->last ? 'mb-3' : '' }}">
            <div class="col-span-3 text-xs font-semibold text-gray-500 capitalize -mb-1">{{ $metal }} rate</div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Price / oz (USD)</label>
                <input type="number" step="0.0001" min="0" name="metal_rates[{{ $metal }}][usd_per_oz]" x-model.number="metalRates['{{ $metal }}'].usdPerOz"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">USD → AED rate</label>
                <input type="number" step="0.0001" min="0" name="metal_rates[{{ $metal }}][usd_aed_rate]" x-model.number="metalRates['{{ $metal }}'].usdAedRate"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Rate / gram (AED)</label>
                <input type="number" step="0.000001" min="0" :value="rateFor('{{ $metal }}').toFixed(6)" readonly
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
            </div>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Lines</h3>

        @foreach($invoice->lines as $line)
        @if($line->line_type === 'metal_out')
        <div class="border border-gray-200 rounded-lg p-3 mb-3"
             x-data="lineRow({{ $line->quantity_grams ?? 0 }}, {{ $line->making_charge_rate ?? 'null' }}, '{{ $line->metal_vat_treatment }}', {{ $line->metal_vat_rate }}, '{{ $line->making_vat_treatment }}', {{ $line->making_vat_rate }}, '{{ $line->inventoryItem->metal_type ?? 'gold' }}')">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <span class="text-sm text-gray-700">{{ $line->description }}</span>
                    <span class="block text-xs text-gray-400">{{ $line->inventoryItem->name ?? '' }} — {{ number_format($line->quantity_grams, 3) }}g pure</span>
                </div>
                <span class="text-xs text-gray-500">Total: <span class="font-mono font-semibold text-gray-800" x-text="total.toFixed(2)"></span></span>
            </div>
            <p class="text-xs text-gray-400 mb-2">Priced at the <span x-text="metal"></span> rate above (<span x-text="rateFor(metal).toFixed(4)"></span> AED/g)</p>
            <input type="hidden" name="lines[{{ $line->id }}][unit_price]" :value="rateFor(metal).toFixed(4)">

            <div class="grid grid-cols-4 gap-2">
                <div>
                    <label class="block text-xs text-gray-400 mb-0.5">Metal VAT</label>
                    <select name="lines[{{ $line->id }}][metal_vat_treatment]" x-model="metalVatTreatment" @change="applyDefault('metal')" class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                        <option value="standard">Standard</option>
                        <option value="zero_rated">Zero-rated</option>
                        <option value="reverse_charge">Reverse charge</option>
                        <option value="exempt">Exempt</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-0.5">Rate %</label>
                    <input type="number" step="0.01" min="0" max="100" name="lines[{{ $line->id }}][metal_vat_rate]" x-model.number="metalVatRate"
                           :readonly="metalVatTreatment !== 'standard'" :class="metalVatTreatment !== 'standard' ? 'bg-gray-50 text-gray-400' : ''"
                           class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-0.5">Making rate/g</label>
                    <input type="number" step="0.0001" min="0" name="lines[{{ $line->id }}][making_charge_rate]" x-model.number="makingRate"
                           class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-0.5">Making VAT</label>
                    <select name="lines[{{ $line->id }}][making_vat_treatment]" x-model="makingVatTreatment" @change="applyDefault('making')" class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                        <option value="standard">Standard</option>
                        <option value="zero_rated">Zero-rated</option>
                        <option value="reverse_charge">Reverse charge</option>
                        <option value="exempt">Exempt</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-2 mt-2">
                <div class="col-start-4">
                    <label class="block text-xs text-gray-400 mb-0.5">Making rate %</label>
                    <input type="number" step="0.01" min="0" max="100" name="lines[{{ $line->id }}][making_vat_rate]" x-model.number="makingVatRate"
                           :readonly="makingVatTreatment !== 'standard'" :class="makingVatTreatment !== 'standard' ? 'bg-gray-50 text-gray-400' : ''"
                           class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                </div>
            </div>
        </div>
        @else
        <div class="border border-gray-100 bg-gray-50 rounded-lg p-3 mb-3 flex items-center justify-between">
            <span class="text-sm text-gray-600">{{ $line->description }}</span>
            <span class="text-xs text-gray-500 font-mono">{{ number_format($line->line_total, 2) }} (already priced)</span>
        </div>
        @endif
        @endforeach
    </div>

    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        Fix price &amp; post
    </button>
</form>

<script>
function lineRow(qty, makingRate, metalVatTreatment, metalVatRate, makingVatTreatment, makingVatRate, metal) {
    return {
        qty, makingRate, metalVatTreatment, metalVatRate, makingVatTreatment, makingVatRate, metal,
        applyDefault(which) {
            if (which === 'metal') this.metalVatRate = this.metalVatTreatment === 'standard' ? 5 : 0;
            if (which === 'making') this.makingVatRate = this.makingVatTreatment === 'standard' ? 5 : 0;
        },
        // rateFor() is read from the parent fixPriceForm() scope (Alpine merges nested
        // x-data contexts), so this stays in sync as the header rate boxes change.
        get subtotal() { return this.qty * (this.rateFor(this.metal) || 0); },
        get metalVat() { return this.subtotal * (parseFloat(this.metalVatRate) || 0) / 100; },
        get makingAmount() { return this.qty * (parseFloat(this.makingRate) || 0); },
        get makingVat() { return this.makingAmount * (parseFloat(this.makingVatRate) || 0) / 100; },
        get total() { return this.subtotal + this.metalVat + this.makingAmount + this.makingVat; },
    };
}
function fixPriceForm() {
    return {
        metalRates: @json($usedMetals->mapWithKeys(fn ($m) => [$m => [
            'usdPerOz' => $m === 'gold' ? $invoice->gold_rate_per_oz : null,
            'usdAedRate' => $invoice->usd_aed_rate ?? 3.674,
        ]])),
        rateFor(metal) {
            const r = this.metalRates[metal];
            if (!r) return 0;
            const oz = parseFloat(r.usdPerOz) || 0;
            const rate = parseFloat(r.usdAedRate) || 0;
            return oz > 0 ? (oz / 31.1035) * rate : 0;
        },
    };
}
</script>

@endsection
