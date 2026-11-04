@extends('layouts.tenant')
@section('title', 'New Invoice — ' . $tenant->name)
@section('page-title', 'New Invoice')
@section('page-subtitle', 'Create a customer invoice')

@section('content')
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<form action="{{ route('tenant.accounting.invoices.store', $tenant->slug) }}" method="POST">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Line items --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Client *</label>
                    <select name="client_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">— select client —</option>
                        @foreach($clients as $cl)
                        <option value="{{ $cl->id }}" {{ (old('client_id') == $cl->id || optional($selectedClient)->id == $cl->id) ? 'selected' : '' }}>
                            {{ $cl->displayName() }}{{ $cl->trn_number ? ' · ' . $cl->trn_number : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Invoice Date *</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">PO / Reference</label>
                    <input type="text" name="po_number" value="{{ old('po_number') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
        </div>

        {{-- Lines --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="inv-lines">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-3 py-2 text-left w-48">Item</th>
                            <th class="px-3 py-2 text-left">Description</th>
                            <th class="px-3 py-2 text-right w-20">Qty</th>
                            <th class="px-3 py-2 text-left w-16">Unit</th>
                            <th class="px-3 py-2 text-right w-28">Unit Price</th>
                            <th class="px-3 py-2 text-right w-20">VAT %</th>
                            <th class="px-3 py-2 text-right w-28">Total</th>
                            <th class="px-3 py-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="inv-lines-body">
                        @for($i = 0; $i < 3; $i++)
                        @include('tenant.accounting.invoice._line_row', ['i' => $i, 'items' => $items, 'accounts' => $accounts])
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <button type="button" onclick="addInvLine()" class="text-sm text-blue-600 hover:underline">+ Add line</button>
                <div class="text-right space-y-1">
                    <div class="flex justify-between gap-8 text-xs text-gray-500"><span>Subtotal</span><span id="inv-subtotal" class="font-mono">0.00</span></div>
                    <div class="flex justify-between gap-8 text-xs text-gray-500"><span>Discount</span>
                        <input type="number" name="discount_amount" step="0.01" min="0" value="0" class="w-24 text-xs border border-gray-200 rounded px-2 py-0.5 text-right font-mono" oninput="recalcTotals()">
                    </div>
                    <div class="flex justify-between gap-8 text-xs text-gray-500"><span>VAT</span><span id="inv-vat" class="font-mono">0.00</span></div>
                    <div class="flex justify-between gap-8 text-sm font-semibold text-gray-800"><span>Total (AED)</span><span id="inv-total" class="font-mono">0.00</span></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">{{ old('notes') }}</textarea>
        </div>
    </div>

    {{-- Right: Actions --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Draft</button>
            <a href="{{ route('tenant.accounting.invoices.index', $tenant->slug) }}" class="block text-center py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</a>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-amber-800 mb-1">Gold VAT Guide (UAE)</p>
            <ul class="text-xs text-amber-700 space-y-0.5">
                <li>Investment gold ≥99% purity → 0%</li>
                <li>Jewelry / manufactured gold → 5%</li>
                <li>Silver bars → 5%</li>
            </ul>
        </div>
    </div>
</div>
</form>

<template id="inv-line-template">
    @include('tenant.accounting.invoice._line_row', ['i' => '__IDX__', 'items' => $items, 'accounts' => $accounts])
</template>

<script>
const inventoryItems = @json($items->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'price' => $i->current_price, 'vat_rate' => $i->vat_rate, 'unit' => $i->unit]));

function addInvLine() {
    const idx  = document.querySelectorAll('#inv-lines-body tr').length;
    const tpl  = document.getElementById('inv-line-template').innerHTML.replace(/__IDX__/g, idx);
    const div  = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('inv-lines-body').appendChild(div.firstElementChild);
    bindRowEvents(div.firstElementChild);
}

function bindRowEvents(row) {
    const itemSel = row.querySelector('[data-role="item-select"]');
    if (itemSel) {
        itemSel.addEventListener('change', function() {
            const item = inventoryItems.find(i => i.id == this.value);
            if (!item) return;
            const desc = row.querySelector('[name$="[description]"], [data-role="desc"]') || row.querySelector('input[name*="descriptions"]');
            const price = row.querySelector('[data-role="price"]') || row.querySelector('input[name*="unit_prices"]');
            const vat   = row.querySelector('[data-role="vat"]') || row.querySelector('input[name*="vat_rates"]');
            const unit  = row.querySelector('[data-role="unit"]') || row.querySelector('select[name*="units"]');
            if (desc && !desc.value) desc.value = item.name;
            if (price) price.value = item.price;
            if (vat)   vat.value   = item.vat_rate;
            if (unit)  unit.value  = item.unit;
            recalcRow(row);
        });
    }
    row.querySelectorAll('input[type=number]').forEach(el => el.addEventListener('input', () => recalcRow(row)));
}

function recalcRow(row) {
    const qty   = parseFloat(row.querySelector('[data-role="qty"]')?.value || 0);
    const price = parseFloat(row.querySelector('[data-role="price"]')?.value || 0);
    const vat   = parseFloat(row.querySelector('[data-role="vat"]')?.value || 0);
    const line  = qty * price;
    const vatAmt= line * vat / 100;
    const total = row.querySelector('[data-role="line-total"]');
    if (total) total.textContent = (line + vatAmt).toFixed(2);
    recalcTotals();
}

function recalcTotals() {
    let subtotal = 0, vatSum = 0;
    document.querySelectorAll('#inv-lines-body tr').forEach(row => {
        const qty   = parseFloat(row.querySelector('[data-role="qty"]')?.value || 0);
        const price = parseFloat(row.querySelector('[data-role="price"]')?.value || 0);
        const vatR  = parseFloat(row.querySelector('[data-role="vat"]')?.value || 0);
        const line  = qty * price;
        subtotal   += line;
        vatSum     += line * vatR / 100;
    });
    const discount = parseFloat(document.querySelector('[name="discount_amount"]')?.value || 0);
    document.getElementById('inv-subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('inv-vat').textContent      = vatSum.toFixed(2);
    document.getElementById('inv-total').textContent    = (subtotal - discount + vatSum).toFixed(2);
}

document.querySelectorAll('#inv-lines-body tr').forEach(r => bindRowEvents(r));
</script>
@endsection
