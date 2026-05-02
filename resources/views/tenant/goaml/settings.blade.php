@extends('layouts.tenant')
@section('title', 'goAML Configuration — ' . $tenant->name)
@section('page-title', 'goAML Configuration')
@section('page-subtitle', 'MLRO and entity details — saved once, used on every report')

@section('content')

<div class="max-w-2xl">
    <form method="POST" action="{{ route('tenant.goaml.settings.save', $tenant->slug) }}" class="space-y-5">
        @csrf

        {{-- Reporting entity --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Reporting entity</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">goAML Entity ID <span class="text-red-500">*</span></label>
                    <input type="text" name="rentity_id" value="{{ old('rentity_id', $config?->rentity_id) }}" required
                           placeholder="Your goAML system entity ID"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Issued by UAE FIU when you registered on goAML.</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Entity name <span class="text-red-500">*</span></label>
                        <input type="text" name="entity_name" value="{{ old('entity_name', $config?->entity_name) }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Country code <span class="text-red-500">*</span></label>
                        <input type="text" name="entity_country_code" value="{{ old('entity_country_code', $config?->entity_country_code ?? 'ARE') }}"
                               required maxlength="3" placeholder="ARE"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Business address <span class="text-red-500">*</span></label>
                    <input type="text" name="entity_address" value="{{ old('entity_address', $config?->entity_address) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">City <span class="text-red-500">*</span></label>
                        <input type="text" name="entity_city" value="{{ old('entity_city', $config?->entity_city ?? 'Dubai') }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">State / emirate</label>
                        <input type="text" name="entity_state" value="{{ old('entity_state', $config?->entity_state ?? 'Dubai') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        {{-- MLRO details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                MLRO (Money Laundering Reporting Officer)
                <span class="text-xs font-normal text-gray-400 ml-1">— appears as reporting person on every report</span>
            </h3>
            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gender <span class="text-red-500">*</span></label>
                        <select name="mlro_gender" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="M" {{ old('mlro_gender', $config?->mlro_gender) === 'M' ? 'selected' : '' }}>Male</option>
                            <option value="F" {{ old('mlro_gender', $config?->mlro_gender) === 'F' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">First name <span class="text-red-500">*</span></label>
                        <input type="text" name="mlro_first_name" value="{{ old('mlro_first_name', $config?->mlro_first_name) }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Last name <span class="text-red-500">*</span></label>
                        <input type="text" name="mlro_last_name" value="{{ old('mlro_last_name', $config?->mlro_last_name) }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Passport / Emirates ID (SSN) <span class="text-red-500">*</span></label>
                        <input type="text" name="mlro_ssn" value="{{ old('mlro_ssn', $config?->mlro_ssn) }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ID number <span class="text-red-500">*</span></label>
                        <input type="text" name="mlro_id_number" value="{{ old('mlro_id_number', $config?->mlro_id_number) }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nationality (3-letter code) <span class="text-red-500">*</span></label>
                        <input type="text" name="mlro_nationality" value="{{ old('mlro_nationality', $config?->mlro_nationality) }}"
                               required maxlength="3" placeholder="ARE / GBR / IND"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="mlro_email" value="{{ old('mlro_email', $config?->mlro_email) }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Occupation / designation <span class="text-red-500">*</span></label>
                    <input type="text" name="mlro_occupation" value="{{ old('mlro_occupation', $config?->mlro_occupation ?? 'MLRO') }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('tenant.goaml', $tenant->slug) }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save configuration
            </button>
        </div>
    </form>
</div>

@endsection
