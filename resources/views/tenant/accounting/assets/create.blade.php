@extends('layouts.tenant')
@section('title', 'Add Asset — ' . $tenant->name)
@section('page-title', 'Add Fixed Asset')
@section('page-subtitle', 'Register a new asset')

@section('content')
<form method="POST" action="{{ route('tenant.accounting.assets.store', $tenant->slug) }}">
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Asset details --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Asset Details</h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2"><label class="block text-xs text-gray-500 mb-1">Asset Name *</label><input type="text" name="name" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Category</label>
                <select name="category_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Supplier</label>
                <select name="supplier_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Serial Number</label><input type="text" name="serial_number" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Location</label><input type="text" name="location" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Assigned To</label><input type="text" name="assigned_to" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Purchase Invoice Ref</label><input type="text" name="purchase_invoice_ref" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Insurance Policy No</label><input type="text" name="insurance_policy_no" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Insurance Expiry</label><input type="date" name="insurance_expiry" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div class="col-span-2"><label class="block text-xs text-gray-500 mb-1">Description</label><textarea name="description" rows="2" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></textarea></div>
        </div>
    </div>

    {{-- Financial --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Financials</h3>
            <div class="space-y-3">
                <div><label class="block text-xs text-gray-500 mb-1">Purchase Date *</label><input type="date" name="purchase_date" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label class="block text-xs text-gray-500 mb-1">In Service Date</label><input type="date" name="in_service_date" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Purchase Cost *</label><input type="number" step="0.01" name="purchase_cost" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Residual Value</label><input type="number" step="0.01" name="residual_value" value="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Useful Life (years)</label><input type="number" step="0.5" name="useful_life_years" value="5" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Depreciation Method</label>
                    <select name="depreciation_method" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                        <option value="straight_line">Straight Line</option>
                        <option value="declining_balance">Declining Balance</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">GL Accounts *</h3>
            <div class="space-y-3">
                @foreach([['asset_account_id','Asset Account (ASSET)'],['accum_depr_account_id','Accum. Depr. (ASSET/contra)'],['depr_expense_account_id','Depr. Expense (EXPENSE)']] as [$field,$label])
                <div><label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                    <select name="{{ $field }}" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">— Select —</option>
                        @foreach($accounts as $type => $accts)
                        <optgroup label="{{ $type }}">
                            @foreach($accts as $a)<option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>@endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="mt-5 flex gap-3">
    <button type="submit" class="px-6 py-2 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">Save Asset</button>
    <a href="{{ route('tenant.accounting.assets.index', $tenant->slug) }}" class="px-6 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
</div>
</form>
@endsection
