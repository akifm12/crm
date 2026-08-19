@extends('layouts.tenant')

@php
    $isRE      = $moduleType === 'real_estate';
    $label     = 'Edit ' . $invoice->invoice_number;
    $rp        = $isRE ? 'tenant.accounting.re.invoices.' : 'tenant.accounting.general.invoices.';
    $initLines = old('lines', $invoice->lines->map(fn($l) => [
        'description'   => $l->description,
        'quantity'      => $l->quantity,
        'unit_price'    => $l->unit_price,
        'vat_treatment' => $l->vat_treatment,
        'vat_rate'      => $l->vat_rate,
    ])->toArray());
@endphp

@section('title', $label)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route($rp . 'show', [$slug, $invoice->id]) }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ $label }}</h1>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <script>
    function stdInvoiceEdit() {
        const isRE    = {{ $isRE ? 'true' : 'false' }};
        const clients = @json($clientsList);
        return {
            clientName: @json(old('client_name', $invoice->client_name)),
            clientVat:  @json(old('client_vat_number', $invoice->client_vat_number ?? '')),
            pickClient(id) {
                if (!id) return;
                const c = clients.find(x => x.id == id);
                if (c) { this.clientName = c.name; this.clientVat = c.trn; }
            },

            lines: @json($initLines),
            lineAmt(l) {
                const qty   = parseFloat(l.quantity)  || 0;
                const price = parseFloat(l.unit_price) || 0;
                return isRE ? qty * price / 100 : qty * price;
            },
            get subtotal() { return this.lines.reduce((s,l) => s + this.lineAmt(l), 0); },
            get vatTotal() {
                return this.lines.reduce((s,l) => {
                    if (!['standard','reverse_charge'].includes(l.vat_treatment)) return s;
                    return s + this.lineAmt(l) * (parseFloat(l.vat_rate)||0) / 100;
                }, 0);
            },
            get grandTotal() { return this.subtotal + this.vatTotal; },
            addLine()     { this.lines.push({description:'',quantity:'',unit_price:'',vat_treatment:'standard',vat_rate:5}); },
            removeLine(i) { if (this.lines.length > 1) this.lines.splice(i, 1); },
            fmt(n)        { return parseFloat(n||0).toFixed(2); },
        };
    }
    </script>

    <form method="POST" action="{{ route($rp . 'update', [$slug, $invoice->id]) }}" x-data="stdInvoiceEdit()">
        @csrf @method('PUT')

        {{-- Client --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Client</h2>

            @if(count($clientsList))
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select from client list</label>
                <select @change="pickClient($event.target.value)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Manual entry / not in list —</option>
                    @foreach($clientsList as $c)
                        <option value="{{ $c['id'] }}" {{ ($invoice->client_name === $c['name']) ? 'selected' : '' }}>
                            {{ $c['name'] }}{{ $c['trn'] ? ' (TRN: '.$c['trn'].')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Name <span class="text-red-500">*</span></label>
                    <input type="text" name="client_name" x-model="clientName" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client TRN / VAT No.</label>
                    <input type="text" name="client_vat_number" x-model="clientVat"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Invoice details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Invoice Details</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date <span class="text-red-500">*</span></label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Property / Contract No.</label>
                    <input type="text" name="reference" value="{{ old('reference', $invoice->reference) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Lines --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">
                {{ $isRE ? 'Commission Lines' : 'Line Items' }}
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left pb-2 font-medium text-gray-600 w-5/12">Description</th>
                            <th class="text-right pb-2 font-medium text-gray-600 w-2/12">{{ $isRE ? 'Property Value (AED)' : 'Qty' }}</th>
                            <th class="text-right pb-2 font-medium text-gray-600 w-2/12">{{ $isRE ? 'Commission %' : 'Unit Price' }}</th>
                            <th class="text-left pb-2 font-medium text-gray-600 w-2/12 px-2">VAT</th>
                            <th class="text-right pb-2 font-medium text-gray-600 w-1/12">{{ $isRE ? 'Commission' : 'Amount' }}</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, i) in lines" :key="i">
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-2">
                                    <input type="text" :name="`lines[${i}][description]`" x-model="line.description" required
                                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </td>
                                <td class="py-2 px-1">
                                    <input type="number" :name="`lines[${i}][quantity]`" x-model="line.quantity"
                                           min="0" step="any" required
                                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </td>
                                <td class="py-2 px-1">
                                    <input type="number" :name="`lines[${i}][unit_price]`" x-model="line.unit_price"
                                           min="0" step="any" required
                                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </td>
                                <td class="py-2 px-2">
                                    <select :name="`lines[${i}][vat_treatment]`" x-model="line.vat_treatment"
                                            class="border border-gray-300 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 w-full mb-1">
                                        <option value="standard">Standard 5%</option>
                                        <option value="zero_rated">Zero Rated</option>
                                        <option value="exempt">Exempt</option>
                                        <option value="reverse_charge">Reverse Charge</option>
                                    </select>
                                    <template x-if="['standard','reverse_charge'].includes(line.vat_treatment)">
                                        <input type="number" :name="`lines[${i}][vat_rate]`" x-model="line.vat_rate"
                                               min="0" max="100" step="0.01"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </template>
                                    <template x-if="!['standard','reverse_charge'].includes(line.vat_treatment)">
                                        <input type="hidden" :name="`lines[${i}][vat_rate]`" value="0">
                                    </template>
                                </td>
                                <td class="py-2 pl-2 text-right font-mono text-gray-800">
                                    <span x-text="fmt(lineAmt(line))"></span>
                                </td>
                                <td class="py-2 pl-1">
                                    <button type="button" @click="removeLine(i)" class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <button type="button" @click="addLine()"
                    class="mt-3 text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Line
            </button>

            <div class="mt-4 border-t border-gray-100 pt-4 flex flex-col items-end gap-1 text-sm">
                <div class="flex gap-8">
                    <span class="text-gray-500">{{ $isRE ? 'Total Commission' : 'Subtotal' }}</span>
                    <span class="font-mono w-28 text-right" x-text="'AED ' + fmt(subtotal)"></span>
                </div>
                <div class="flex gap-8">
                    <span class="text-gray-500">VAT</span>
                    <span class="font-mono w-28 text-right" x-text="'AED ' + fmt(vatTotal)"></span>
                </div>
                <div class="flex gap-8 font-semibold text-base border-t border-gray-200 pt-1 mt-1">
                    <span>Total</span>
                    <span class="font-mono w-28 text-right" x-text="'AED ' + fmt(grandTotal)"></span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $invoice->notes) }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                Save Changes
            </button>
            <a href="{{ route($rp . 'show', [$slug, $invoice->id]) }}"
               class="px-6 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
