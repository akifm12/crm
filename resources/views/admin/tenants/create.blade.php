@extends('layouts.admin')
@section('title', 'New Tenant')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900">Create new tenant portal</h1>
    <p class="text-sm text-gray-500 mt-0.5">Set up a compliance portal for a new client</p>
</div>

<form method="POST" action="{{ route('kyc.tenants') }}" class="max-w-2xl space-y-5">
@csrf

@if($errors->any())
<div class="p-4 bg-red-50 border border-red-200 rounded-xl">
    <p class="text-sm font-semibold text-red-700 mb-1">Please fix the following:</p>
    <ul class="text-sm text-red-600 list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Portal details --}}
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Portal details</h3>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Company name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Portal slug <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-1">
                    <span class="text-xs text-gray-400 flex-shrink-0">bluearrow.ae/</span>
                    <input type="text" name="slug" value="{{ old('slug') }}" required
                           placeholder="e.g. prince-jewellers"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <p class="text-xs text-gray-400 mt-1">Lowercase, letters, numbers and hyphens only</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Business sector <span class="text-red-500">*</span></label>
                <select name="business_type" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— Select sector —</option>
                    @foreach($sectors as $key => $label)
                    <option value="{{ $key }}" {{ old('business_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Determines form fields, declarations and risk factors</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Contact email <span class="text-red-500">*</span></label>
                <input type="email" name="contact_email" value="{{ old('contact_email') }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">DNFBP registration number</label>
                <input type="text" name="dnfbp_reg_no" value="{{ old('dnfbp_reg_no') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Business address</label>
            <input type="text" name="address" value="{{ old('address') }}"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
</div>

{{-- Portal admin account --}}
<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Portal admin account</h3>
    <p class="text-xs text-gray-400 mb-4">This person will log in to manage the compliance portal.</p>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Initial password <span class="text-red-500">*</span></label>
            <input type="text" name="admin_password" value="{{ old('admin_password') }}" required minlength="8"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-400 mt-1">The admin can change this after first login from Settings.</p>
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
        Create portal
    </button>
</div>

</form>
@endsection
