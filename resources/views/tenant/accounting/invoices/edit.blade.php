@extends('layouts.tenant')
@section('title', 'Edit ' . $invoice->invoice_number . ' — ' . $tenant->name)
@section('page-title', 'Edit ' . $invoice->invoice_number)
@section('page-subtitle', ucfirst($invoice->invoice_type) . ' invoice — ' . $invoice->client->displayName())

@section('content')

@php
    $isPosted = $invoice->status === 'posted';

    $itemsForJs = $items->map(fn ($item) => [
        'id'                   => $item->id,
        'name'                 => $item->name,
        'purity'               => $item->purity,
        'metal_type'           => $item->metal_type,
        'nominal_weight_grams' => $item->nominal_weight_grams ? (float) $item->nominal_weight_grams : null,
        'stock_grams'          => $item->balance ? (float) $item->balance->quantity_grams : 0,
        'stock_pcs'            => $item->balance ? (int) $item->balance->pieces : 0,
    ]);

    // Pre-populate existing lines in the shape the JS form expects
    $existingLines = $invoice->lines->map(fn ($l) => [
        'line_type'             => $l->line_type,
        'inventory_item_id'     => $l->inventory_item_id ?? '',
        'description'           => $l->description,
        'metal_type'            => $l->metal_type ?? '',
        'nominal_weight_grams'  => $l->inventoryItem?->nominal_weight_grams ? (float) $l->inventoryItem->nominal_weight_grams : null,
        'purity'                => $l->purity !== null ? (float) $l->purity : null,
        'gross_weight_grams'    => $l->gross_weight_grams !== null ? (float) $l->gross_weight_grams : null,
        'quantity_grams'        => $l->quantity_grams !== null ? (float) $l->quantity_grams : null,
        'pcs'                   => $l->pcs !== null ? (int) $l->pcs : null,
        'line_subtotal'         => $l->line_subtotal !== null ? (float) $l->line_subtotal : null,
        'metal_vat_treatment'   => $l->metal_vat_treatment ?? 'standard',
        'metal_vat_rate'        => (float) ($l->metal_vat_rate ?? 0),
        'making_charge_rate'    => $l->making_charge_rate !== null ? (float) $l->making_charge_rate : null,
        'making_vat_treatment'  => $l->making_vat_treatment ?? 'standard',
        'making_vat_rate'       => (float) ($l->making_vat_rate ?? 0),
        'outOfStock'            => false,
        'stockLabel'            => '',
    ])->values()->all();

    // Pre-populate metal rates from stored invoice data
    $existingMetalRates = [];
    if ($invoice->metal_rates) {
        foreach ($invoice->metal_rates as $metal => $rate) {
            $existingMetalRates[$metal] = [
                'usdPerOz'   => ($rate['usd_per_oz'] ?? null) > 0 ? (float) $rate['usd_per_oz'] : null,
                'usdAedRate' => ($rate['usd_aed_rate'] ?? null) > 0 ? (float) $rate['usd_aed_rate'] : null,
            ];
        }
    }
@endphp

@if($errors->any())
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">
    <ul class="list-disc pl-4">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

@if($isPosted)
<div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800 mb-4 flex items-start gap-2">
    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>This invoice has been posted. The line items and amounts are locked to the posted journal entry. You can update the date, party reference, and notes only. To correct amounts, void this invoice and create a new one.</span>
</div>
@endif

<form method="POST" action="{{ route('tenant.accounting.invoices.update', [$tenant->slug, $invoice->id]) }}"
      @if(!$isPosted) x-data="invoiceForm()" @endif class="max-w-6xl"
      @keydown.enter="if ($event.target.tagName === 'INPUT' && $event.target.type !== 'submit') $event.preventDefault()">
    @csrf
    @method('PUT')

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Client</label>
                <p class="px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-50 text-gray-700">{{ $invoice->client->displayName() }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Invoice type</label>
                <p class="px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-50 text-gray-700">{{ ucfirst($invoice->invoice_type) }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pricing</label>
                <p class="px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-50 text-gray-700">{{ ucfirst($invoice->pricing_type) }}</p>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Invoice date <span class="text-red-500">*</span></label>
                <input type="date" name="invoice_date" required
                       value="{{ old('invoice_date', $invoice->invoice_date->toDateString()) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Currency</label>
                @if($isPosted)
                <p class="px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-50 text-gray-700">{{ $invoice->currency_code }}</p>
                @else
                <select name="currency_code" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                    <option value="AED" {{ $invoice->currency_code === 'AED' ? 'selected' : '' }}>AED</option>
                    <option value="USD" {{ $invoice->currency_code === 'USD' ? 'selected' : '' }}>USD</option>
                </select>
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Exchange rate (to AED)</label>
                @if($isPosted)
                <p class="px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-50 text-gray-700">{{ $invoice->exchange_rate }}</p>
                @else
                <input type="number" step="0.000001" name="exchange_rate"
                       value="{{ old('exchange_rate', $invoice->exchange_rate) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Party reference</label>
                <input type="text" name="party_reference"
                       value="{{ old('party_reference', $invoice->party_reference) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
        </div>

        @if(!$isPosted && $invoice->invoice_type === 'sale')
        <div class="grid grid-cols-4 gap-4 mt-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Premium (USD / oz)</label>
                <input type="number" step="0.01" min="0" x-model.number="premiumUsdPerOz"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                <p class="text-xs text-gray-400 mt-0.5" x-show="premiumAed > 0">≈ AED <span x-text="fmt(premiumAed)"></span></p>
                <input type="hidden" name="premium_amount" :value="premiumAed.toFixed(2)">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Discount (USD / oz)</label>
                <input type="number" step="0.01" min="0" x-model.number="discountUsdPerOz"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                <p class="text-xs text-gray-400 mt-0.5" x-show="discountAed > 0">≈ AED <span x-text="fmt(discountAed)"></span></p>
                <input type="hidden" name="discount_amount" :value="discountAed.toFixed(2)">
            </div>
        </div>
        @endif
    </div>

    {{-- ── Lines ────────────────────────────────────────────────────────── --}}
    @if($isPosted)
    {{-- Read-only line display for posted invoices --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Lines <span class="text-xs font-normal text-gray-400">(locked — invoice is posted)</span></h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 uppercase border-b border-gray-100">
                    <th class="text-left pb-2 font-medium">Type</th>
                    <th class="text-left pb-2 font-medium">Description</th>
                    <th class="text-right pb-2 font-medium">Grams</th>
                    <th class="text-right pb-2 font-medium">Subtotal</th>
                    <th class="text-right pb-2 font-medium">VAT</th>
                    <th class="text-right pb-2 font-medium">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lines as $line)
                <tr class="border-b border-gray-50">
                    <td class="py-2 pr-3 text-xs text-gray-500">{{ str_replace('_', ' ', ucfirst($line->line_type)) }}</td>
                    <td class="py-2 pr-3">{{ $line->description }}</td>
                    <td class="py-2 pr-3 text-right font-mono text-xs">{{ $line->quantity_grams !== null ? number_format($line->quantity_grams, 3) : '—' }}</td>
                    <td class="py-2 pr-3 text-right font-mono">{{ number_format($line->line_subtotal, 2) }}</td>
                    <td class="py-2 pr-3 text-right font-mono">{{ number_format($line->metal_vat_amount + $line->making_vat_amount, 2) }}</td>
                    <td class="py-2 text-right font-mono font-semibold">{{ number_format($line->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold">
                    <td colspan="5" class="pt-3 text-right text-gray-600">Total</td>
                    <td class="pt-3 text-right font-mono">{{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    {{-- Editable lines for drafts --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Lines</h3>

        <template x-for="(line, i) in lines" :key="i">
            <div class="border border-gray-200 rounded-lg p-3 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-400">Line <span x-text="i + 1"></span></span>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">Total: <span class="font-mono font-semibold text-gray-800" x-text="fmt(lineTotal(line))"></span></span>
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
                        <input type="text" :name="'lines['+i+'][description]'" x-model="line.description"
                               :list="['metal_out'].includes(line.line_type) ? 'inventory-items-in-stock' : 'inventory-items-list'"
                               autocomplete="off"
                               required @input="matchItemByName(line)" placeholder="Pick an inventory item or type a description"
                               :class="line.outOfStock ? 'border-red-400' : 'border-gray-200'"
                               class="w-full px-2 py-1.5 text-sm border rounded-lg">
                        <p x-show="line.outOfStock" class="text-xs text-red-500 mt-0.5">This item has no stock — cannot be sold.</p>
                        <p x-show="line.stockLabel && !line.outOfStock" class="text-xs text-gray-400 mt-0.5" x-text="line.stockLabel"></p>
                        <input type="hidden" :name="'lines['+i+'][inventory_item_id]'" :value="line.inventory_item_id">
                        <input type="hidden" :name="'lines['+i+'][metal_type]'" :value="line.metal_type">
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2 mb-2" x-show="['metal_in','metal_out'].includes(line.line_type) && !line.inventory_item_id">
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
                    <p class="col-span-3 text-xs text-amber-600 self-end pb-2">No matching inventory item — select the metal type so the correct account and rate are used.</p>
                </div>

                <div x-show="['metal_in','metal_out'].includes(line.line_type)">
                    {{-- Row 1: Gross weight + purity are the primary inputs; pure weight auto-calculates --}}
                    <div class="grid grid-cols-4 gap-2 mb-2">
                        <div>
                            <label class="block text-xs text-gray-400 mb-0.5">Gross (g) <span class="text-red-400">*</span></label>
                            <input type="number" step="0.001" min="0" :name="'lines['+i+'][gross_weight_grams]'" x-model.number="line.gross_weight_grams" @input="syncFromGross(line)"
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-0.5">Purity <span class="text-red-400">*</span></label>
                            <input type="number" step="0.001" min="0" max="1000" :name="'lines['+i+'][purity]'" x-model.number="line.purity" @input="syncFromGross(line)"
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-0.5">Pure (g)</label>
                            <input type="number" step="0.001" min="0" :name="'lines['+i+'][quantity_grams]'" x-model.number="line.quantity_grams" @input="syncFromPure(line)"
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-0.5">Pcs <span class="text-gray-400 text-xs font-normal">(optional)</span></label>
                            <input type="number" step="1" min="0" :name="'lines['+i+'][pcs]'" x-model.number="line.pcs" @input="syncFromPcs(line)"
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                            <p class="text-xs text-gray-400 mt-0.5" x-show="line.nominal_weight_grams" x-text="line.nominal_weight_grams + 'g each'"></p>
                        </div>
                    </div>
                    {{-- Row 2: Inline metal rate — appears as soon as an item with a metal type is picked --}}
                    <div x-show="line.metal_type" class="grid grid-cols-3 gap-2 mb-2 p-2 bg-amber-50 border border-amber-100 rounded-lg">
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">
                                <span x-text="(line.metal_type||'').charAt(0).toUpperCase()+(line.metal_type||'').slice(1)"></span>
                                price / oz (USD) <span x-show="pricingType === 'fixed'" class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.0001" :min="pricingType === 'fixed' ? '0.0001' : '0'"
                                   :name="'metal_rates['+line.metal_type+'][usd_per_oz]'"
                                   :value="metalRates[line.metal_type] ? metalRates[line.metal_type].usdPerOz : ''"
                                   @change="ensureMetalRate(line.metal_type); metalRates[line.metal_type].usdPerOz = parseFloat($event.target.value)"
                                   :required="pricingType === 'fixed'"
                                   class="w-full px-2 py-1.5 text-sm border border-amber-200 rounded-lg bg-white">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">USD → AED rate <span x-show="pricingType === 'fixed'" class="text-red-500">*</span></label>
                            <input type="number" step="0.0001" :min="pricingType === 'fixed' ? '0.0001' : '0'"
                                   :name="'metal_rates['+line.metal_type+'][usd_aed_rate]'"
                                   :value="metalRates[line.metal_type] ? metalRates[line.metal_type].usdAedRate : ''"
                                   @change="ensureMetalRate(line.metal_type); metalRates[line.metal_type].usdAedRate = parseFloat($event.target.value)"
                                   class="w-full px-2 py-1.5 text-sm border border-amber-200 rounded-lg bg-white">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-0.5">AED / gram (auto)</label>
                            <input type="number" step="0.000001" :value="rateFor(line.metal_type).toFixed(6)" readonly
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                        </div>
                    </div>
                    <p x-show="!line.metal_type" class="text-xs text-amber-600 mb-2">Pick an inventory item above to set the metal type and rate.</p>
                    <input type="hidden" :name="'lines['+i+'][unit_price]'" :value="rateFor(line.metal_type).toFixed(4)">
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
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-mono" x-text="fmt(subtotal)"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">VAT</span><span class="font-mono" x-text="fmt(vatTotal)"></span></div>
                <div class="flex justify-between text-green-700" x-show="premiumAed > 0"><span>Premium</span><span class="font-mono" x-text="'+' + fmt(premiumAed)"></span></div>
                <div class="flex justify-between text-red-600" x-show="discountAed > 0"><span>Discount</span><span class="font-mono" x-text="'-' + fmt(discountAed)"></span></div>
                <div class="flex justify-between font-semibold text-gray-800"><span>Total</span><span class="font-mono" x-text="fmt(total)"></span></div>
            </div>
        </div>
    </div>

    <datalist id="inventory-items-list">
        @foreach($items as $item)
        <option value="{{ $item->name }}">{{ $item->balance ? number_format($item->balance->quantity_grams, 3).'g in stock' : 'no stock record' }}</option>
        @endforeach
    </datalist>
    <datalist id="inventory-items-in-stock">
        @foreach($items->filter(fn($i) => $i->balance && $i->balance->quantity_grams > 0) as $item)
        <option value="{{ $item->name }}">{{ number_format($item->balance->quantity_grams, 3) }}g in stock</option>
        @endforeach
    </datalist>
    @endif


    {{-- ── Notes ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">{{ old('notes', $invoice->notes) }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            {{ $isPosted ? 'Save changes' : 'Save draft' }}
        </button>
        <a href="{{ route('tenant.accounting.invoices.show', [$tenant->slug, $invoice->id]) }}"
           class="px-4 py-2.5 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
    </div>
</form>

@if(!$isPosted)
<script>
function invoiceForm() {
    return {
        invoiceType: '{{ $invoice->invoice_type }}',
        pricingType: '{{ $invoice->pricing_type }}',
        metalRates: @json($existingMetalRates),
        premiumUsdPerOz: 0,
        discountUsdPerOz: 0,
        lines: [],
        itemsByName: {},
        init() {
            this.lines = @json($existingLines);
            // Ensure metalRates entries exist for every metal already on the lines
            this.lines.forEach(l => {
                if (l.metal_type && !this.metalRates[l.metal_type]) {
                    this.metalRates[l.metal_type] = { usdPerOz: null, usdAedRate: 3.674 };
                }
            });
            @json($itemsForJs)
                .forEach(it => this.itemsByName[it.name] = it);
        },
        stockLabel(item) {
            if (!item) return '';
            const g = item.stock_grams, pcs = item.stock_pcs;
            if (g <= 0) return '';
            return pcs > 0 ? `${pcs} pcs / ${g.toFixed(3)} g in stock` : `${g.toFixed(3)} g in stock`;
        },
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
        matchItemByName(line) {
            const match = this.itemsByName[line.description];
            if (match) {
                line.inventory_item_id = match.id;
                line.purity = match.purity;
                line.metal_type = match.metal_type;
                line.nominal_weight_grams = match.nominal_weight_grams;
                line.outOfStock = line.line_type === 'metal_out' && match.stock_grams <= 0;
                line.stockLabel = this.stockLabel(match);
                this.ensureMetalRate(match.metal_type);
                if (line.nominal_weight_grams && line.pcs) this.syncFromPcs(line);
                else this.syncFromGross(line);
            } else {
                line.inventory_item_id = '';
                line.metal_type = '';
                line.nominal_weight_grams = null;
                line.outOfStock = false;
                line.stockLabel = '';
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
                metal_type: '', nominal_weight_grams: null, purity: null, gross_weight_grams: null,
                quantity_grams: null, pcs: null, line_subtotal: null,
                metal_vat_treatment: 'zero_rated', metal_vat_rate: 0,
                making_charge_rate: null, making_vat_treatment: 'zero_rated', making_vat_rate: 0,
                outOfStock: false, stockLabel: '',
            };
        },
        addLine()        { this.lines.push(this.blankLine()); },
        removeLine(i)    { this.lines.splice(i, 1); },
        applyDefaultVatRate(line, treatmentKey, rateKey) {
            line[rateKey] = line[treatmentKey] === 'standard' ? 5 : 0;
        },
        syncFromGross(line) {
            const gross = parseFloat(line.gross_weight_grams), purity = parseFloat(line.purity);
            if (gross > 0 && purity > 0) {
                line.quantity_grams = Math.round(gross * (purity / 1000) * 1000) / 1000;
            }
        },
        syncFromPure(line) {
            const gross = parseFloat(line.gross_weight_grams);
            const pure = parseFloat(line.quantity_grams);
            if (gross > 0 && pure > 0) {
                line.purity = Math.round(pure / gross * 1000 * 1000) / 1000;
            }
        },
        syncFromPcs(line) {
            if (line.nominal_weight_grams && line.pcs) {
                line.gross_weight_grams = Math.round(line.pcs * line.nominal_weight_grams * 1000) / 1000;
                this.syncFromGross(line);
            }
        },
        isMetalLine(line) { return ['metal_in', 'metal_out'].includes(line.line_type); },
        lineSubtotal(line) {
            if (this.isMetalLine(line)) {
                return (parseFloat(line.quantity_grams) || 0) * this.rateFor(line.metal_type);
            }
            return parseFloat(line.line_subtotal) || 0;
        },
        lineMetalVat(line)   { return this.lineSubtotal(line) * ((parseFloat(line.metal_vat_rate) || 0) / 100); },
        lineMakingAmount(line) {
            return (parseFloat(line.quantity_grams) || 0) * (parseFloat(line.making_charge_rate) || 0);
        },
        lineMakingVat(line) { return this.lineMakingAmount(line) * ((parseFloat(line.making_vat_rate) || 0) / 100); },
        lineTotal(line)    { return this.lineSubtotal(line) + this.lineMetalVat(line) + this.lineMakingAmount(line) + this.lineMakingVat(line); },
        get subtotal()     { return this.lines.reduce((s, l) => s + this.lineSubtotal(l) + this.lineMakingAmount(l), 0); },
        get vatTotal()     { return this.lines.reduce((s, l) => s + this.lineMetalVat(l) + this.lineMakingVat(l), 0); },
        get premiumAed() {
            const usdPerOz = parseFloat(this.premiumUsdPerOz) || 0;
            if (usdPerOz <= 0) return 0;
            return this.lines.filter(l => this.isMetalLine(l) && l.metal_type).reduce((s, l) => {
                const grams = parseFloat(l.quantity_grams) || 0;
                const r = this.metalRates[l.metal_type];
                const usdAed = r ? (parseFloat(r.usdAedRate) || 3.674) : 3.674;
                return s + grams * usdPerOz / 31.1035 * usdAed;
            }, 0);
        },
        get discountAed() {
            const usdPerOz = parseFloat(this.discountUsdPerOz) || 0;
            if (usdPerOz <= 0) return 0;
            return this.lines.filter(l => this.isMetalLine(l) && l.metal_type).reduce((s, l) => {
                const grams = parseFloat(l.quantity_grams) || 0;
                const r = this.metalRates[l.metal_type];
                const usdAed = r ? (parseFloat(r.usdAedRate) || 3.674) : 3.674;
                return s + grams * usdPerOz / 31.1035 * usdAed;
            }, 0);
        },
        get total()        { return this.subtotal + this.vatTotal + this.premiumAed - this.discountAed; },
        fmt(n) { return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    };
}
</script>
@endif

@endsection
