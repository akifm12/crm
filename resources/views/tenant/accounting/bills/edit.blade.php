@extends('layouts.tenant')
@section('title', 'Edit ' . $bill->bill_number . ' — ' . $tenant->name)
@section('page-title', 'Edit ' . $bill->bill_number)
@section('page-subtitle', $bill->supplier_name)

@section('content')

@if($errors->any())
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">
    <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

@php
$existingLines = $bill->lines->map(fn ($l) => [
    'description'   => $l->description,
    'category'      => $l->category,
    'amount'        => (float) $l->amount,
    'vat_treatment' => $l->vat_treatment,
    'vat_rate'      => (float) $l->vat_rate,
])->values()->all();
@endphp

<form method="POST" action="{{ route('tenant.accounting.bills.update', [$tenant->slug, $bill->id]) }}"
      x-data="billForm()"
      @keydown.enter="if ($event.target.tagName === 'INPUT' && $event.target.type !== 'submit') $event.preventDefault()"
      class="max-w-4xl">
    @csrf @method('PUT')

    {{-- Header --}}
    @php
        $suppliersList = $suppliers->map(fn($s) => ['id'=>$s->id,'name'=>$s->name,'vat'=>$s->vat_number??''])->values()->toArray();
        $initSupplierId = old('supplier_id', $bill->supplier_id ?? ($suppliers->isEmpty() ? '0' : ''));
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5"
         x-data="{
             suppliers: @json($suppliersList),
             selectedId: '{{ $initSupplierId }}',
             manualName: '{{ old('supplier_name', $bill->supplier_name) }}',
             manualVat: '{{ old('supplier_vat_number', $bill->supplier_vat_number) }}',
             get isManual() { return this.selectedId === '' || this.selectedId === '0'; },
             pick(id) {
                 this.selectedId = id;
                 if (!this.isManual) {
                     let s = this.suppliers.find(x => x.id == id);
                     if (s) { this.manualName = s.name; this.manualVat = s.vat; }
                 }
             }
         }">
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Supplier <span class="text-red-500">*</span></label>
                <select name="supplier_id" x-model="selectedId" @change="pick(selectedId)"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                    <option value="">— Select supplier —</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                    <option value="0">Other / manual entry</option>
                </select>
            </div>
            <div x-show="!isManual && selectedId !== ''" x-cloak>
                <label class="block text-xs font-medium text-gray-600 mb-1">Supplier VAT #</label>
                <p class="px-3 py-2 text-sm text-gray-700" x-text="manualVat || '—'"></p>
            </div>
        </div>
        <input type="hidden" name="supplier_name" :value="isManual ? manualName : (suppliers.find(x=>x.id==selectedId)?.name ?? '')">
        <input type="hidden" name="supplier_vat_number" :value="isManual ? manualVat : (suppliers.find(x=>x.id==selectedId)?.vat ?? '')">
        <div x-show="isManual" x-cloak class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Supplier name <span class="text-red-500">*</span></label>
                <input type="text" x-model="manualName" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Supplier VAT number</label>
                <input type="text" x-model="manualVat" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Bill date <span class="text-red-500">*</span></label>
                <input type="date" name="bill_date" value="{{ old('bill_date', $bill->bill_date->toDateString()) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Due date</label>
                <input type="date" name="due_date" value="{{ old('due_date', $bill->due_date?->toDateString()) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Supplier's ref / invoice #</label>
                <input type="text" name="reference" value="{{ old('reference', $bill->reference) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            </div>
        </div>
    </div>

    {{-- Lines --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700">Line items</h3>
            <button type="button" @click="addLine()"
                    class="px-3 py-1.5 text-xs font-semibold text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
                + Add line
            </button>
        </div>

        <template x-for="(line, i) in lines" :key="i">
            <div class="border border-gray-200 rounded-lg p-3 mb-3">
                <div class="grid grid-cols-12 gap-2 mb-2">
                    <div class="col-span-5">
                        <label class="block text-xs text-gray-400 mb-0.5">Description</label>
                        <input type="text" :name="'lines['+i+'][description]'" x-model="line.description" required
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-400 mb-0.5">Category</label>
                        <select :name="'lines['+i+'][category]'" x-model="line.category"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                            @foreach(App\Models\Bill::CATEGORIES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-400 mb-0.5">Amount (AED)</label>
                        <input type="number" step="0.01" min="0.01" :name="'lines['+i+'][amount]'" x-model.number="line.amount" required
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                    <div class="col-span-1 flex items-end pb-1">
                        <button type="button" @click="removeLine(i)" x-show="lines.length > 1"
                                class="text-red-400 hover:text-red-600 text-xs">✕</button>
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">VAT treatment</label>
                        <select :name="'lines['+i+'][vat_treatment]'" x-model="line.vat_treatment"
                                @change="applyVatDefault(line)"
                                class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-white">
                            <option value="standard">Standard (5%)</option>
                            <option value="zero_rated">Zero-rated (0%)</option>
                            <option value="exempt">Exempt</option>
                            <option value="reverse_charge">Reverse charge</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">VAT rate %</label>
                        <input type="number" step="0.01" min="0" max="100"
                               :name="'lines['+i+'][vat_rate]'" x-model.number="line.vat_rate"
                               :readonly="line.vat_treatment !== 'standard' && line.vat_treatment !== 'reverse_charge'"
                               :class="(line.vat_treatment !== 'standard' && line.vat_treatment !== 'reverse_charge') ? 'bg-gray-50 text-gray-400' : ''"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg text-right">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">VAT amount</label>
                        <input type="number" readonly :value="lineVat(line).toFixed(2)"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500 text-right">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-0.5">Line total</label>
                        <input type="number" readonly :value="lineTotal(line).toFixed(2)"
                               class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg bg-gray-50 font-semibold text-right">
                    </div>
                </div>
            </div>
        </template>

        <div class="mt-4 flex justify-end">
            <div class="w-56 text-sm space-y-1">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span class="font-mono" x-text="subtotal.toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>VAT</span>
                    <span class="font-mono" x-text="vatTotal.toFixed(2)"></span>
                </div>
                <div class="flex justify-between font-semibold text-gray-800 border-t border-gray-200 pt-1">
                    <span>Total</span>
                    <span class="font-mono" x-text="total.toFixed(2)"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">{{ old('notes', $bill->notes) }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Save draft
        </button>
        <a href="{{ route('tenant.accounting.bills.show', [$tenant->slug, $bill->id]) }}"
           class="px-4 py-2.5 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
    </div>
</form>

<script>
function billForm() {
    return {
        lines: @json($existingLines),
        addLine() {
            this.lines.push({ description: '', category: 'other', amount: '', vat_treatment: 'standard', vat_rate: 5 });
        },
        removeLine(i) {
            this.lines.splice(i, 1);
        },
        applyVatDefault(line) {
            if (line.vat_treatment === 'standard' || line.vat_treatment === 'reverse_charge') {
                line.vat_rate = 5;
            } else {
                line.vat_rate = 0;
            }
        },
        lineVat(line) {
            const amt = parseFloat(line.amount) || 0;
            const rate = parseFloat(line.vat_rate) || 0;
            if (line.vat_treatment === 'standard' || line.vat_treatment === 'reverse_charge') {
                return Math.round(amt * rate / 100 * 100) / 100;
            }
            return 0;
        },
        lineTotal(line) {
            return (parseFloat(line.amount) || 0) + this.lineVat(line);
        },
        get subtotal() {
            return this.lines.reduce((s, l) => s + (parseFloat(l.amount) || 0), 0);
        },
        get vatTotal() {
            return this.lines.reduce((s, l) => s + this.lineVat(l), 0);
        },
        get total() {
            return this.subtotal + this.vatTotal;
        },
    };
}
</script>

@endsection
