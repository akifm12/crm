@extends('layouts.tenant')

@section('title', 'New Report — goAML')
@section('page-title', 'File goAML Report')
@section('page-subtitle', $client ? 'Pre-filled from ' . $client->displayName() : 'Select a client and complete the form')

@section('content')

@php
$isCorporate = $client && $client->client_type !== 'individual';
$sig         = $client?->signatories?->first();
$sh          = $client?->shareholders?->first();
$director    = $sig ?? $sh;

// Pre-fill values
$name                     = old('name', $isCorporate ? $client?->company_name : $client?->full_name);
$commercial_name          = old('commercial_name', $isCorporate ? $client?->company_name : $client?->full_name);
$incorporation_number     = old('incorporation_number', $client?->trade_license_no ?? $client?->passport_number);
$incorporation_country    = old('incorporation_country_code', $client?->country_of_incorporation ?? $client?->nationality ?? '');
$e_tph_number             = old('e_tph_number', $client?->phone ?? '');
$dir_first                = old('first_name', $director ? explode(' ', $director->full_name ?? $director->name ?? '', 2)[0] : ($client?->full_name ? explode(' ', $client->full_name, 2)[0] : ''));
$dir_last                 = old('last_name', $director ? (explode(' ', $director->full_name ?? $director->name ?? '', 2)[1] ?? '') : ($client?->full_name ? (explode(' ', $client->full_name, 2)[1] ?? '') : ''));
$dir_dob = old('birthdate',
    $sig?->dob?->format('Y-m-d')
    ?? $sh?->dob?->format('Y-m-d')
    ?? $client?->dob?->format('Y-m-d')
    ?? ''
);
$dir_passport             = old('passport_number', $director?->passport_number ?? $client?->passport_number ?? '');
$dir_passport_country     = old('passport_country', $director?->nationality ?? $client?->nationality ?? '');
$dir_id                   = old('id_number', $director?->passport_number ?? $client?->passport_number ?? '');
$dir_nationality          = old('nationality1', $director?->nationality ?? $client?->nationality ?? '');
$dir_residence            = old('residence', $director?->nationality ?? $client?->nationality ?? '');
$dir_phone                = old('d_tph_number', $client?->phone ?? '');
@endphp

<form method="POST" action="{{ route('tenant.goaml.store', $tenant->slug) }}" novalidate>
@csrf

@if($errors->any())
<div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
    <p class="text-sm font-semibold text-red-700 mb-2">Please fix the following errors:</p>
    <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Client picker (if not pre-loaded) --}}
@if(!$client)
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Select client</h3>
    <select name="client_select" id="client_select"
            onchange="if(this.value) window.location.href='{{ route('tenant.goaml.create', $tenant->slug) }}?client='+this.value"
            class="w-full max-w-sm px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        <option value="">— Select a client to pre-fill —</option>
        @foreach($clients as $c)
        <option value="{{ $c->id }}">{{ $c->client_type === 'individual' ? $c->full_name : $c->company_name }}</option>
        @endforeach
    </select>
</div>
@else
<input type="hidden" name="bullion_client_id" value="{{ $client->id }}">
@endif

{{-- Report type --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Report type & invoice</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ reportType: '{{ old('report_type','DPMSR') }}' }">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-2">Report type <span class="text-red-500">*</span></label>
            <input type="hidden" name="report_type" :value="reportType">
            <div class="flex gap-3">
                @foreach(['DPMSR'=>['bg-blue-600','Designated Payment > AED 55k'],'STR'=>['bg-red-600','Suspicious Transaction'],'SAR'=>['bg-orange-600','Suspicious Activity']] as $type=>[$color,$desc])
                <div @click="reportType='{{ $type }}'"
                     :class="reportType==='{{ $type }}' ? '{{ $color }} text-white border-transparent' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'"
                     class="flex-1 border rounded-xl p-3 cursor-pointer text-center transition">
                    <p class="text-sm font-bold">{{ $type }}</p>
                    <p class="text-xs mt-0.5 opacity-80">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Invoice / reference number <span class="text-red-500">*</span></label>
            <input type="text" name="entity_reference" value="{{ old('entity_reference') }}" required
                   placeholder="e.g. INV-2026-0042"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

{{-- Missing data alert --}}
@php
$missing = [];
if (!$name)                   $missing[] = 'Company name';
if (!$incorporation_number)   $missing[] = 'Trade licence number';
if (!$e_tph_number)           $missing[] = 'Company phone';
if (!$dir_first)              $missing[] = 'Director first name';
if (!$dir_last)               $missing[] = 'Director last name';
if (!$dir_dob)                $missing[] = 'Director date of birth';
if (!$dir_passport)           $missing[] = 'Director passport number';
if (!$dir_nationality)        $missing[] = 'Director nationality';
@endphp

@if(count($missing) > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-start gap-3">
    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="text-sm font-semibold text-amber-800">{{ count($missing) }} field(s) missing from client record</p>
        <p class="text-xs text-amber-700 mt-1">{{ implode(', ', $missing) }}</p>
        <a href="{{ route('tenant.clients.edit', [$tenant->slug, $client->id]) }}"
           class="text-xs text-amber-800 font-semibold underline mt-1 inline-block">Update client profile →</a>
    </div>
</div>
@else
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5 flex items-center gap-3">
    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-sm text-green-700">All client and director data pre-filled from <strong>{{ $client->displayName() }}</strong></p>
</div>
@endif

{{-- Hidden fields — entity / counterparty --}}
<input type="hidden" name="name" value="{{ $name }}">
<input type="hidden" name="commercial_name" value="{{ $commercial_name }}">
<input type="hidden" name="incorporation_number" value="{{ $incorporation_number }}">
<input type="hidden" name="incorporation_country_code" value="{{ $incorporation_country }}">
<input type="hidden" name="e_tph_number" value="{{ $e_tph_number }}">

{{-- Hidden fields — director --}}
<input type="hidden" name="first_name" value="{{ $dir_first }}">
<input type="hidden" name="last_name" value="{{ $dir_last }}">
<input type="hidden" name="birthdate" value="{{ $dir_dob }}">
<input type="hidden" name="passport_number" value="{{ $dir_passport }}">
<input type="hidden" name="passport_country" value="{{ $dir_passport_country }}">
<input type="hidden" name="id_number" value="{{ $dir_id }}">
<input type="hidden" name="nationality1" value="{{ $dir_nationality }}">
<input type="hidden" name="residence" value="{{ $dir_residence }}">
<input type="hidden" name="d_tph_number" value="{{ $dir_phone }}">

{{-- Transaction / goods details --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Transaction details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Currency <span class="text-red-500">*</span></label>
            <select name="currency_code" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="AED" {{ old('currency_code','AED') === 'AED' ? 'selected' : '' }}>AED — UAE Dirham</option>
                <option value="USD" {{ old('currency_code') === 'USD' ? 'selected' : '' }}>USD — US Dollar</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Transaction date <span class="text-red-500">*</span></label>
            <input type="date" name="registration_date" value="{{ old('registration_date', date('Y-m-d')) }}"
                   required max="{{ date('Y-m-d') }}"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Total trade / estimated value <span class="text-red-500">*</span></label>
            <input type="number" name="estimated_value" value="{{ old('estimated_value') }}" required min="0" step="0.01"
                   placeholder="Total value of the relationship / transaction series"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Disposed / transaction value <span class="text-red-500">*</span></label>
            <input type="number" name="disposed_value" value="{{ old('disposed_value') }}" required min="0" step="0.01"
                   placeholder="Value of this specific transaction"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Weight <span class="text-red-500">*</span></label>
            <input type="number" name="size" value="{{ old('size') }}" required min="0" step="0.001"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Unit of measure <span class="text-red-500">*</span></label>
            <input type="text" name="size_uom" value="{{ old('size_uom','Grams') }}" required
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
    <div class="mt-4">
        <label class="block text-xs font-medium text-gray-600 mb-1">Comments / additional notes</label>
        <textarea name="comments" rows="3"
                  class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                  placeholder="Any additional context or observations…">{{ old('comments') }}</textarea>
    </div>
</div>

{{-- Actions --}}
<div class="flex items-center justify-between">
    <a href="{{ route('tenant.goaml', $tenant->slug) }}"
       class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Cancel
    </a>
    <button type="submit"
            class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Generate XML report
    </button>
</div>

</form>

@endsection