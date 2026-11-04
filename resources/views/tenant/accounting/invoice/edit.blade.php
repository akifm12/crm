@extends('layouts.tenant')
@section('title', 'Edit ' . $invoice->invoice_number)
@section('page-title', 'Edit Invoice')
@section('page-subtitle', $invoice->invoice_number)

@section('content')
<form action="{{ route('tenant.accounting.invoices.update', [$tenant->slug, $invoice->id]) }}" method="POST">
@csrf @method('PUT')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Client *</label>
                    <select name="client_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                        @foreach($clients as $cl)
                        <option value="{{ $cl->id }}" {{ $invoice->client_id == $cl->id ? 'selected' : '' }}>{{ $cl->displayName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Invoice Date *</label>
                    <input type="date" name="invoice_date" value="{{ $invoice->invoice_date }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ $invoice->due_date }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">PO / Reference</label>
                    <input type="text" name="po_number" value="{{ $invoice->po_number }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
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
                        @foreach($lines as $i => $line)
                        <tr class="border-t border-gray-100">
                            <td class="px-2 py-1">
                                <select name="item_ids[]" data-role="item-select" class="w-full text-xs border border-gray-200 rounded px-2 py-1">
                                    <option value="">— none —</option>
                                    @foreach($items as $itm)
                                    <option value="{{ $itm->id }}" {{ $line->inventory_item_id == $itm->id ? 'selected' : '' }}>{{ $itm->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-2 py-1"><input type="text" name="descriptions[]" data-role="desc" value="{{ $line->description }}" class="w-full text-xs border border-gray-200 rounded px-2 py-1"></td>
                            <td class="px-2 py-1"><input type="number" name="quantities[]" data-role="qty" step="0.0001" min="0" value="{{ $line->quantity }}" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right" oninput="recalcRow(this.closest('tr'))"></td>
                            <td class="px-2 py-1">
                                <select name="units[]" data-role="unit" class="w-full text-xs border border-gray-200 rounded px-1 py-1">
                                    @foreach(['pcs','grams','oz','kg'] as $u)
                                    <option value="{{ $u }}" {{ $line->unit === $u ? 'selected' : '' }}>{{ $u }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-2 py-1"><input type="number" name="unit_prices[]" data-role="price" step="0.0001" min="0" value="{{ $line->unit_price }}" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right" oninput="recalcRow(this.closest('tr'))"></td>
                            <td class="px-2 py-1"><input type="number" name="vat_rates[]" data-role="vat" step="0.01" min="0" max="100" value="{{ $line->vat_rate }}" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right" oninput="recalcRow(this.closest('tr'))"></td>
                            <td class="px-2 py-1 text-right font-mono text-xs text-gray-600"><span data-role="line-total">{{ number_format($line->total_amount, 2) }}</span></td>
                            <td class="px-2 py-1 text-center"><button type="button" onclick="this.closest('tr').remove(); recalcTotals()" class="text-red-400 hover:text-red-600 text-xs">×</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <button type="button" onclick="addInvLine()" class="text-sm text-blue-600 hover:underline">+ Add line</button>
                <div class="text-right space-y-1">
                    <div class="flex justify-between gap-8 text-xs text-gray-500"><span>Subtotal</span><span id="inv-subtotal" class="font-mono">{{ number_format($invoice->subtotal, 2) }}</span></div>
                    <div class="flex justify-between gap-8 text-xs text-gray-500"><span>VAT</span><span id="inv-vat" class="font-mono">{{ number_format($invoice->vat_amount, 2) }}</span></div>
                    <div class="flex justify-between gap-8 text-sm font-semibold text-gray-800"><span>Total (AED)</span><span id="inv-total" class="font-mono">{{ number_format($invoice->total_amount, 2) }}</span></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">{{ $invoice->notes }}</textarea>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save Changes</button>
            <a href="{{ route('tenant.accounting.invoices.show', [$tenant->slug, $invoice->id]) }}" class="block text-center py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</a>
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
    const div  = document.createElement('div'); div.innerHTML = tpl;
    document.getElementById('inv-lines-body').appendChild(div.firstElementChild);
    bindRowEvents(div.firstElementChild);
}
function bindRowEvents(row) {
    row.querySelectorAll('input[type=number]').forEach(el => el.addEventListener('input', () => recalcRow(row)));
}
function recalcRow(row) {
    const qty = parseFloat(row.querySelector('[data-role="qty"]')?.value || 0);
    const price = parseFloat(row.querySelector('[data-role="price"]')?.value || 0);
    const vat = parseFloat(row.querySelector('[data-role="vat"]')?.value || 0);
    const line = qty * price;
    const total = row.querySelector('[data-role="line-total"]');
    if (total) total.textContent = (line + line * vat / 100).toFixed(2);
    recalcTotals();
}
function recalcTotals() {
    let sub = 0, v = 0;
    document.querySelectorAll('#inv-lines-body tr').forEach(row => {
        const qty = parseFloat(row.querySelector('[data-role="qty"]')?.value || 0);
        const price = parseFloat(row.querySelector('[data-role="price"]')?.value || 0);
        const vatR = parseFloat(row.querySelector('[data-role="vat"]')?.value || 0);
        const line = qty * price;
        sub += line; v += line * vatR / 100;
    });
    const disc = parseFloat(document.querySelector('[name="discount_amount"]')?.value || 0);
    document.getElementById('inv-subtotal').textContent = sub.toFixed(2);
    document.getElementById('inv-vat').textContent = v.toFixed(2);
    document.getElementById('inv-total').textContent = (sub - disc + v).toFixed(2);
}
document.querySelectorAll('#inv-lines-body tr').forEach(r => bindRowEvents(r));
</script>
@endsection
