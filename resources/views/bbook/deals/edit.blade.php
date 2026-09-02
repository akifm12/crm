@extends('layouts.bbook')
@section('title', 'Edit ' . $deal->deal_number . ' — ' . $tenant->name)
@section('page-title', 'Edit ' . $deal->deal_number)
@section('page-subtitle', 'Draft — deal type cannot be changed once created')

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

<form method="POST" action="{{ route('bbook.deals.update', [$tenant->slug, $deal->id]) }}" x-data="otcDealForm()" class="max-w-4xl">
    @csrf
    @method('PUT')

    <div class="card p-5 mb-5">
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Deal type</label>
                <input type="text" value="{{ ucfirst($deal->deal_type) }} — {{ $deal->deal_type === 'buy' ? 'we receive metal' : 'we issue metal' }}" readonly
                       class="w-full px-3 py-2 text-sm bg-black/20 border border-white/5 rounded-lg text-gray-500">
                <input type="hidden" name="deal_type" value="{{ $deal->deal_type }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Deal date <span class="text-red-400">*</span></label>
                <input type="date" name="deal_date" required value="{{ old('deal_date', $deal->deal_date->toDateString()) }}"
                       class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Link to existing client (optional)</label>
                <select @change="pickClient($event.target.value)" class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
                    <option value="">— Not linked —</option>
                    @foreach($clients as $c)
                    <option value="{{ $c['id'] }}" {{ $deal->bullion_client_id === $c['id'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Counterparty name <span class="text-red-400">*</span></label>
                <input type="text" name="counterparty_name" x-model="counterpartyName" required
                       class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
                <input type="hidden" name="bullion_client_id" x-model="clientId">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1">Notes</label>
                <input type="text" name="notes" value="{{ old('notes', $deal->notes) }}"
                       class="w-full px-3 py-2 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 focus:outline-none focus:ring-1 focus:ring-amber-500/50">
            </div>
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
                        <label class="block text-xs text-gray-500 mb-0.5">Metal type <span class="text-red-400" x-show="dealType==='buy'">*</span></label>
                        <select x-model="line.metal_type" :name="'lines['+i+'][metal_type]'" class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200">
                            <option value="">— select —</option>
                            <option value="gold">Gold</option>
                            <option value="silver">Silver</option>
                            <option value="platinum">Platinum</option>
                            <option value="palladium">Palladium</option>
                        </select>
                    </div>
                    <p class="col-span-3 text-xs text-amber-600/70 self-end pb-2" x-show="dealType==='sell'">
                        Sell lines must reference an existing inventory item — pick one from the list above.
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
                <div class="grid grid-cols-4 gap-2 mt-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-0.5">Rate (AED/g) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.0001" min="0" :name="'lines['+i+'][unit_price]'" x-model.number="line.unit_price"
                               required class="w-full px-2 py-1.5 text-sm bg-black/40 border border-white/10 rounded-lg text-gray-200 text-right">
                    </div>
                </div>
            </div>
        </template>

        <button type="button" @click="addLine" class="flex items-center gap-2 text-sm text-amber-500 hover:text-amber-400 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add line
        </button>

        <div class="mt-4 pt-4 border-t border-white/10 flex justify-end">
            <div class="w-48 flex justify-between text-sm">
                <span class="text-gray-400">Total</span>
                <span class="font-mono font-semibold text-amber-400" x-text="fmt(total)"></span>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-600 rounded-lg hover:from-amber-300 hover:to-yellow-500 transition">
            Save changes
        </button>
        <a href="{{ route('bbook.deals.show', [$tenant->slug, $deal->id]) }}" class="px-5 py-2.5 text-sm font-medium text-gray-400 border border-white/10 rounded-lg hover:bg-white/5">
            Cancel
        </a>
    </div>
</form>

<script>
function otcDealForm() {
    return {
        dealType: '{{ $deal->deal_type }}',
        counterpartyName: @json(old('counterparty_name', $deal->counterparty_name)),
        clientId: @json(old('bullion_client_id', $deal->bullion_client_id)),
        itemsByName: {},
        lines: [],
        init() {
            @json($items).forEach(it => this.itemsByName[it.name] = it);
            const existing = @json($deal->lines->map(fn ($l) => [
                'description'        => $l->description,
                'inventory_item_id'  => $l->inventory_item_id,
                'metal_type'         => $l->metal_type,
                'purity'             => $l->purity,
                'gross_weight_grams' => $l->gross_weight_grams,
                'quantity_grams'     => $l->quantity_grams,
                'pcs'                => $l->pcs,
                'unit_price'         => $l->unit_price,
                'stockLabel'         => $l->inventoryItem && $l->inventoryItem->balance
                    ? ((float) $l->inventoryItem->balance->quantity_grams) . 'g in stock'
                    : '',
            ]));
            this.lines = existing.length ? existing : [this.blankLine()];
        },
        blankLine() {
            return { description:'', inventory_item_id:'', metal_type:'', purity:null, gross_weight_grams:null, quantity_grams:null, pcs:null, unit_price:null, stockLabel:'' };
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
                this.syncFromGross(line);
            } else {
                line.inventory_item_id = '';
                line.stockLabel = '';
            }
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
            return (parseFloat(line.quantity_grams) || 0) * (parseFloat(line.unit_price) || 0);
        },
        get total() { return this.lines.reduce((s, l) => s + this.lineTotal(l), 0); },
        fmt(n) { return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    };
}
</script>

@endsection
