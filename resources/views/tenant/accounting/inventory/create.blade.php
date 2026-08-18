@extends('layouts.tenant')
@section('title', 'New Inventory Item — ' . $tenant->name)
@section('page-title', 'New Inventory Item')
@section('page-subtitle', 'Define a metal SKU to track stock and cost for')

@section('content')

<form method="POST" action="{{ route('tenant.accounting.inventory.store', $tenant->slug) }}"
      class="bg-white rounded-xl border border-gray-200 p-5 max-w-xl space-y-4"
      x-data="{
          metal: '',
          preset: '',
          weight: '',
          name: '',
          presets: {{ json_encode(\App\Support\BarWeightPresets::all()) }},
          applyPreset() {
              if (this.preset === '' || this.preset === 'custom') {
                  if (this.preset === 'custom') { this.weight = ''; }
                  return;
              }
              const p = this.presets[this.preset];
              this.weight = p.grams;
              if (this.metal) {
                  this.name = this.metal.charAt(0).toUpperCase() + this.metal.slice(1) + ' ' + p.label;
              }
          },
          onMetalChange() {
              if (this.preset !== '' && this.preset !== 'custom') {
                  this.applyPreset();
              }
          },
      }">
    @csrf
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Metal <span class="text-red-500">*</span></label>
            <select name="metal_type" required x-model="metal" x-on:change="onMetalChange()"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="">Select metal…</option>
                @foreach(\App\Models\InventoryItem::METAL_TYPES as $metal)
                    <option value="{{ $metal }}">{{ ucfirst($metal) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Bar / unit size</label>
            <select x-model="preset" x-on:change="applyPreset()"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="">—</option>
                <template x-for="(p, index) in presets" :key="index">
                    <option :value="index" x-text="p.label"></option>
                </template>
                <option value="custom">Custom weight…</option>
            </select>
        </div>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Name <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="e.g. Gold Bar 1kg 999.9" x-model="name"
               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
    </div>
    <div class="grid grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">SKU</label>
            <input type="text" name="sku" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Purity</label>
            <input type="number" step="0.001" name="purity" placeholder="999.900"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Form</label>
            <input type="text" name="form" list="form-types" autocomplete="off"
                   placeholder="Bar, Coin, Jewellery…"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
            <datalist id="form-types">
                @foreach($formTypes as $ft)
                <option value="{{ ucfirst($ft) }}"></option>
                @endforeach
            </datalist>
        </div>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Nominal weight (grams)</label>
        <input type="number" step="0.0001" name="nominal_weight_grams" x-model="weight"
               placeholder="Leave blank for arbitrary/scrap weights"
               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
        <p class="text-xs text-gray-400 mt-1">Informational catalog size only — actual stock quantity is always entered per movement.</p>
    </div>
    <div class="flex items-center justify-between">
        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Create item
        </button>
        <a href="{{ route('tenant.accounting.inventory.options', $tenant->slug) }}"
           class="text-xs text-gray-400 hover:text-gray-600">Manage form types →</a>
    </div>
</form>

@endsection
