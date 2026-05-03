@extends('layouts.admin')
@section('title', 'Edit Tenant — ' . $tenant->name)

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900">Edit — {{ $tenant->name }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">
        <a href="{{ $tenant->portalUrl() }}" target="_blank" class="text-blue-600 hover:underline font-mono text-xs">{{ $tenant->portalUrl() }}</a>
    </p>
</div>

<form method="POST" action="{{ route('kyc.tenants.update', $tenant->id) }}" class="max-w-2xl space-y-5">
@csrf @method('PATCH')

<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Portal details</h3>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Company name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Portal slug</label>
                <input type="text" value="{{ $tenant->slug }}" disabled
                       class="w-full px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-50 text-gray-400">
                <p class="text-xs text-gray-400 mt-1">Slug cannot be changed after creation</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Business sector <span class="text-red-500">*</span></label>
                <select name="business_type" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @foreach($sectors as $key => $label)
                    <option value="{{ $key }}" {{ old('business_type', $tenant->business_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Contact email <span class="text-red-500">*</span></label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">DNFBP registration number</label>
                <input type="text" name="dnfbp_reg_no" value="{{ old('dnfbp_reg_no', $tenant->dnfbp_reg_no) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ $tenant->is_active ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600">
            <label for="is_active" class="text-sm text-gray-700">Portal active — tenants can log in</label>
        </div>
    </div>
</div>

<div class="flex items-center justify-between">
    <a href="{{ route('kyc.tenants') }}"
       class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Cancel
    </a>
    <button type="submit"
            class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Save changes
    </button>
</div>

</form>
@endsection
