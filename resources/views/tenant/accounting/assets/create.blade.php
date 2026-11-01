@extends('layouts.tenant')
@section('title', 'New Fixed Asset — ' . $tenant->name)
@section('page-title', 'New Fixed Asset')
@section('page-subtitle', 'Add an asset to the register')

@section('content')
<div class="max-w-lg">
    <form method="POST" action="{{ route('tenant.accounting.assets.store', $tenant->slug) }}" class="space-y-4">
        @csrf

        @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Asset Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— None —</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date *</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Cost (AED) *</label>
                <input type="number" name="purchase_cost" value="{{ old('purchase_cost') }}" step="0.01" min="0" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Salvage Value (AED)</label>
                <input type="number" name="salvage_value" value="{{ old('salvage_value', 0) }}" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Useful Life (months) *</label>
                <input type="number" name="useful_life_months" value="{{ old('useful_life_months', 60) }}" min="1" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Depreciation Method</label>
            <select name="depreciation_method" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="straight_line" {{ old('depreciation_method','straight_line') === 'straight_line' ? 'selected' : '' }}>Straight Line</option>
                <option value="declining_balance" {{ old('depreciation_method') === 'declining_balance' ? 'selected' : '' }}>Declining Balance</option>
                <option value="none" {{ old('depreciation_method') === 'none' ? 'selected' : '' }}>None</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>

        <div class="pt-2 flex gap-3">
            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Create asset
            </button>
            <a href="{{ route('tenant.accounting.assets.index', $tenant->slug) }}"
               class="px-5 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</a>
        </div>
    </form>
</div>
@endsection
