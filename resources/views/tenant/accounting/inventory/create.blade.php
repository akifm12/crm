@extends('layouts.tenant')
@section('title', 'New Inventory Item')
@section('page-title', 'New Inventory Item')
@section('page-subtitle', 'Add a gold, silver or precious metal item')

@section('content')
<div class="max-w-2xl">
<form action="{{ route('tenant.accounting.inventory.store', $tenant->slug) }}" method="POST" class="space-y-5">
@csrf

<div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
    <h3 class="text-sm font-semibold text-gray-700">Basic Details</h3>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Item Code</label>
            <input type="text" name="code" value="{{ old('code') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" placeholder="e.g. AU-1KG-999">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
            <select name="category" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                <option value="gold_bar">Gold Bar</option>
                <option value="gold_coin">Gold Coin</option>
                <option value="jewelry">Jewelry</option>
                <option value="silver_bar">Silver Bar</option>
                <option value="silver_coin">Silver Coin</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-span-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">Name (English) *</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required placeholder="e.g. 1 KG Gold Bar PAMP Suisse">
        </div>
        <div class="col-span-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">Name (Arabic)</label>
            <input type="text" name="name_arabic" value="{{ old('name_arabic') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" dir="rtl">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
    <h3 class="text-sm font-semibold text-gray-700">Gold / Metal Specs</h3>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Unit</label>
            <select name="unit" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                <option value="pcs">pcs</option>
                <option value="grams">grams</option>
                <option value="oz">troy oz</option>
                <option value="kg">kg</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Gold Purity (‰)</label>
            <input type="number" name="gold_purity" value="{{ old('gold_purity') }}" step="0.1" min="0" max="999.9" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 font-mono" placeholder="999.9">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Weight per unit (g)</label>
            <input type="number" name="weight_per_unit_grams" value="{{ old('weight_per_unit_grams') }}" step="0.0001" min="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 font-mono" placeholder="1000.0">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
    <h3 class="text-sm font-semibold text-gray-700">Pricing & VAT</h3>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Current Sell Price (AED)</label>
            <input type="number" name="current_price" value="{{ old('current_price', 0) }}" step="0.0001" min="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 font-mono">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cost Price (AED)</label>
            <input type="number" name="cost_price" value="{{ old('cost_price', 0) }}" step="0.0001" min="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 font-mono">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">VAT Rate (%)</label>
            <select name="vat_rate" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                <option value="0">0% — Investment Gold</option>
                <option value="5">5% — Standard</option>
            </select>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
    <h3 class="text-sm font-semibold text-gray-700">Accounts & Opening Stock</h3>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sales Account</label>
            <select name="sales_account_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                <option value="">— none —</option>
                @foreach($accounts->where('type', 'REVENUE') as $acc)
                <option value="{{ $acc->id }}">{{ $acc->code }} – {{ $acc->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Opening Stock (qty)</label>
            <input type="number" name="opening_stock" value="{{ old('opening_stock', 0) }}" step="0.0001" min="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 font-mono">
        </div>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">{{ old('notes') }}</textarea>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Create Item</button>
    <a href="{{ route('tenant.accounting.inventory.index', $tenant->slug) }}" class="px-5 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</a>
</div>
</form>
</div>
@endsection
