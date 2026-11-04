@extends('layouts.tenant')
@section('title', 'New Bill — ' . $tenant->name)
@section('page-title', 'New Bill')
@section('page-subtitle', 'Create a supplier bill')

@section('content')
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<form action="{{ route('tenant.accounting.bills.store', $tenant->slug) }}" method="POST">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Bill Details</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Supplier *</label>
                        <select name="supplier_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                            <option value="">— select supplier —</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id || optional($selectedSupplier)->id==$s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bill Date *</label>
                        <input type="date" name="bill_date" value="{{ old('bill_date', date('Y-m-d')) }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Vendor Ref</label>
                        <input type="text" name="vendor_ref" value="{{ old('vendor_ref') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" placeholder="Supplier invoice #">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Line Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-left">Account</th>
                                <th class="px-3 py-2 text-left">Description</th>
                                <th class="px-3 py-2 w-20 text-right">Qty</th>
                                <th class="px-3 py-2 w-28 text-right">Unit Price</th>
                                <th class="px-3 py-2 w-16 text-right">VAT%</th>
                                <th class="px-3 py-2 w-28 text-right">Total</th>
                                <th class="px-3 py-2 w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="bill-lines-body">
                            @for($i = 0; $i < 3; $i++)
                            <tr class="border-t border-gray-100">
                                <td class="px-2 py-1">
                                    <select name="accounts[]" class="w-full text-xs border border-gray-200 rounded px-2 py-1">
                                        <option value="">— select —</option>
                                        @foreach($accounts as $type => $accs)
                                        <optgroup label="{{ $type }}">
                                            @foreach($accs as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2 py-1"><input type="text" name="descriptions[]" class="w-full text-xs border border-gray-200 rounded px-2 py-1"></td>
                                <td class="px-2 py-1"><input type="number" name="quantities[]" step="0.0001" min="0" value="1" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right line-qty"></td>
                                <td class="px-2 py-1"><input type="number" name="unit_prices[]" step="0.01" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right line-price"></td>
                                <td class="px-2 py-1"><input type="number" name="vat_rates[]" step="0.01" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right line-vat"></td>
                                <td class="px-2 py-1 text-right text-xs font-mono line-total">0.00</td>
                                <td class="px-2 py-1 text-center"><button type="button" onclick="this.closest('tr').remove();recalc()" class="text-red-400 text-xs">×</button></td>
                            </tr>
                            @endfor
                        </tbody>
                        <tfoot class="border-t-2 border-gray-200">
                            <tr><td colspan="5" class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Subtotal</td><td class="px-3 py-2 text-right text-xs font-mono" id="sub-total">0.00</td><td></td></tr>
                            <tr><td colspan="5" class="px-3 py-2 text-right text-xs text-gray-500">VAT</td><td class="px-3 py-2 text-right text-xs font-mono" id="vat-total">0.00</td><td></td></tr>
                            <tr class="bg-gray-50"><td colspan="5" class="px-3 py-2 text-right text-sm font-bold text-gray-800">Total</td><td class="px-3 py-2 text-right text-sm font-bold font-mono" id="grand-total">0.00</td><td></td></tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" onclick="addBillLine()" class="mt-3 text-sm text-blue-600 hover:underline">+ Add line</button>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-4">
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 mb-3">
                    Save Bill
                </button>
                <a href="{{ route('tenant.accounting.bills.index', $tenant->slug) }}" class="block text-center py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<template id="bill-line-template">
    <tr class="border-t border-gray-100">
        <td class="px-2 py-1">
            <select name="accounts[]" class="w-full text-xs border border-gray-200 rounded px-2 py-1">
                <option value="">— select —</option>
                @foreach($accounts as $type => $accs)
                <optgroup label="{{ $type }}">
                    @foreach($accs as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
        </td>
        <td class="px-2 py-1"><input type="text" name="descriptions[]" class="w-full text-xs border border-gray-200 rounded px-2 py-1"></td>
        <td class="px-2 py-1"><input type="number" name="quantities[]" step="0.0001" min="0" value="1" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right line-qty"></td>
        <td class="px-2 py-1"><input type="number" name="unit_prices[]" step="0.01" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right line-price"></td>
        <td class="px-2 py-1"><input type="number" name="vat_rates[]" step="0.01" min="0" value="0" class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-right line-vat"></td>
        <td class="px-2 py-1 text-right text-xs font-mono line-total">0.00</td>
        <td class="px-2 py-1 text-center"><button type="button" onclick="this.closest('tr').remove();recalc()" class="text-red-400 text-xs">×</button></td>
    </tr>
</template>

<script>
document.addEventListener('input', recalc);
function recalc() {
    let sub = 0, vat = 0;
    document.querySelectorAll('#bill-lines-body tr').forEach(row => {
        const qty   = parseFloat(row.querySelector('.line-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.line-price')?.value) || 0;
        const vatR  = parseFloat(row.querySelector('.line-vat')?.value) || 0;
        const line  = qty * price;
        const lineVat = line * vatR / 100;
        sub += line; vat += lineVat;
        const totalEl = row.querySelector('.line-total');
        if (totalEl) totalEl.textContent = (line + lineVat).toFixed(2);
    });
    document.getElementById('sub-total').textContent   = sub.toFixed(2);
    document.getElementById('vat-total').textContent   = vat.toFixed(2);
    document.getElementById('grand-total').textContent = (sub + vat).toFixed(2);
}
function addBillLine() {
    const tpl = document.getElementById('bill-line-template');
    document.getElementById('bill-lines-body').appendChild(tpl.content.cloneNode(true));
}
recalc();
</script>
@endsection
