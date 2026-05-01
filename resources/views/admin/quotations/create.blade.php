@extends('layouts.admin')
@section('title', 'New Quotation')
@section('page-title', 'New Quotation')
@section('page-subtitle', 'Create a standalone quotation — for existing clients or new prospects')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<form method="POST" action="{{ route('quotations.store') }}"
      x-data="qtCreate()" class="max-w-4xl">
@csrf

<div class="space-y-5">

    {{-- Recipient --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Recipient</h2>
        </div>
        <div class="p-6 space-y-4">
            {{-- Client type toggle --}}
            <div class="flex gap-3">
                <button type="button" @click="clientType='existing'"
                        :class="clientType==='existing' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200'"
                        class="px-4 py-2 text-sm font-semibold border rounded-lg transition">
                    Existing CRM client
                </button>
                <button type="button" @click="clientType='prospect'"
                        :class="clientType==='prospect' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200'"
                        class="px-4 py-2 text-sm font-semibold border rounded-lg transition">
                    New prospect / custom
                </button>
            </div>
            <input type="hidden" name="client_type" :value="clientType">

            {{-- Existing client picker --}}
            <div x-show="clientType==='existing'">
                <label class="block text-xs font-medium text-gray-600 mb-1">Select client</label>
                <select name="crm_client_id" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select client —</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Prospect details --}}
            <div x-show="clientType==='prospect'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company / person name <span class="text-red-500">*</span></label>
                    <input type="text" name="recipient_name"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="recipient_email"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <input type="text" name="recipient_address"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>

    {{-- Quotation details --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Quotation details</h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subject / title <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" required x-model="subject"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Valid for (days)</label>
                    <input type="number" name="validity_days" value="30"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Template picker --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Load from template (optional)</label>
                <select @change="loadTemplate($event.target.value)"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Start from scratch or pick a template —</option>
                    @foreach($templates as $t)
                    <option value="{{ $t->id }}" data-items="{{ json_encode($t->line_items) }}" data-terms="{{ $t->terms }}" data-name="{{ $t->name }}">
                        {{ $t->name }}
                    </option>
                    @endforeach
                </select>
                <input type="hidden" name="quotation_template_id" :value="templateId">
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Line items</h2>
        </div>
        <div class="p-6">
            {{-- Column headers --}}
            <div class="grid grid-cols-12 gap-2 mb-2 px-1">
                <div class="col-span-7 text-xs font-semibold text-gray-500 uppercase">Description</div>
                <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase">Qty</div>
                <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase">Unit price (AED)</div>
                <div class="col-span-1"></div>
            </div>

            <template x-for="(item, i) in items" :key="i">
            <div class="grid grid-cols-12 gap-2 mb-2 items-center">
                <div class="col-span-7">
                    <input type="text" :name="'items['+i+'][description]'" x-model="item.description"
                           placeholder="Service description"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <input type="number" :name="'items['+i+'][qty]'" x-model.number="item.qty" min="1"
                           @input="calcTotals"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <input type="number" step="0.01" :name="'items['+i+'][unit_price]'" x-model.number="item.unit_price"
                           @input="calcTotals" placeholder="0.00"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-1 flex justify-center">
                    <button type="button" @click="items.splice(i,1); calcTotals()" x-show="items.length > 1"
                            class="text-red-400 hover:text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            </template>

            <button type="button" @click="items.push({description:'',qty:1,unit_price:0}); calcTotals()"
                    class="mt-2 flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add line item
            </button>

            {{-- Totals --}}
            <div class="mt-5 border-t border-gray-100 pt-4">
                <div class="flex justify-end">
                    <div class="w-64 space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="font-medium" x-text="'AED ' + subtotal.toLocaleString('en-AE', {minimumFractionDigits:2})"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">VAT (5%)</span>
                            <span class="font-medium" x-text="'AED ' + vat.toLocaleString('en-AE', {minimumFractionDigits:2})"></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold border-t border-gray-200 pt-1.5">
                            <span>Total</span>
                            <span x-text="'AED ' + total.toLocaleString('en-AE', {minimumFractionDigits:2})"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Terms --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-800">Terms & conditions</h2></div>
        <div class="p-6">
            <textarea name="terms" rows="6" x-model="terms"
                      class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"></textarea>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
            Create quotation
        </button>
        <a href="{{ route('quotations.index') }}" class="px-6 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Cancel
        </a>
    </div>

</div>
</form>

<script>
function qtCreate() {
    return {
        clientType: 'existing',
        templateId: '',
        subject:    '',
        items:      [{ description: '', qty: 1, unit_price: 0 }],
        terms:      '',
        subtotal:   0,
        vat:        0,
        total:      0,

        loadTemplate(id) {
            if (!id) return;
            const opt = document.querySelector(`option[value="${id}"]`);
            if (!opt) return;
            this.templateId = id;
            try {
                const loadedItems = JSON.parse(opt.dataset.items || '[]');
                if (loadedItems.length) this.items = loadedItems.map(i => ({ description: i.description || '', qty: i.qty || 1, unit_price: i.unit_price || 0 }));
            } catch(e) {}
            if (!this.subject) this.subject = opt.dataset.name || '';
            this.terms = opt.dataset.terms || this.terms;
            this.calcTotals();
        },

        calcTotals() {
            this.subtotal = this.items.reduce((s, i) => s + (Number(i.qty) * Number(i.unit_price)), 0);
            this.vat      = Math.round(this.subtotal * 0.05 * 100) / 100;
            this.total    = this.subtotal + this.vat;
        }
    }
}
</script>

@endsection
