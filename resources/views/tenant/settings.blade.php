@extends('layouts.tenant')
@section('title', 'Settings — ' . $tenant->name)
@section('page-title', 'Settings')
@section('page-subtitle', 'Manage your portal configuration')

@section('content')

<div class="max-w-2xl space-y-5">

    {{-- ── COMPANY PROFILE ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Company profile</h3>
            <p class="text-xs text-gray-400 mt-0.5">Basic details about your business</p>
        </div>
        <form method="POST" action="{{ route('tenant.settings.profile', $tenant->slug) }}" class="p-5 space-y-4">
            @csrf @method('PATCH')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Contact email <span class="text-red-500">*</span></label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Trade licence number</label>
                    <input type="text" name="trade_license_no" value="{{ old('trade_license_no', $tenant->trade_license_no) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">DNFBP registration number</label>
                    <input type="text" name="dnfbp_reg_no" value="{{ old('dnfbp_reg_no', $tenant->dnfbp_reg_no) }}"
                           placeholder="Your CBUAE DNFBP registration number"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Business address</label>
                <textarea name="address" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('address', $tenant->address) }}</textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Save profile
                </button>
            </div>
        </form>
    </div>

    {{-- ── MLRO DETAILS ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">MLRO details</h3>
            <p class="text-xs text-gray-400 mt-0.5">Money Laundering Reporting Officer — appears on compliance documents</p>
        </div>
        <form method="POST" action="{{ route('tenant.settings.mlro', $tenant->slug) }}" class="p-5 space-y-4">
            @csrf @method('PATCH')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">MLRO name <span class="text-red-500">*</span></label>
                    <input type="text" name="mlro_name" value="{{ old('mlro_name', $tenant->mlro_name) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">MLRO email <span class="text-red-500">*</span></label>
                    <input type="email" name="mlro_email" value="{{ old('mlro_email', $tenant->mlro_email) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">MLRO phone</label>
                    <input type="text" name="mlro_phone" value="{{ old('mlro_phone', $tenant->mlro_phone) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Save MLRO details
                </button>
            </div>
        </form>
    </div>

    {{-- ── LOGO ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Portal logo</h3>
            <p class="text-xs text-gray-400 mt-0.5">Shown in the sidebar of your compliance portal</p>
        </div>
        <div class="p-5">
            @if($tenant->logo_url)
            <div class="mb-4 p-3 bg-gray-50 rounded-lg inline-block">
                <img src="{{ Storage::url($tenant->logo_url) }}" alt="Logo" class="h-12 object-contain">
            </div>
            @endif
            <form method="POST" action="{{ route('tenant.settings.logo', $tenant->slug) }}"
                  enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="logo" accept=".jpg,.jpeg,.png,.svg"
                       class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex-shrink-0">
                    Upload logo
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2">JPG, PNG or SVG — max 2MB. Recommended: 160×40px, transparent background.</p>
        </div>
    </div>

    {{-- ── GOAML QUICK LINK ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">goAML configuration</h3>
                <p class="text-xs text-gray-400 mt-0.5">MLRO and entity details for goAML XML reports</p>
                @if($goaml && $goaml->isComplete())
                <span class="inline-flex items-center gap-1 mt-1 text-xs text-green-700 bg-green-100 px-2 py-0.5 rounded-full font-semibold">
                    ✓ Configured — Entity ID: {{ $goaml->rentity_id }}
                </span>
                @else
                <span class="inline-flex items-center gap-1 mt-1 text-xs text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full font-semibold">
                    ⚠ Not configured
                </span>
                @endif
            </div>
            <a href="{{ route('tenant.goaml.settings', $tenant->slug) }}"
               class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition flex-shrink-0">
                {{ $goaml && $goaml->isComplete() ? 'Edit config' : 'Configure now' }} →
            </a>
        </div>
    </div>

    {{-- ── PORTAL INFO ──────────────────────────────────────────────────── --}}
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Portal information</h3>
        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-gray-500">Portal URL</span>
                <a href="{{ $tenant->portalUrl() }}" target="_blank"
                   class="text-blue-600 hover:underline font-mono text-xs">{{ $tenant->portalUrl() }}</a>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-500">Slug</span>
                <span class="font-mono text-xs text-gray-700">{{ $tenant->slug }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-500">Status</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── CHANGE PASSWORD ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Change password</h3>
        </div>
        <form method="POST" action="{{ route('tenant.settings.password', $tenant->slug) }}" class="p-5 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Current password</label>
                <input type="password" name="current_password" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('current_password')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">New password</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Confirm new password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-gray-700 rounded-lg hover:bg-gray-800 transition">
                    Update password
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
