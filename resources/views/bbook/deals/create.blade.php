@extends('layouts.bbook')
@section('title', 'New OTC Deal — ' . $tenant->name)
@section('page-title', 'New OTC Deal')
@section('page-subtitle', 'Generic dealing — no VAT, no formal tax invoice')

@section('content')

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-950/40 border border-red-900/50 rounded-lg text-sm text-red-300">
    <ul class="list-disc pl-4">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<datalist id="otc-items-list">
    @foreach($items as $item)
    <option value="{{ $item['name'] }}">{{ $item['metal_type'] }} · {{ $item['stock_grams'] }}g in stock</option>
    @endforeach
</datalist>

<form method="POST" action="{{ route('bbook.deals.store', $tenant->slug) }}" x-data="otcDealForm()" class="max-w-4xl">
    @csrf

    <div class="card p-5 mb-5">
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Deal type <span class="text-red-400">*</span></label>
                <select name="deal_type" x-model="dealType" @change="onDealTypeChange" required class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
                    <option value="buy">Buy — we receive metal</option>
                    <option value="sell">Sell — we issue metal</option>
                    <option value="exchange">Exchange — metal swap</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Deal date <span class="text-red-400">*</span></label>
                <input type="date" name="deal_date" required value="{{ old('deal_date', now()->toDateString()) }}"
                       class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Link to existing client (optional)</label>
                <select @change="pickClient($event.target.value)" class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
                    <option value="">— Not linked —</option>
                    @foreach($clients as $c)
                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Counterparty name <span class="text-red-400">*</span></label>
                <input type="text" name="counterparty_name" x-model="counterpartyName" required
                       class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
                <input type="hidden" name="bullion_client_id" x-model="clientId">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Party reference <span class="text-gray-600">(optional)</span></label>
                <input type="text" name="party_reference" placeholder="Their invoice / order ref"
                       class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4" x-show="dealType==='sell'">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Pricing</label>
                <select name="pricing_type" x-model="pricingType" class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
                    <option value="fixed">Fixed now</option>
                    <option value="unfixed">Unfixed — fix price later</option>
                </select>
                <p x-show="pricingType==='unfixed'" class="text-xs text-amber-600/70 mt-1">Metal ships now at today's stock cost; the sale price and revenue post once you fix the price.</p>
            </div>
        </div>
        <div class="mb-2">
            <label class="block text-xs font-medium text-gray-400 mb-1">Notes</label>
            <input type="text" name="notes"
                   class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
        </div>
    </div>

    <div class="card p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-300 mb-3">Lines</h3>

        <template x-for="(line, i) in lines" :key="i">
            <div class="border border-white/10 rounded-xl p-3 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-500">Line <span x-text="i + 1"></span></span>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">Total: <span class="font-mono font-semibold text-amber-400" x-text="fmt(lineTotal(line))"></span></span>
                        <button type="button" @click="removeLine(i)" x-show="lines.length > 1" class="text-red-400 hover:text-red-300 text-xs">Remove</button>
                    </div>
                </div>

                <div x-show="dealType==='exchange'" class="mb-2">
                    <label class="block text-xs text-gray-500 mb-0.5">Line type <span class="text-red-400">*</span></label>
                    <select x-model="line.line_type" :name="'lines['+i+'][line_type]'" class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200">
                        <option value="metal_in">Metal in — we receive</option>
                        <option value="metal_out">Metal out — we give</option>
                        <option value="cash_topup">Cash top-up</option>
                    </select>
                </div>
                <template x-if="dealType!=='exchange'">
                    <input type="hidden" :name="'lines['+i+'][line_type]'" :value="dealType==='buy' ? 'metal_in' : 'metal_out'">
                </template>

                <template x-if="line.line_type !== 'cash_topup'">
                <div>
                    <div class="mb-2">
                        <label class="block text-xs text-gray-500 mb-0.5">Item / description</label>
                        <input type="text" :name="'lines['+i+'][description]'" x-model="line.description" list="otc-items-list"
                               autocomplete="off" required @input="matchItemByName(line)" placeholder="Pick an existing item or type a description"
                               class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200">
                        <p x-show="line.stockLabel" class="text-xs text-gray-500 mt-0.5" x-text="line.stockLabel"></p>
                        <input type="hidden" :name="'lines['+i+'][inventory_item_id]'" :value="line.inventory_item_id">
                    </div>

                    <div class="grid grid-cols-4 gap-2 mb-2" x-show="!line.inventory_item_id">
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Metal type <span class="text-red-400" x-show="line.line_type==='metal_in'">*</span></label>
                            <select x-model="line.metal_type" @change="ensureMetalRate(line.metal_type)" :name="'lines['+i+'][metal_type]'" class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200">
                                <option value="">— select —</option>
                                <option value="gold">Gold</option>
                                <option value="silver">Silver</option>
                                <option value="platinum">Platinum</option>
                                <option value="palladium">Palladium</option>
                            </select>
                        </div>
                        <p class="col-span-3 text-xs text-amber-600/70 self-end pb-2" x-show="line.line_type==='metal_out'">
                            Metal-out lines must reference an existing inventory item — pick one from the list above.
                        </p>
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Gross (g) <span class="text-red-400">*</span></label>
                            <input type="number" step="0.001" min="0" :name="'lines['+i+'][gross_weight_grams]'" x-model.number="line.gross_weight_grams" @input="syncFromGross(line)"
                                   class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Purity <span class="text-red-400">*</span></label>
                            <input type="number" step="0.001" min="0" max="1000" :name="'lines['+i+'][purity]'" x-model.number="line.purity" @input="syncFromGross(line)"
                                   class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Pure (g)</label>
                            <input type="number" step="0.001" min="0" :name="'lines['+i+'][quantity_grams]'" x-model.number="line.quantity_grams" @input="syncFromPure(line)"
                                   class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">Pcs <span class="text-gray-600">(optional)</span></label>
                            <input type="number" step="1" min="0" :name="'lines['+i+'][pcs]'" x-model.number="line.pcs"
                                   class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                        </div>
                    </div>

                    {{-- Inline metal rate — appears as soon as a metal type is set, same as A-Book --}}
                    <div x-show="line.metal_type" class="grid grid-cols-3 gap-2 mt-2 p-2 bg-amber-950/20 border border-amber-900/30 rounded-lg">
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">
                                <span x-text="(line.metal_type||'').charAt(0).toUpperCase()+(line.metal_type||'').slice(1)"></span>
                                price / oz (USD) <span class="text-red-400">*</span>
                            </label>
                            <input type="number" step="0.0001" min="0.0001"
                                   :name="'metal_rates['+line.metal_type+'][usd_per_oz]'"
                                   :value="metalRates[line.metal_type] ? metalRates[line.metal_type].usdPerOz : ''"
                                   @change="ensureMetalRate(line.metal_type); metalRates[line.metal_type].usdPerOz = parseFloat($event.target.value)"
                                   required
                                   class="w-full px-2 py-1.5 text-sm bg-black/40 border border-amber-900/40 rounded-lg text-gray-200">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">USD → AED rate <span class="text-red-400">*</span></label>
                            <input type="number" step="0.0001" min="0.0001"
                                   :name="'metal_rates['+line.metal_type+'][usd_aed_rate]'"
                                   :value="metalRates[line.metal_type] ? metalRates[line.metal_type].usdAedRate : ''"
                                   @change="ensureMetalRate(line.metal_type); metalRates[line.metal_type].usdAedRate = parseFloat($event.target.value)"
                                   required
                                   class="w-full px-2 py-1.5 text-sm bg-black/40 border border-amber-900/40 rounded-lg text-gray-200">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">AED / gram (auto)</label>
                            <input type="number" step="0.000001" :value="rateFor(line.metal_type).toFixed(6)" readonly
                                   class="w-full px-2 py-1.5 text-sm bg-black/20 border border-white/5 rounded-lg text-gray-500">
                        </div>
                    </div>
                    <p x-show="!line.metal_type" class="text-xs text-amber-600/70 mt-2">Pick an item or metal type above to set the rate.</p>
                    <input type="hidden" :name="'lines['+i+'][unit_price]'" :value="rateFor(line.metal_type).toFixed(4)">

                    <div x-show="line.line_type==='metal_out'" class="mt-2">
                        <label class="block text-xs text-gray-500 mb-0.5">Making charge (AED/g) <span class="text-gray-600">(optional)</span></label>
                        <input type="number" step="0.0001" min="0" :name="'lines['+i+'][making_charge_rate]'" x-model.number="line.making_charge_rate"
                               class="w-40 px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                    </div>
                </div>
                </template>

                <template x-if="line.line_type === 'cash_topup'">
                    <div>
                        <label class="block text-xs text-gray-500 mb-0.5">Description</label>
                        <input type="text" :name="'lines['+i+'][description]'" x-model="line.description" required placeholder="e.g. Cash balancing top-up"
                               class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 mb-2">
                        <label class="block text-xs text-gray-500 mb-0.5">Amount (AED) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" min="0" :name="'lines['+i+'][unit_price]'" x-model.number="line.unit_price"
                               class="w-40 px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                        <input type="hidden" :name="'lines['+i+'][quantity_grams]'" value="1">
                    </div>
                </template>
            </div>
        </template>

        <button type="button" @click="addLine" class="flex items-center gap-2 text-sm text-amber-500 hover:text-amber-400 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add line
        </button>

        <div class="mt-4 pt-4 border-t border-white/10 flex justify-end">
            <div class="w-64 space-y-1.5">
                <div class="flex justify-between text-sm" x-show="dealType==='sell'">
                    <span class="text-gray-400">Premium <span class="text-gray-600">(AED)</span></span>
                    <input type="number" step="0.01" name="premium_amount" x-model.number="premiumAmount" class="w-28 px-2 py-1 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                </div>
                <div class="flex justify-between text-sm" x-show="dealType==='sell'">
                    <span class="text-gray-400">Discount <span class="text-gray-600">(AED)</span></span>
                    <input type="number" step="0.01" name="discount_amount" x-model.number="discountAmount" class="w-28 px-2 py-1 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                </div>
                <div class="flex justify-between text-sm pt-1.5 border-t border-white/10">
                    <span class="text-gray-400">Total</span>
                    <span class="font-mono font-semibold text-amber-400" x-text="fmt(total)"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-600 rounded-lg hover:from-amber-300 hover:to-yellow-500 transition">
            Save draft
        </button>
        <a href="{{ route('bbook.deals.index', $tenant->slug) }}" class="px-5 py-2.5 text-sm font-medium text-gray-400 border border-white/10 rounded-lg hover:bg-white/5">
            Cancel
        </a>
    </div>
</form>

<script>
function otcDealForm() {
    return {
        dealType: 'buy',
        pricingType: 'fixed',
        counterpartyName: '',
        clientId: '',
        premiumAmount: 0,
        discountAmount: 0,
        itemsByName: {},
        lines: [],
        metalRates: {},
        init() {
            @json($items).forEach(it => this.itemsByName[it.name] = it);
            this.lines = [this.blankLine()];
        },
        blankLine() {
            return { line_type: this.dealType === 'buy' ? 'metal_in' : 'metal_out', description:'', inventory_item_id:'', metal_type:'', purity:null, gross_weight_grams:null, quantity_grams:null, pcs:null, unit_price:null, making_charge_rate:null, stockLabel:'' };
        },
        onDealTypeChange() {
            if (this.dealType !== 'exchange') {
                this.lines.forEach(l => l.line_type = this.dealType === 'buy' ? 'metal_in' : 'metal_out');
            }
        },
        addLine() { this.lines.push(this.blankLine()); },
        removeLine(i) { this.lines.splice(i, 1); },
        pickClient(id) {
            if (!id) { this.clientId = ''; return; }
            this.clientId = id;
            const opt = event.target.selectedOptions[0];
            if (opt) this.counterpartyName = opt.textContent.trim();
        },
        matchItemByName(line) {
            const match = this.itemsByName[line.description];
            if (match) {
                line.inventory_item_id = match.id;
                line.metal_type = match.metal_type;
                line.purity = match.purity;
                line.stockLabel = match.stock_grams > 0 ? match.stock_grams.toFixed(3) + 'g in stock' : 'Out of stock';
                this.ensureMetalRate(match.metal_type);
                this.syncFromGross(line);
            } else {
                line.inventory_item_id = '';
                line.stockLabel = '';
            }
        },
        // (USD/oz ÷ troy-oz-in-grams) × USD→AED rate = AED per gram, for one metal's rate box.
        ensureMetalRate(metal) {
            if (metal && !this.metalRates[metal]) {
                this.metalRates[metal] = { usdPerOz: null, usdAedRate: 3.674 };
            }
        },
        rateFor(metal) {
            const r = this.metalRates[metal];
            if (!r) return 0;
            const oz = parseFloat(r.usdPerOz) || 0;
            const rate = parseFloat(r.usdAedRate) || 0;
            return oz > 0 ? (oz / 31.1035) * rate : 0;
        },
        syncFromGross(line) {
            const g = parseFloat(line.gross_weight_grams), p = parseFloat(line.purity);
            if (g > 0 && p > 0) line.quantity_grams = Math.round(g * (p/1000) * 1000) / 1000;
        },
        syncFromPure(line) {
            const g = parseFloat(line.gross_weight_grams), q = parseFloat(line.quantity_grams);
            if (g > 0 && q > 0) line.purity = Math.round(q / g * 1000 * 1000) / 1000;
        },
        lineTotal(line) {
            if (line.line_type === 'cash_topup') return parseFloat(line.unit_price) || 0;
            const rate = this.rateFor(line.metal_type);
            const qty = parseFloat(line.quantity_grams) || 0;
            const making = (parseFloat(line.making_charge_rate) || 0) * qty;
            return qty * rate + making;
        },
        get total() {
            const linesTotal = this.lines.reduce((s, l) => s + this.lineTotal(l), 0);
            if (this.dealType !== 'sell') return linesTotal;
            return linesTotal + (parseFloat(this.premiumAmount) || 0) - (parseFloat(this.discountAmount) || 0);
        },
        fmt(n) { return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    };
}
</script>

@endsection
