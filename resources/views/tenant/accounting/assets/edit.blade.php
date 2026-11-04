@extends('layouts.tenant')
@section('title', 'Edit ' . $asset->name . ' — ' . $tenant->name)
@section('page-title', 'Edit Asset')
@section('page-subtitle', $asset->asset_number)

@section('content')
<form method="POST" action="{{ route('tenant.accounting.assets.update', [$tenant->slug, $asset->id]) }}">
@csrf @method('PUT')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold mb-4">Asset Details</h3>
        <div class="space-y-3">
            <div><label class="block text-xs text-gray-500 mb-1">Asset Name *</label><input type="text" name="name" value="{{ $asset->name }}" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Category</label>
                <select name="category_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ $asset->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Supplier</label>
                <select name="supplier_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($suppliers as $s)<option value="{{ $s->id }}" {{ $asset->supplier_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Serial Number</label><input type="text" name="serial_number" value="{{ $asset->serial_number }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Location</label><input type="text" name="location" value="{{ $asset->location }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Assigned To</label><input type="text" name="assigned_to" value="{{ $asset->assigned_to }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">In Service Date</label><input type="date" name="in_service_date" value="{{ $asset->in_service_date }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Insurance Policy No</label><input type="text" name="insurance_policy_no" value="{{ $asset->insurance_policy_no }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Insurance Expiry</label><input type="date" name="insurance_expiry" value="{{ $asset->insurance_expiry }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Purchase Invoice Ref</label><input type="text" name="purchase_invoice_ref" value="{{ $asset->purchase_invoice_ref }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Notes</label><textarea name="notes" rows="3" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">{{ $asset->notes }}</textarea></div>
        </div>
    </div>
</div>
<div class="mt-5 flex gap-3">
    <button type="submit" class="px-6 py-2 text-sm font-medium bg-gray-800 text-white rounded-lg">Save Changes</button>
    <a href="{{ route('tenant.accounting.assets.show', [$tenant->slug, $asset->id]) }}" class="px-6 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
</div>
</form>
@endsection
