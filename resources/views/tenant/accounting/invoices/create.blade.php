@extends('layouts.tenant')
@section('title', 'New Invoice — ' . $tenant->name)
@section('page-title', 'New Invoice')
@section('page-subtitle', 'Totals are recalculated on the server when saved')

@section('content')

@php
    // Computed outside @json() below — Blade's directive-argument parser mis-splits this
    // expression into invalid PHP once the closure crosses a certain length/complexity
    // (reproducible: works with 3 array keys, breaks at 4+ combined with a ternary).
    $itemsForJs = $items->map(fn ($item) => [
        'id' => $item->id,
        'name' => $item->name,
        'purity' => $item->purity,
        'metal_type' => $item->metal_type,
        'nominal_weight_grams' => $item->nominal_weight_grams ? (float) $item->nominal_weight_grams : null,
    ]);
@endphp

@if($errors->any())
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">
    <ul class="list-disc pl-4">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('tenant.accounting.invoices.store', $tenant->slug) }}"
      x-data="invoiceForm()" class="max-w-6xl">
    @csrf

    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Client <span class="text-red-500">*</span></label>
                <select name="bullion_client_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                    <option value="">Select client...</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ $selectedClientId === $client->id ? 'selected' : '' }}>{{ $client->displayName() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Invoice type <span class="text-red-500">*</span></label>
                <select name="invoice_type" x-model="invoiceType" @change="onInvoiceTypeChange()" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                    <option value="purchase" {{ $invoiceType === 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="sale" {{ $invoiceType === 'sale' ? 'selected' : '' }}>Sale</option>
                    <option value="exchange" {{ $invoiceType === 'exchange' ? 'selected' : '' }}>Exchange</option>
                </select>
            </div>
            <div x-show="invoiceType === 'sale'">
                <label class="block text-xs font-medium text-gray-600 mb-1">Pricing</label>
                <select name="pricing_type" x-model="pricingType" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                    <option value="fixed">Fixed — price locked now</option>
                    <option value="unfixed">Unfixed — metal moves now, price fixed later</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Invoice date <span class="text-red-500">*</span></label>
                <input type="date" name="invoice_date" required value="{{ old('invoice_date', now()->toDateString()) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Currency</label>
                <select name="currency_code" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                    <option value="AED">AED</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Exchange rate (to AED)</label>
                <input type="number" step="0.000001" name="exchange_rate" value="{{ old('exchange_rate', 1) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Party reference</label>
                <input type="text" name="party_reference" value="{{ old('party_reference') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
        </div>
        <div x-show="usedMetals.length > 0" class="space-y-3">
            <template x-for="metal in usedMetals" :key="metal">
                <div class="p-3 border border-gray-100 rounded-lg">
                    <div class="text-xs font-semibold text-gray-500 capitalize mb-2" x-text="metal + ' rate'"></div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Price / oz (USD)</label>
                            <input type="number" step="0.0001" min="0" :name="'metal_rates['+metal+'][usd_per_oz]'" x-model.number="metalRates[metal].usdPerOz"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">USD → AED rate</label>
                            <input type="number" step="0.0001" min="0" :name="'metal_rates['+metal+'][usd_aed_rate]'" x-model.number="metalRates[metal].usdAedRate"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Rate / gram (AED)</label>
                            <input type="number" step="0.000001" min="0" :value="rateFor(metal).toFixed(6)" readonly
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                        </div>
                    </div>
                </div>
            </template>
            <p class="text-xs text-gray-400">= (USD/oz ÷ 31.1035) × rate — applied per metal to the matching lines below</p>
        </div>
        <p x-show="usedMetals.length === 0" class="text-xs text-gray-400">Pick an inventory item on a metal line below to configure its rate.</p>
        <div class="grid grid-cols-4 gap-4 mt-4" x-show="invoiceType === 'sale'">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Premium (AED)</label>
                <input type="number" step="0.01" min="0" name="premium_amount" value="{{ old('premium_amount', 0) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Discount (AED)</label>
                <input type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', 0) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
        </div>
    </div>

    <div x-show="pricingType === 'unfixed'" class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800 mb-5">
        Unfixed sale: metal quantity/weight is recorded and inventory moves when posted, but price, VAT and the
        financial journal entry are deferred until you use <strong>Fix Price</strong> later. Unit price and making
        rate below are optional for now.
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Lines</h3>

        <template x-for="(line, i) in lines" :key="i">
            <div class="border border-gray-200 rounded-lg p-3 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-400">Line <span x-text="i + 1"></span></span>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">Total: <span class="font-mono font-semibold text-gray-800" x-text="lineTotal(line).toFixed(2)"></span></span>
                        <button type="button" @click="removeLine(i)" x-show="lines.length > 1" class="text-red-400 hover:text-red-600 text-xs">Remove</button>
                    </div>
                </div>

                <div class="grid grid-cols-6 gap-2 mb-2">
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Type</label>
                        <select :name="'lines['+i+'][line_type]'" x-model="line.line_type" class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                            <template x-for="opt in lineTypeOptions" :key="opt.value">
                                <option :value="opt.value" x-text="opt.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-span-5">
                        <label class="block text-xs text-gray-400 mb-0.5">Item / description</label>
                        <input type="text" :name="'lines['+i+'][description]'" x-model="line.description" list="inventory-items-list" required
                               @input="matchItemByName(line)" placeholder="Pick an inventory item or type a description"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                        <input type="hidden" :name="'lines['+i+'][inventory_item_id]'" :value="line.inventory_item_id">
                        <input type="hidden" :name="'lines['+i+'][metal_type]'" :value="line.metal_type">
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2 mb-2" x-show="line.line_type === 'metal_in' && !line.inventory_item_id">
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Metal type <span class="text-red-400">*</span></label>
                        <select x-model="line.metal_type" @change="ensureMetalRate(line.metal_type)" class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                            <option value="">— select metal —</option>
                            <option value="gold">Gold</option>
                            <option value="silver">Silver</option>
                            <option value="platinum">Platinum</option>
                            <option value="palladium">Palladium</option>
                        </select>
                    </div>
                    <p class="col-span-3 text-xs text-amber-600 self-end pb-2">No matching inventory item — select the metal type so this purchase is posted to the correct account.</p>
                </div>

                <div class="grid grid-cols-4 gap-2 mb-2" x-show="['metal_in','metal_out'].includes(line.line_type)">
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Purity</label>
                        <input type="number" step="0.001" min="0" max="999.999" :name="'lines['+i+'][purity]'" x-model.number="line.purity" @input="syncFromGross(line)"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Gross (g)</label>
                        <input type="number" step="0.001" min="0" :name="'lines['+i+'][gross_weight_grams]'" x-model.number="line.gross_weight_grams" @input="syncFromGross(line)"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Pure (g)</label>
                        <input type="number" step="0.001" min="0" :name="'lines['+i+'][quantity_grams]'" x-model.number="line.quantity_grams"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Pcs</label>
                        <input type="number" step="1" min="0" :name="'lines['+i+'][pcs]'" x-model.number="line.pcs" @input="syncFromPcs(line)"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                        <p class="text-xs text-gray-400 mt-0.5" x-show="line.nominal_weight_grams" x-text="line.nominal_weight_grams + 'g each'"></p>
                    </div>
                    <input type="hidden" :name="'lines['+i+'][unit_price]'" :value="rateFor(line.metal_type).toFixed(4)">
                    <p class="col-span-4 text-xs text-gray-400 -mt-1" x-show="line.metal_type">Priced at the <span x-text="line.metal_type"></span> rate above (<span x-text="rateFor(line.metal_type).toFixed(4)"></span> AED/g)</p>
                    <p class="col-span-4 text-xs text-amber-600 -mt-1" x-show="!line.metal_type">Pick an inventory item above to price this line.</p>
                </div>

                <div class="grid grid-cols-4 gap-2 mb-2" x-show="!['metal_in','metal_out'].includes(line.line_type)">
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Amount (AED)</label>
                        <input type="number" step="0.01" min="0" :name="'lines['+i+'][line_subtotal]'" x-model.number="line.line_subtotal"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                </div>

                <div class="grid grid-cols-6 gap-2 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Metal VAT</label>
                        <select :name="'lines['+i+'][metal_vat_treatment]'" x-model="line.metal_vat_treatment" @change="applyDefaultVatRate(line, 'metal_vat_treatment', 'metal_vat_rate')" class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                            <option value="standard">Standard</option>
                            <option value="zero_rated">Zero-rated</option>
                            <option value="reverse_charge">Reverse charge</option>
                            <option value="exempt">Exempt</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Rate %</label>
                        <input type="number" step="0.01" min="0" max="100" :name="'lines['+i+'][metal_vat_rate]'" x-model.number="line.metal_vat_rate"
                               :readonly="line.metal_vat_treatment !== 'standard'"
                               :class="line.metal_vat_treatment !== 'standard' ? 'bg-gray-50 text-gray-400' : ''"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Making rate/g</label>
                        <input type="number" step="0.0001" min="0" :name="'lines['+i+'][making_charge_rate]'" x-model.number="line.making_charge_rate"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Making VAT</label>
                        <select :name="'lines['+i+'][making_vat_treatment]'" x-model="line.making_vat_treatment" @change="applyDefaultVatRate(line, 'making_vat_treatment', 'making_vat_rate')" class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                            <option value="standard">Standard</option>
                            <option value="zero_rated">Zero-rated</option>
                            <option value="reverse_charge">Reverse charge</option>
                            <option value="exempt">Exempt</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Rate %</label>
                        <input type="number" step="0.01" min="0" max="100" :name="'lines['+i+'][making_vat_rate]'" x-model.number="line.making_vat_rate"
                               :readonly="line.making_vat_treatment !== 'standard'"
                               :class="line.making_vat_treatment !== 'standard' ? 'bg-gray-50 text-gray-400' : ''"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                </div>
            </div>
        </template>

        <button type="button" @click="addLine()" class="text-xs text-blue-600 hover:underline">+ Add line</button>

        <div class="flex justify-end mt-4 pt-4 border-t border-gray-100">
            <div class="w-56 text-sm space-y-1">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-mono" x-text="subtotal.toFixed(2)"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">VAT</span><span class="font-mono" x-text="vatTotal.toFixed(2)"></span></div>
                <div class="flex justify-between font-semibold text-gray-800"><span>Total</span><span class="font-mono" x-text="total.toFixed(2)"></span></div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">{{ old('notes') }}</textarea>
    </div>

    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        Save draft
    </button>
</form>

<datalist id="inventory-items-list">
    @foreach($items as $item)
    <option value="{{ $item->name }}"></option>
    @endforeach
</datalist>

<script>
function invoiceForm() {
    return {
        invoiceType: '{{ $invoiceType }}',
        pricingType: 'fixed',
        metalRates: {}, // metal => { usdPerOz, usdAedRate } — populated as metal lines get linked to items
        lines: [],
        itemsByName: {},
        init() {
            this.lines = [this.blankLine()];
            @json($itemsForJs)
                .forEach(it => this.itemsByName[it.name] = it);
        },
        ensureMetalRate(metal) {
            if (metal && !this.metalRates[metal]) {
                this.metalRates[metal] = { usdPerOz: null, usdAedRate: 3.674 };
            }
        },
        // (USD/oz ÷ troy-oz-in-grams) × USD→AED rate = AED per gram, for one metal's rate box.
        rateFor(metal) {
            const r = this.metalRates[metal];
            if (!r) return 0;
            const oz = parseFloat(r.usdPerOz) || 0;
            const rate = parseFloat(r.usdAedRate) || 0;
            return oz > 0 ? (oz / 31.1035) * rate : 0;
        },
        // Distinct metals among the invoice's metal lines — drives which rate boxes show.
        get usedMetals() {
            const set = new Set();
            this.lines.forEach(l => { if (this.isMetalLine(l) && l.metal_type) set.add(l.metal_type); });
            return Array.from(set);
        },
        // Typing a name that matches a catalog item links it (for stock/purity/metal); anything
        // else is treated as a free-text description with no inventory linkage.
        matchItemByName(line) {
            const match = this.itemsByName[line.description];
            if (match) {
                line.inventory_item_id = match.id;
                line.purity = match.purity;
                line.metal_type = match.metal_type;
                line.nominal_weight_grams = match.nominal_weight_grams;
                this.ensureMetalRate(match.metal_type);
                if (line.nominal_weight_grams && line.pcs) {
                    this.syncFromPcs(line);
                } else {
                    this.syncFromGross(line);
                }
            } else {
                line.inventory_item_id = '';
                line.metal_type = '';
                line.nominal_weight_grams = null;
            }
        },
        lineTypeOptionsFor(type) {
            return {
                purchase: [{ value: 'metal_in', label: 'Metal in' }, { value: 'other', label: 'Other' }],
                sale: [{ value: 'metal_out', label: 'Metal out' }, { value: 'other', label: 'Other' }],
                exchange: [
                    { value: 'metal_in', label: 'Metal in' },
                    { value: 'metal_out', label: 'Metal out' },
                    { value: 'cash_topup', label: 'Cash top-up' },
                    { value: 'other', label: 'Other' },
                ],
            }[type] ?? [];
        },
        get lineTypeOptions() { return this.lineTypeOptionsFor(this.invoiceType); },
        blankLine() {
            return {
                line_type: this.lineTypeOptions[0].value, inventory_item_id: '', description: '',
                metal_type: '', nominal_weight_grams: null, purity: null, gross_weight_grams: null, quantity_grams: null, pcs: null,
                line_subtotal: null,
                metal_vat_treatment: 'standard', metal_vat_rate: 5,
                making_charge_rate: null,
                making_vat_treatment: 'standard', making_vat_rate: 5,
            };
        },
        onInvoiceTypeChange() {
            // Line types are invoice-type-specific (e.g. a sale invoice can't carry a
            // metal_in line) — reset to a single fresh line rather than leaving stale,
            // now-invalid line types selected. Unfixed pricing only makes sense for sales.
            this.lines = [this.blankLine()];
            if (this.invoiceType !== 'sale') {
                this.pricingType = 'fixed';
            }
        },
        addLine() {
            this.lines.push(this.blankLine());
        },
        removeLine(i) { this.lines.splice(i, 1); },
        applyDefaultVatRate(line, treatmentKey, rateKey) {
            line[rateKey] = line[treatmentKey] === 'standard' ? 5 : 0;
        },
        // Pure weight = gross weight × purity fraction (purity stored as e.g. 999.900 per mille).
        syncFromGross(line) {
            const gross = parseFloat(line.gross_weight_grams);
            const purity = parseFloat(line.purity);
            if (gross > 0 && purity > 0) {
                line.quantity_grams = Math.round(gross * (purity / 1000) * 1000) / 1000;
            }
        },
        // For a denominated item (has a nominal weight, e.g. a "10 Gram" bar), piece count
        // drives the gross weight — this is how many discrete bars/coins physically move —
        // then the usual gross×purity calc derives the pure weight on top.
        syncFromPcs(line) {
            if (line.nominal_weight_grams && line.pcs) {
                line.gross_weight_grams = Math.round(line.pcs * line.nominal_weight_grams * 1000) / 1000;
                this.syncFromGross(line);
            }
        },
        isMetalLine(line) { return ['metal_in', 'metal_out'].includes(line.line_type); },
        lineSubtotal(line) {
            if (this.isMetalLine(line)) {
                const qty = parseFloat(line.quantity_grams) || 0;
                return qty * this.rateFor(line.metal_type);
            }
            return parseFloat(line.line_subtotal) || 0;
        },
        lineMetalVat(line) {
            const rate = parseFloat(line.metal_vat_rate) || 0;
            return this.lineSubtotal(line) * rate / 100;
        },
        lineMakingAmount(line) {
            const qty = parseFloat(line.quantity_grams) || 0;
            const rate = parseFloat(line.making_charge_rate) || 0;
            return qty * rate;
        },
        lineMakingVat(line) {
            const rate = parseFloat(line.making_vat_rate) || 0;
            return this.lineMakingAmount(line) * rate / 100;
        },
        lineTotal(line) {
            return this.lineSubtotal(line) + this.lineMetalVat(line) + this.lineMakingAmount(line) + this.lineMakingVat(line);
        },
        get subtotal() { return this.lines.reduce((sum, l) => sum + this.lineSubtotal(l) + this.lineMakingAmount(l), 0); },
        get vatTotal() { return this.lines.reduce((sum, l) => sum + this.lineMetalVat(l) + this.lineMakingVat(l), 0); },
        get total() { return this.subtotal + this.vatTotal; },
    };
}
</script>

@endsection
