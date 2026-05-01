@extends('layouts.admin')
@section('title', ($template ? 'Edit' : 'New') . ' Quotation Template')
@section('page-title', $template ? 'Edit Quotation Template' : 'New Quotation Template')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<form method="POST"
      action="{{ $template ? route('settings.qt.update', $template->id) : route('settings.qt.store') }}"
      class="max-w-3xl"
      x-data="qtForm()">
@csrf
@if($template) @method('PUT') @endif

<div class="space-y-5">

    {{-- Basic info --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Template details</h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Template name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $template?->name) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Service type <span class="text-red-500">*</span></label>
                    <input type="text" name="service_type" value="{{ old('service_type', $template?->service_type) }}" required
                           placeholder="e.g. Compliance & AML Support"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Short description</label>
                <textarea name="description" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $template?->description) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Validity (days)</label>
                <input type="number" name="validity_days" value="{{ old('validity_days', $template?->validity_days ?? 30) }}"
                       class="w-32 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Line items</h2>
            <p class="text-xs text-gray-400 mt-0.5">Set unit prices to 0 if they will be agreed per client</p>
        </div>
        <div class="p-6">
            {{-- Header --}}
            <div class="grid grid-cols-12 gap-2 mb-2 px-1">
                <div class="col-span-7 text-xs font-semibold text-gray-500 uppercase">Description</div>
                <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase">Qty</div>
                <div class="col-span-2 text-xs font-semibold text-gray-500 uppercase">Unit price (AED)</div>
                <div class="col-span-1"></div>
            </div>

            {{-- Items --}}
            <template x-for="(item, i) in items" :key="i">
            <div class="grid grid-cols-12 gap-2 mb-2 items-center">
                <div class="col-span-7">
                    <input type="text" :name="'items['+i+'][description]'" x-model="item.description"
                           placeholder="Service description"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <input type="number" :name="'items['+i+'][qty]'" x-model="item.qty" min="1" value="1"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <input type="number" step="0.01" :name="'items['+i+'][unit_price]'" x-model="item.unit_price"
                           placeholder="0.00"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-1 flex justify-center">
                    <button type="button" @click="items.splice(i,1)" x-show="items.length > 1"
                            class="text-red-400 hover:text-red-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            </template>

            <button type="button" @click="items.push({description:'',qty:1,unit_price:0})"
                    class="mt-2 flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add line item
            </button>
        </div>
    </div>

    {{-- Terms --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Terms & conditions</h2>
        </div>
        <div class="p-6">
            <textarea name="terms" rows="6"
                      class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">{{ old('terms', $template?->terms) }}</textarea>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
            {{ $template ? 'Update template' : 'Create template' }}
        </button>
        <a href="{{ route('settings.index') }}" class="px-6 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Cancel
        </a>
    </div>

</div>
</form>

<script>
function qtForm() {
    return {
        items: @json(old('items', $template?->line_items ?? [['description'=>'','qty'=>1,'unit_price'=>0]])),
    }
}
</script>

@endsection
