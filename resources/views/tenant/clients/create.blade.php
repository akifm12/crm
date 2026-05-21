@extends('layouts.tenant')
@section('title', 'Add Client — ' . $tenant->name)
@section('page-title', 'Add new client')
@section('page-subtitle', 'Complete all steps to onboard a new ' . ($sector['label'] ?? 'compliance') . ' client')

@section('content')

{{-- ── AI DOCUMENT SCAN (own Alpine scope, outside the form) ───────────────── --}}
<div x-data="docScanner()"
     data-url="{{ route('tenant.clients.scan', $tenant->slug) }}"
     class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-5 mb-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-blue-900">Auto-fill from document <span class="text-xs font-normal text-blue-500">(optional)</span></p>
                <p class="text-xs text-blue-600">Upload a passport, Emirates ID or trade licence — AI will pre-fill the form below</p>
            </div>
        </div>
        <button type="button" @click="open = !open" class="text-blue-400 hover:text-blue-600 transition">
            <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak class="mt-4 border-t border-blue-100 pt-4">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-blue-800 mb-1">
                    Select document <span class="font-normal text-blue-500">(passport, Emirates ID, or trade licence)</span>
                </label>
                <input type="file"
                       multiple
                       @change="filesSelected($event)"
                       accept="image/jpeg,image/jpg,image/png,application/pdf"
                       class="block w-full text-sm text-blue-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                <p class="text-xs text-blue-400 mt-1">JPG, PNG or PDF · Max 10 MB · Select multiple files at once</p>
            </div>
            <button type="button" @click="scan()"
                    :disabled="!files.length || scanning"
                    class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2 flex-shrink-0 transition">
                <svg x-show="!scanning" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <svg x-show="scanning" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span x-text="scanning ? 'Scanning...' : 'Scan & Fill'"></span>
            </button>
        </div>

        <div x-show="progress" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <p class="text-xs text-blue-700 font-medium" x-text="progress"></p>
        </div>

        <div x-show="filled.length > 0" class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg flex items-start gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-xs font-semibold text-green-800"><span x-text="filled.length"></span> fields pre-filled — review and correct before saving</p>
                <p class="text-xs text-green-700 mt-0.5" x-text="filled.join(' · ')"></p>
            </div>
        </div>

        <div x-show="error" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-xs text-red-700" x-text="error"></p>
        </div>
    </div>
</div>

{{-- ── DUPLICATE CLIENT ALERT ───────────────────────────────────────────────── --}}
@if(session('duplicate_client_id'))
<div class="mb-5 flex items-start gap-4 p-4 bg-amber-50 border border-amber-300 rounded-xl">
    <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <div class="flex-1">
        <p class="text-sm font-bold text-amber-800">This client already exists</p>
        <p class="text-sm text-amber-700 mt-0.5">
            <strong>{{ session('duplicate_client_name') }}</strong> is already registered.
            To record a new transaction, open their profile and use the <strong>Transactions</strong> tab.
        </p>
        <a href="{{ route('tenant.clients.show', [$tenant->slug, session('duplicate_client_id')]) }}?tab=transactions"
           class="inline-flex items-center gap-2 mt-3 px-4 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
            Go to {{ session('duplicate_client_name') }}'s profile →
        </a>
    </div>
</div>
@endif

<form method="POST" action="{{ route('tenant.clients.store', $tenant->slug) }}"
      enctype="multipart/form-data"
      novalidate
      x-data="clientForm()">
@csrf
<input type="hidden" name="client_type" :value="clientType">

{{-- ── TYPE SELECTOR ────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <p class="text-sm font-semibold text-gray-700 mb-3">Client type</p>
    <div class="flex flex-wrap gap-3">
        @foreach($sector['client_types'] as $typeKey => $typeLabel)
        <button type="button" @click="setType('{{ $typeKey }}')"
                :class="clientType==='{{ $typeKey }}' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400'"
                class="px-5 py-2.5 rounded-lg border text-sm font-semibold transition-all">
            {{ $typeLabel }}
        </button>
        @endforeach
    </div>
    <p class="text-xs text-gray-400 mt-3">
        <span x-show="clientType==='individual'">6 steps — profile · AML/CDD · documents · declarations · screening · review</span>
        <span x-show="clientType!=='individual'">8 steps — profile · signatories · shareholders · AML/CDD · documents · declarations · screening · review</span>
    </p>
</div>

{{-- ── STEP INDICATORS ──────────────────────────────────────────────────────── --}}
<div class="mb-5">
    {{-- Corporate --}}
    <div x-show="clientType!=='individual'" class="flex items-center">
        @php $cs = [1=>'Profile',2=>'Signatories',3=>'Shareholders',4=>'AML / CDD',5=>'Documents',6=>'Declarations',7=>'Screening',8=>'Review']; @endphp
        @foreach($cs as $n => $label)
        <div class="flex items-center {{ $n < count($cs) ? 'flex-1' : '' }}">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                     :class="stepErrors[{{$n}}] ? 'bg-red-500 text-white' : (step==={{$n}} ? 'bg-blue-600 text-white' : (step>{{$n}} ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400'))">
                    <template x-if="stepErrors[{{$n}}]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></template>
                    <template x-if="!stepErrors[{{$n}}] && step > {{$n}}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></template>
                    <template x-if="!stepErrors[{{$n}}] && step <= {{$n}}"><span>{{$n}}</span></template>
                </div>
                <span class="text-xs mt-1 font-medium hidden sm:block whitespace-nowrap"
                      :class="stepErrors[{{$n}}] ? 'text-red-600' : (step==={{$n}} ? 'text-blue-600' : (step>{{$n}} ? 'text-green-600' : 'text-gray-400'))">{{ $label }}</span>
            </div>
            @if($n < count($cs))
            <div class="flex-1 h-px mx-1 mt-0 sm:-mt-4" :class="step > {{$n}} ? 'bg-green-400' : 'bg-gray-200'"></div>
            @endif
        </div>
        @endforeach
    </div>
    {{-- Individual --}}
    <div x-show="clientType==='individual'" class="flex items-center">
        @php $is = [1=>'Profile',2=>'AML / CDD',3=>'Documents',4=>'Declarations',5=>'Screening',6=>'Review']; @endphp
        @foreach($is as $n => $label)
        <div class="flex items-center {{ $n < count($is) ? 'flex-1' : '' }}">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all"
                     :class="indStepErrors[{{$n}}] ? 'bg-red-500 text-white' : (indStep==={{$n}} ? 'bg-blue-600 text-white' : (indStep>{{$n}} ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400'))">
                    <template x-if="indStepErrors[{{$n}}]"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></template>
                    <template x-if="!indStepErrors[{{$n}}] && indStep > {{$n}}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></template>
                    <template x-if="!indStepErrors[{{$n}}] && indStep <= {{$n}}"><span>{{$n}}</span></template>
                </div>
                <span class="text-xs mt-1 font-medium hidden sm:block whitespace-nowrap"
                      :class="indStepErrors[{{$n}}] ? 'text-red-600' : (indStep==={{$n}} ? 'text-blue-600' : (indStep>{{$n}} ? 'text-green-600' : 'text-gray-400'))">{{ $label }}</span>
            </div>
            @if($n < count($is))
            <div class="flex-1 h-px mx-1 mt-0 sm:-mt-4" :class="indStep > {{$n}} ? 'bg-green-400' : 'bg-gray-200'"></div>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ── FORM CARD ────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

{{-- ══ CORP 1 — Company Profile ══════════════════════════════════════════════ --}}
<div x-show="clientType!=='individual' && step===1" x-cloak data-corp-step="1">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Company profile</h2>
        <p class="text-xs text-gray-400 mt-0.5">Registration and contact details</p>
    </div>
    <div class="p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Company name with live duplicate search --}}
            <div x-data="clientSearch('{{ route('tenant.clients.search', $tenant->slug) }}', '{{ old('company_name') }}')" class="relative">
                <label class="block text-xs font-medium text-gray-600 mb-1">Company name <span class="text-red-500">*</span></label>
                <input type="text" name="company_name" required
                       x-model="query"
                       @input="onInput()"
                       @keydown.escape="close()"
                       @blur="delayClose()"
                       autocomplete="off"
                       class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white {{ $errors->has('company_name') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                @error('company_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                {{-- Dropdown --}}
                <div x-show="suggestions.length > 0" x-cloak
                     class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                    <template x-for="c in suggestions" :key="c.id">
                        <button type="button" @mousedown.prevent="select(c)"
                                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-blue-50 text-left border-b border-gray-100 last:border-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate" x-text="c.name"></p>
                                <p class="text-xs text-gray-400" x-text="c.identifier || c.type"></p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0"
                                  :class="c.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                  x-text="c.status"></span>
                        </button>
                    </template>
                </div>
                {{-- Match card --}}
                <div x-show="selected" x-cloak class="mt-2 p-3 bg-amber-50 border border-amber-300 rounded-xl">
                    <p class="text-xs font-bold text-amber-800 mb-1">⚠ Existing client found</p>
                    <p class="text-sm font-semibold text-gray-800" x-text="selected?.name"></p>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="(selected?.identifier || '') + (selected?.added ? ' · Added ' + selected.added : '')"></p>
                    <div class="flex gap-2 mt-2">
                        <a :href="'{{ url('/'.$tenant->slug.'/clients/') }}/' + selected?.id + '?tab=transactions'"
                           class="flex-1 text-center py-1.5 text-xs font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition">
                            Add transaction →
                        </a>
                        <button type="button" @click="dismiss()"
                                class="flex-1 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            Different company
                        </button>
                    </div>
                </div>
            </div>
            @include('tenant.clients._field', ['name'=>'trade_license_no','label'=>'Trade licence number','required'=>true])
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @include('tenant.clients._field', ['name'=>'trade_license_issue','label'=>'Licence issue date','type'=>'date'])
            @include('tenant.clients._field', ['name'=>'trade_license_expiry','label'=>'Licence expiry date','required'=>true,'type'=>'date'])
            @include('tenant.clients._field', ['name'=>'trn_number','label'=>'TRN number'])
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @include('tenant.clients._select', ['name'=>'legal_form','label'=>'Legal form','options'=>['LLC'=>'LLC','Sole Establishment'=>'Sole Establishment','Free Zone LLC'=>'Free Zone LLC','Free Zone Establishment'=>'Free Zone Establishment','Public Joint Stock'=>'Public Joint Stock','Branch'=>'Branch of Foreign Company','Other'=>'Other']])
            @include('tenant.clients._field', ['name'=>'ejari_number','label'=>'Ejari number'])
                @include('tenant.clients._field', ['name'=>'ejari_expiry','label'=>'Ejari expiry date','type'=>'date'])
            @include('tenant.clients._country', ['name'=>'country_of_incorporation','label'=>'Country of incorporation','required'=>true,'value'=>'AE'])
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @include('tenant.clients._field', ['name'=>'business_activity','label'=>'Business activity','required'=>true])
            @include('tenant.clients._field', ['name'=>'email','label'=>'Company email','type'=>'email'])
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @include('tenant.clients._field', ['name'=>'phone','label'=>'Phone'])
            @include('tenant.clients._field', ['name'=>'website','label'=>'Website'])
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @include('tenant.clients._textarea', ['name'=>'registered_address','label'=>'Registered address'])
            @include('tenant.clients._textarea', ['name'=>'operating_address','label'=>'Operating address (if different)'])
        </div>
        @include('tenant.clients._textarea', ['name'=>'nature_of_business','label'=>'Nature of business','required'=>true])
    </div>
</div>

{{-- ══ CORP 2 — Signatories ═══════════════════════════════════════════════════ --}}
<div x-show="clientType!=='individual' && step===2" x-cloak data-corp-step="2">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Authorised signatories</h2>
        <p class="text-xs text-gray-400 mt-0.5">All persons authorised to sign on behalf of the company</p>
    </div>
    <div class="p-6">
        <template x-for="(sig, i) in signatories" :key="i">
        <div class="border border-gray-200 rounded-xl p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500" x-text="'Signatory ' + (i+1)"></span>
                <button type="button" @click="removeSig(i)" x-show="signatories.length > 1"
                        class="text-red-400 hover:text-red-600 text-xs flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Full name <span class="text-red-500">*</span></label>
                    <input type="text" :name="'signatories['+i+'][full_name]'" x-model="sig.full_name" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Position</label>
                    <input type="text" :name="'signatories['+i+'][position]'" x-model="sig.position" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                    <select :name="'signatories['+i+'][nationality]'" x-model="sig.nationality" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"><option value="">— Select —</option>
                        @php if(!isset($countryOpts)) $countryOpts = \App\Models\Country::orderBy('country_name')->get(['country_code','country_name']); @endphp
                        @foreach($countryOpts as $_c)
                        <option value="{{ $_c->country_code }}">{{ $_c->country_name }}</option>
                        @endforeach</select></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                    <input type="date" :name="'signatories['+i+'][dob]'" x-model="sig.dob" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Passport number</label>
                    <input type="text" :name="'signatories['+i+'][passport_number]'" x-model="sig.passport_number" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Passport expiry</label>
                    <input type="date" :name="'signatories['+i+'][passport_expiry]'" x-model="sig.passport_expiry" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Emirates ID</label>
                    <input type="text" :name="'signatories['+i+'][eid_number]'" x-model="sig.eid_number" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            </div>
        </div>
        </template>
        <button type="button" @click="addSig" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add another signatory
        </button>
    </div>
</div>

{{-- ══ CORP 3 — Shareholders & UBOs ═══════════════════════════════════════════ --}}
<div x-show="clientType!=='individual' && step===3" x-cloak data-corp-step="3">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Shareholders & UBOs</h2>
        <p class="text-xs text-gray-400 mt-0.5">Ownership structure — UBO threshold is 25%+ effective ownership</p>
    </div>
    <div class="p-6 space-y-6">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Shareholders</h3>
            <template x-for="(sh, i) in shareholders" :key="i">
            <div class="border border-gray-200 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500" x-text="'Shareholder '+(i+1)"></span>
                    <button type="button" @click="removeSh(i)" x-show="shareholders.length > 1" class="text-red-400 hover:text-red-600 text-xs flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Remove
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select :name="'shareholders['+i+'][shareholder_type]'" x-model="sh.type" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="individual">Individual</option><option value="corporate">Corporate</option>
                        </select></div>
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" :name="'shareholders['+i+'][name]'" x-model="sh.name" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Nationality / country</label>
                        <select :name="'shareholders['+i+'][nationality]'" x-model="sh.nationality" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"><option value="">— Select —</option>
                        @php if(!isset($countryOpts)) $countryOpts = \App\Models\Country::orderBy('country_name')->get(['country_code','country_name']); @endphp
                        @foreach($countryOpts as $_c)
                        <option value="{{ $_c->country_code }}">{{ $_c->country_name }}</option>
                        @endforeach</select></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Ownership %</label>
                        <input type="number" step="0.01" :name="'shareholders['+i+'][ownership_percentage]'" x-model="sh.ownership_percentage" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Passport / Reg. no.</label>
                        <input type="text" :name="'shareholders['+i+'][passport_number]'" x-model="sh.passport_number" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    <div class="flex items-center gap-2 pt-4">
                        <input type="checkbox" :name="'shareholders['+i+'][is_ubo]'" value="1" x-model="sh.is_ubo" :id="'sh_ubo_'+i" class="rounded border-gray-300 text-blue-600">
                        <label :for="'sh_ubo_'+i" class="text-xs text-gray-600">Also a UBO (25%+)</label>
                    </div>
                </div>
            </div>
            </template>
            <button type="button" @click="addSh" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add shareholder
            </button>
        </div>
        <div class="border-t border-gray-100 pt-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Ultimate Beneficial Owners (UBOs)</h3>
                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded-full">Natural persons · 25%+ ownership</span>
            </div>
            <template x-for="(ubo, i) in ubos" :key="i">
            <div class="border border-amber-200 bg-amber-50 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-amber-700" x-text="'UBO '+(i+1)"></span>
                    <button type="button" @click="removeUbo(i)" x-show="ubos.length > 1" class="text-red-400 hover:text-red-600 text-xs flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Remove
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Full name <span class="text-red-500">*</span></label>
                        <input type="text" :name="'ubos['+i+'][full_name]'" x-model="ubo.full_name" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                        <select :name="'ubos['+i+'][nationality]'" x-model="ubo.nationality" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"><option value="">— Select —</option>
                        @php if(!isset($countryOpts)) $countryOpts = \App\Models\Country::orderBy('country_name')->get(['country_code','country_name']); @endphp
                        @foreach($countryOpts as $_c)
                        <option value="{{ $_c->country_code }}">{{ $_c->country_name }}</option>
                        @endforeach</select></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                        <input type="date" :name="'ubos['+i+'][dob]'" x-model="ubo.dob" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Passport number</label>
                        <input type="text" :name="'ubos['+i+'][passport_number]'" x-model="ubo.passport_number" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Ownership %</label>
                        <input type="number" step="0.01" :name="'ubos['+i+'][ownership_percentage]'" x-model="ubo.ownership_percentage" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Country of residence</label>
                        <input type="text" :name="'ubos['+i+'][country_of_residence]'" x-model="ubo.country_of_residence" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"></div>
                    <div class="flex items-center gap-2 pt-4">
                        <input type="checkbox" :name="'ubos['+i+'][pep_status]'" value="1" x-model="ubo.pep_status" :id="'ubo_pep_'+i" class="rounded border-gray-300 text-blue-600">
                        <label :for="'ubo_pep_'+i" class="text-xs text-gray-600">This UBO is a PEP</label>
                    </div>
                </div>
            </div>
            </template>
            <button type="button" @click="addUbo" class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add UBO
            </button>
        </div>
    </div>
</div>

{{-- ══ AML/CDD — shared (Corp 4, Ind 2) ══════════════════════════════════════ --}}
<div x-show="(clientType!=='individual' && step===4) || (clientType==='individual' && indStep===2)" x-cloak data-corp-step="4" data-ind-step="2">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">AML / CDD details</h2>
        <p class="text-xs text-gray-400 mt-0.5">Risk profile, source of funds and transaction information</p>
    </div>
    <div class="p-6 space-y-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Source of funds <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach($sector['source_of_funds'] as $v=>$l)
                <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                    <input type="checkbox" name="source_of_funds[]" value="{{ $v }}" class="rounded border-gray-300 text-blue-600"> {{ $l }}
                </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Source of wealth</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach(['uae_business'=>'UAE business operations','foreign_business'=>'Foreign business operations','salary'=>'Salary / employment','inheritance'=>'Inheritance','property_sale'=>'Property sale','investment'=>'Investment returns','professional'=>'Professional income','other'=>'Other'] as $v=>$l)
                <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                    <input type="checkbox" name="source_of_wealth[]" value="{{ $v }}" class="rounded border-gray-300 text-blue-600"> {{ $l }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @include('tenant.clients._field', ['name'=>'purpose_of_relationship','label'=>'Purpose of business relationship','required'=>true])
            <template x-if="clientType !== 'individual'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @include('tenant.clients._field', ['name'=>'expected_monthly_volume','label'=>'Expected monthly volume (AED)','type'=>'number'])
                    @include('tenant.clients._select', ['name'=>'expected_monthly_frequency','label'=>'Transaction frequency','options'=>['1-5'=>'1–5 per month','6-15'=>'6–15 per month','16-30'=>'16–30 per month','30+'=>'More than 30 per month']])
                </div>
            </template>
            @include('tenant.clients._select', ['name'=>'cdd_type','label'=>'CDD type','options'=>['standard'=>'Standard CDD','enhanced'=>'Enhanced Due Diligence (EDD)'],'default'=>'standard'])
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Customer risk rating</label>
            <div class="flex gap-3">
                <label class="flex-1 border border-green-200 bg-green-50 rounded-xl p-4 cursor-pointer text-center hover:opacity-80 transition">
                    <input type="radio" name="risk_rating" value="low" class="sr-only" checked><span class="text-sm font-semibold text-green-700">Low risk</span>
                </label>
                <label class="flex-1 border border-amber-200 bg-amber-50 rounded-xl p-4 cursor-pointer text-center hover:opacity-80 transition">
                    <input type="radio" name="risk_rating" value="medium" class="sr-only"><span class="text-sm font-semibold text-amber-700">Medium risk</span>
                </label>
                <label class="flex-1 border border-red-200 bg-red-50 rounded-xl p-4 cursor-pointer text-center hover:opacity-80 transition">
                    <input type="radio" name="risk_rating" value="high" class="sr-only"><span class="text-sm font-semibold text-red-700">High risk</span>
                </label>
            </div>
        </div>
        @include('tenant.clients._field', ['name'=>'next_review_date','label'=>'Next KYC review date','type'=>'date','hint'=>'High risk: 1 year · Medium: 2 years · Low: 3 years'])

        {{-- Sector-specific extra fields --}}
        @if(!empty($sector['extra_fields']))
        <div class="border-t border-gray-100 pt-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ $sector['label'] }} — additional details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($sector['extra_fields'] as $fieldKey => $fieldConfig)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $fieldConfig['label'] }}</label>
                    @if($fieldConfig['type'] === 'select')
                    <select name="extra_data[{{ $fieldKey }}]"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">— Select —</option>
                        @foreach($fieldConfig['options'] as $optVal => $optLabel)
                        <option value="{{ $optVal }}">{{ $optLabel }}</option>
                        @endforeach
                    </select>
                    @elseif($fieldConfig['type'] === 'number')
                    <input type="number" name="extra_data[{{ $fieldKey }}]"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @else
                    <input type="text" name="extra_data[{{ $fieldKey }}]"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ══ Declarations — shared (Corp 5, Ind 3) ══════════════════════════════════ --}}
<div x-show="(clientType!=='individual' && step===6) || (clientType==='individual' && indStep===4)" x-cloak data-corp-step="6" data-ind-step="4">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Declarations</h2>
        <p class="text-xs text-gray-400 mt-0.5">Confirm each declaration has been received and acknowledged</p>
    </div>
    <div class="p-6 space-y-3">
        @php
        $allDeclLabels = [
            'pep'            => ['PEP declaration',                    'Client confirmed PEP status and signed the declaration.'],
            'supply_chain'   => ['Gold supply chain sourcing',         'Client confirmed legitimate sourcing.'],
            'cahra'          => ['No CAHRA imports',                   'Client confirmed no gold from conflict-affected areas.'],
            'source_of_funds'=> ['Source of funds & wealth',           'Client confirmed and declared source of funds.'],
            'sanctions'      => ['Sanctions compliance',               'Client confirmed no applicable sanctions exposure.'],
            'ubo'            => ['Beneficial ownership',               'Client disclosed all UBOs accurately.'],
            'property'       => ['Property transaction declaration',   'Client confirmed property transaction details.'],
            'beneficial_ownership' => ['Beneficial ownership structure','Client confirmed beneficial ownership structure.'],
            'client_funds'   => ['Client funds handling',              'Client confirmed client monies arrangements.'],
        ];
        $corpDecls = $sector['declarations_corporate'] ?? ['pep','source_of_funds','sanctions','ubo'];
        $indDecls  = $sector['declarations_individual'] ?? ['pep','source_of_funds','sanctions'];
        // Render all unique declarations — Alpine hides irrelevant ones per client type
        $allDecls = array_unique(array_merge($corpDecls, $indDecls));
        @endphp
        @foreach($allDecls as $dt)
        @php
            $f = 'decl_' . $dt;
            [$t,$d] = $allDeclLabels[$dt] ?? [ucfirst($dt), ''];
            $inCorp = in_array($dt, $corpDecls);
            $inInd  = in_array($dt, $indDecls);
            // Build Alpine x-show expression
            if ($inCorp && $inInd) $xshow = 'true';
            elseif ($inCorp)       $xshow = "clientType !== 'individual'";
            else                   $xshow = "clientType === 'individual'";
        @endphp
        <label class="flex items-start gap-4 p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition"
               x-show="{{ $xshow }}">
            <input type="checkbox" name="{{ $f }}" value="1" class="mt-0.5 rounded border-gray-300 text-blue-600 w-5 h-5 flex-shrink-0">
            <div><p class="text-sm font-semibold text-gray-800">{{ $t }}</p><p class="text-xs text-gray-500 mt-0.5">{{ $d }}</p></div>
        </label>
        @endforeach
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl mt-2">
            <p class="text-sm font-semibold text-blue-800">Master declaration — physical signature required</p>
            <p class="text-xs text-blue-600 mt-0.5">Upload the signed copy in the Documents step or after onboarding.</p>
        </div>
    </div>
</div>

{{-- ══ IND 1 — Individual Profile ═════════════════════════════════════════════ --}}
<div x-show="clientType==='individual' && indStep===1" x-cloak data-ind-step="1">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Personal profile</h2>
        <p class="text-xs text-gray-400 mt-0.5">Individual client details as per official documents</p>
    </div>
    <div class="p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Full name with live duplicate search --}}
            <div x-data="clientSearch('{{ route('tenant.clients.search', $tenant->slug) }}', '{{ old('full_name') }}')" class="relative">
                <label class="block text-xs font-medium text-gray-600 mb-1">Full name (as per ID document) <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" required
                       x-model="query"
                       @input="onInput()"
                       @keydown.escape="close()"
                       @blur="delayClose()"
                       autocomplete="off"
                       class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white {{ $errors->has('full_name') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                @error('full_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                {{-- Dropdown --}}
                <div x-show="suggestions.length > 0" x-cloak
                     class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                    <template x-for="c in suggestions" :key="c.id">
                        <button type="button" @mousedown.prevent="select(c)"
                                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-blue-50 text-left border-b border-gray-100 last:border-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate" x-text="c.name"></p>
                                <p class="text-xs text-gray-400" x-text="(c.identifier || '') + (c.dob ? ' · DOB ' + c.dob : '')"></p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0"
                                  :class="c.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                  x-text="c.status"></span>
                        </button>
                    </template>
                </div>
                {{-- Match card --}}
                <div x-show="selected" x-cloak class="mt-2 p-3 bg-amber-50 border border-amber-300 rounded-xl">
                    <p class="text-xs font-bold text-amber-800 mb-1">⚠ Existing client found</p>
                    <p class="text-sm font-semibold text-gray-800" x-text="selected?.name"></p>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="(selected?.identifier || '') + (selected?.dob ? ' · DOB ' + selected.dob : '') + (selected?.added ? ' · Added ' + selected.added : '')"></p>
                    <div class="flex gap-2 mt-2">
                        <a :href="'{{ url('/'.$tenant->slug.'/clients/') }}/' + selected?.id + '?tab=transactions'"
                           class="flex-1 text-center py-1.5 text-xs font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition">
                            Add transaction →
                        </a>
                        <button type="button" @click="dismiss()"
                                class="flex-1 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            Different person
                        </button>
                    </div>
                </div>
            </div>
            @include('tenant.clients._field', ['name'=>'name_arabic','label'=>'Name in Arabic'])
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @include('tenant.clients._country', ['name'=>'nationality','label'=>'Nationality','required'=>true])
            @include('tenant.clients._field', ['name'=>'dob','label'=>'Date of birth','required'=>true,'type'=>'date'])
            @include('tenant.clients._field', ['name'=>'email','label'=>'Email address','type'=>'email'])
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @include('tenant.clients._field', ['name'=>'passport_number','label'=>'Passport number'])
            @include('tenant.clients._field', ['name'=>'passport_expiry','label'=>'Passport expiry','type'=>'date'])
            <div class="md:col-span-3">
                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">⚠ At least one of Passport or Emirates ID is required for identity verification.</p>
            </div>
            @include('tenant.clients._field', ['name'=>'eid_number','label'=>'Emirates ID number'])
            @include('tenant.clients._field', ['name'=>'eid_expiry','label'=>'Emirates ID expiry','type'=>'date'])            @include('tenant.clients._field', ['name'=>'phone','label'=>'Phone number'])
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @include('tenant.clients._field', ['name'=>'occupation','label'=>'Occupation / profession'])
            @include('tenant.clients._field', ['name'=>'employer_name','label'=>'Employer / business name'])
        </div>
        <div class="border border-gray-200 rounded-xl p-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="pep_status" value="1" class="rounded border-gray-300 text-blue-600 w-5 h-5">
                <div>
                    <p class="text-sm font-semibold text-gray-800">This individual is a Politically Exposed Person (PEP)</p>
                    <p class="text-xs text-gray-500 mt-0.5">Held a prominent public function within the last 12 months</p>
                </div>
            </label>
            <div class="mt-3">
                @include('tenant.clients._textarea', ['name'=>'pep_details','label'=>'PEP details (position, country, dates)'])
            </div>
        </div>
    </div>
</div>

{{-- ══ Documents — shared (Corp 6, Ind 4) ════════════════════════════════════ --}}
<div x-show="(clientType!=='individual' && step===5) || (clientType==='individual' && indStep===3)" x-cloak data-corp-step="5" data-ind-step="3">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Document upload</h2>
        <p class="text-xs text-gray-400 mt-0.5">Upload all available documents now. You can add more from the client profile later.</p>
    </div>
    <div class="p-6 space-y-3">

        {{-- Corporate document list --}}
        <div x-show="clientType!=='individual'">
            @php
            $corpDocs = [
                ['type'=>'trade_license',       'label'=>'Trade licence',                    'required'=>true,  'has_expiry'=>true],
                ['type'=>'moa',                 'label'=>'Memorandum of Association (MoA)',   'required'=>true,  'has_expiry'=>false],
                ['type'=>'certificate_incorp',  'label'=>'Certificate of incorporation',      'required'=>true,  'has_expiry'=>false],
                ['type'=>'signatory_passport',  'label'=>'Authorised signatory passport',     'required'=>true,  'has_expiry'=>true],
                ['type'=>'signatory_eid',       'label'=>'Authorised signatory Emirates ID',  'required'=>false, 'has_expiry'=>true],
                ['type'=>'shareholder_passport','label'=>'Shareholder passport(s)',            'required'=>true,  'has_expiry'=>true],
                ['type'=>'ubo_passport',        'label'=>'UBO passport(s)',                   'required'=>false, 'has_expiry'=>true],
                ['type'=>'source_of_funds',     'label'=>'Source of funds evidence',          'required'=>false, 'has_expiry'=>false],
                ['type'=>'bank_statement',      'label'=>'Bank statement (3 months)',          'required'=>false, 'has_expiry'=>false],
                ['type'=>'other',               'label'=>'Other / additional document',        'required'=>false, 'has_expiry'=>false],
            ];
            @endphp
            @foreach($corpDocs as $doc)
            @include('tenant.clients._doc_row', $doc)
            @endforeach
        </div>

        {{-- Individual document list --}}
        <div x-show="clientType==='individual'">
            @php
            $indDocs = [
                ['type'=>'passport',         'label'=>'Passport',                 'required'=>true,  'has_expiry'=>true],
                ['type'=>'eid',              'label'=>'Emirates ID',              'required'=>false, 'has_expiry'=>true],
                ['type'=>'proof_of_address', 'label'=>'Proof of address',         'required'=>false, 'has_expiry'=>false],
                ['type'=>'source_of_funds',  'label'=>'Source of funds evidence', 'required'=>false, 'has_expiry'=>false],
                ['type'=>'bank_statement',   'label'=>'Bank statement (3 months)', 'required'=>false, 'has_expiry'=>false],
                ['type'=>'other',            'label'=>'Other / additional',        'required'=>false, 'has_expiry'=>false],
            ];
            @endphp
            @foreach($indDocs as $doc)
            @include('tenant.clients._doc_row', $doc)
            @endforeach
        </div>

        <p class="text-xs text-gray-400 pt-2">Accepted formats: PDF, JPG, PNG, DOCX — max 10MB per file</p>
    </div>
</div>

{{-- ══ Screening — shared (Corp 7, Ind 5) ═══════════════════════════════════ --}}
<div x-show="(clientType!=='individual' && step===7) || (clientType==='individual' && indStep===5)" x-cloak data-corp-step="7" data-ind-step="5">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">AML Screening</h2>
        <p class="text-xs text-gray-400 mt-0.5">Screen the client and all associated persons against sanctions, PEP and adverse media lists</p>
    </div>
    <div class="p-6 space-y-4" x-data="{ screening: false, screeningDone: false, screeningResults: [], hasMatch: false }">

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-blue-700">Screening checks names against UAE, UN, OFAC, EU and UK sanctions lists, PEP databases, and adverse media. Results are saved with the client record.</p>
        </div>

        <button type="button"
            x-show="!screeningDone"
            :disabled="screening"
            @click="
                screening = true;
                const formData = new FormData(document.querySelector('form[novalidate]'));
                fetch('{{ route('tenant.clients.screen.preview', $tenant->slug) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    screeningResults = data.results || [];
                    hasMatch = screeningResults.some(r => r.result && r.result.status === 'match');
                    screeningDone = true;
                    screening = false;
                })
                .catch(() => { screening = false; alert('Screening failed. Please try again.'); })
            "
            class="w-full flex items-center justify-center gap-2 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition disabled:opacity-60">
            <template x-if="!screening">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </template>
            <template x-if="screening">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </template>
            <span x-text="screening ? 'Screening in progress...' : 'Run screening now'"></span>
        </button>

        {{-- Results --}}
        <template x-if="screeningDone">
            <div class="space-y-3">

                <div :class="hasMatch ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'"
                     class="border rounded-xl p-4 flex items-center gap-3">
                    <template x-if="hasMatch">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </template>
                    <template x-if="!hasMatch">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <div>
                        <p :class="hasMatch ? 'text-red-700' : 'text-green-700'" class="text-sm font-semibold"
                           x-text="hasMatch ? '⚠ Potential matches found — review before proceeding' : '✓ No sanctions or PEP matches found'"></p>
                        <p class="text-xs text-gray-500 mt-0.5">You may still proceed — MLRO must review any matches</p>
                    </div>
                </div>

                <template x-for="res in screeningResults" :key="res.name">
                    <div class="border border-gray-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-800" x-text="res.name"></p>
                                <p class="text-xs text-gray-400" x-text="res.role"></p>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                  :class="res.result && res.result.status === 'match' ? 'bg-red-100 text-red-700' : (res.result && res.result.status === 'clear' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500')"
                                  x-text="res.result ? (res.result.total_hits > 0 ? res.result.total_hits + ' hit(s)' : 'Clear') : 'Error'">
                            </span>
                        </div>
                        <template x-if="res.result && res.result.hits && res.result.hits.length > 0">
                            <div class="mt-2 space-y-1">
                                <template x-for="hit in res.result.hits" :key="hit.name">
                                    <div class="flex items-center gap-2 text-xs text-red-700 bg-red-50 rounded-lg px-3 py-1.5">
                                        <span x-text="hit.name"></span>
                                        <span class="text-red-400" x-text="hit.type ? '· '+hit.type : ''"></span>
                                        <span class="text-red-400" x-text="hit.matchScore ? '· '+hit.matchScore+'%' : ''"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                <button type="button" @click="screeningDone=false; screeningResults=[]"
                        class="text-xs text-blue-600 hover:underline">Re-run screening</button>
            </div>
        </template>

        <template x-if="!screeningDone">
            <p class="text-xs text-gray-400 text-center py-4">Screening not yet run — you can also skip and run from the client profile after submission.</p>
        </template>
    </div>
</div>

{{-- ══ Review — shared (Corp 8, Ind 6) ══════════════════════════════════════ --}}
<div x-show="(clientType!=='individual' && step===8) || (clientType==='individual' && indStep===6)" x-cloak>
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Review & submit</h2>
        <p class="text-xs text-gray-400 mt-0.5">Confirm everything before creating the client record</p>
    </div>
    <div class="p-6">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">Before submitting</p>
                <p class="text-xs text-amber-700 mt-0.5">Verify all information against original documents. Run screening from the client profile after creation.</p>
            </div>
        </div>

        {{-- Corporate summary --}}
        <template x-if="clientType!=='individual'">
        <div class="space-y-2">
            @foreach([[1,'Company profile','Basic details, trade licence, addresses'],[2,'Authorised signatories','Persons authorised to sign'],[3,'Shareholders & UBOs','Ownership structure and beneficial owners'],[4,'AML / CDD','Risk rating, source of funds, transaction profile'],[5,'Documents','Uploaded client documents'],[6,'Declarations','Compliance declarations checklist'],[7,'Screening','AML sanctions and PEP screening']] as [$n,$t,$d])
            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div><p class="text-sm font-medium text-gray-800">{{ $t }}</p><p class="text-xs text-gray-400">{{ $d }}</p></div>
                </div>
                <button type="button" @click="step = {{ $n }}" class="text-xs text-blue-600 hover:underline">Edit</button>
            </div>
            @endforeach
        </div>
        </template>

        {{-- Individual summary --}}
        <template x-if="clientType==='individual'">
        <div class="space-y-2">
            @foreach([[1,'Personal profile','Name, passport, Emirates ID, PEP status'],[2,'AML / CDD','Risk rating, source of funds, transaction profile'],[3,'Documents','Uploaded client documents'],[4,'Declarations','Compliance declarations checklist'],[5,'Screening','AML sanctions and PEP screening']] as [$n,$t,$d])
            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div><p class="text-sm font-medium text-gray-800">{{ $t }}</p><p class="text-xs text-gray-400">{{ $d }}</p></div>
                </div>
                <button type="button" @click="indStep = {{ $n }}" class="text-xs text-blue-600 hover:underline">Edit</button>
            </div>
            @endforeach
        </div>
        </template>
    </div>
</div>

{{-- ── NAV BUTTONS ──────────────────────────────────────────────────────────── --}}
<div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
    <button type="button"
            x-show="(clientType!=='individual' && step>1) || (clientType==='individual' && indStep>1)"
            @click="prevStep"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Previous
    </button>
    <div x-show="(clientType!=='individual' && step===1) || (clientType==='individual' && indStep===1)"></div>

    <div class="flex items-center gap-3">
        <span class="text-xs text-gray-400"
              x-text="clientType!=='individual' ? 'Step '+step+' of 8' : 'Step '+indStep+' of 6'"></span>
        <button type="button"
                x-show="(clientType!=='individual' && step<8) || (clientType==='individual' && indStep<6)"
                @click="nextStep"
                class="flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            Next <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        <button type="button" @click="validateAndSubmit()"
                x-show="(clientType!=='individual' && step===8) || (clientType==='individual' && indStep===6)"
                class="flex items-center gap-2 px-6 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Create client record
        </button>
    </div>
</div>

</div>
</form>

<script>
function clientForm() {
    return {
        clientType: '{{ array_key_first($sector["client_types"]) }}',
        step: 1,
        indStep: 1,
        stepErrors:    {1:false,2:false,3:false,4:false,5:false,6:false,7:false,8:false},
        indStepErrors: {1:false,2:false,3:false,4:false,5:false,6:false},

        init() {
            window.addEventListener('set-client-type', (e) => this.setType(e.detail));
        },

        signatories:  [{ full_name:'', position:'', nationality:'', dob:'', passport_number:'', passport_expiry:'', eid_number:'' }],
        shareholders: [{ shareholder_type:'individual', name:'', nationality:'', dob:'', ownership_percentage:'', passport_number:'', is_ubo:false, is_resident:false, eid_number:'', eid_expiry:'' }],
        ubos:         [{ full_name:'', nationality:'', dob:'', passport_number:'', ownership_percentage:'', country_of_residence:'', pep_status:false }],
        setType(t) {
            this.clientType=t; this.step=1; this.indStep=1;
            this.stepErrors={1:false,2:false,3:false,4:false,5:false,6:false,7:false,8:false};
            this.indStepErrors={1:false,2:false,3:false,4:false,5:false,6:false};
            window.scrollTo(0,0);
        },
        nextStep() {
            if(this.clientType!=='individual'&&this.step<8){this.step++;}
            else if(this.clientType==='individual'&&this.indStep<6){this.indStep++;}
            window.scrollTo(0,0);
        },
        prevStep() {
            if(this.clientType!=='individual'&&this.step>1){this.step--;}
            else if(this.clientType==='individual'&&this.indStep>1){this.indStep--;}
            window.scrollTo(0,0);
        },
        addSig()    { this.signatories.push({full_name:'',position:'',nationality:'',dob:'',passport_number:'',passport_expiry:'',eid_number:''}); },
        removeSig(i){ this.signatories.splice(i,1); },
        addSh()     { this.shareholders.push({shareholder_type:'individual',name:'',nationality:'',dob:'',ownership_percentage:'',passport_number:'',is_ubo:false,is_resident:false,eid_number:'',eid_expiry:''}); },
        removeSh(i) { this.shareholders.splice(i,1); },
        addUbo()    { this.ubos.push({full_name:'',nationality:'',dob:'',passport_number:'',ownership_percentage:'',country_of_residence:'',pep_status:false}); },
        removeUbo(i){ this.ubos.splice(i,1); },
        validateAndSubmit() {
            const isInd    = this.clientType === 'individual';
            const total    = isInd ? 6 : 8;
            const attr     = isInd ? 'data-ind-step' : 'data-corp-step';
            const errors   = {};
            let   first    = null;

            for (let s = 1; s < total; s++) {
                const sec = document.querySelector('[' + attr + '="' + s + '"]');
                if (!sec) continue;
                let bad = false;
                sec.querySelectorAll('input[required],select[required],textarea[required]').forEach(function(inp) {
                    if (!inp.value || inp.value.trim() === '') bad = true;
                });
                if (bad) { errors[s] = true; if (!first) first = s; }
            }

            if (isInd) {
                this.indStepErrors = Object.assign({1:false,2:false,3:false,4:false,5:false,6:false}, errors);
            } else {
                this.stepErrors = Object.assign({1:false,2:false,3:false,4:false,5:false,6:false,7:false,8:false}, errors);
            }

            if (first !== null) {
                if (isInd) { this.indStep = first; } else { this.step = first; }
                window.scrollTo(0, 0);
                return;
            }

            document.querySelector('form[novalidate]').submit();
        },
    }
}

function clientSearch(searchUrl, initialValue) {
    return {
        searchUrl,
        query:       initialValue || '',
        suggestions: [],
        selected:    null,
        timer:       null,
        closeTimer:  null,

        onInput() {
            this.selected = null;
            clearTimeout(this.timer);
            if (this.query.length < 2) { this.suggestions = []; return; }
            this.timer = setTimeout(() => this.fetch(), 300);
        },

        async fetch() {
            try {
                const res = await fetch(this.searchUrl + '?q=' + encodeURIComponent(this.query));
                this.suggestions = await res.json();
            } catch(e) { this.suggestions = []; }
        },

        select(client) {
            this.selected    = client;
            this.suggestions = [];
            // Also update the visible input to show the selected name
            this.query = client.name;
        },

        dismiss() {
            this.selected = null;
        },

        close() {
            this.suggestions = [];
        },

        delayClose() {
            this.closeTimer = setTimeout(() => this.close(), 150);
        },
    };
}

function docScanner() {
    return {
        open:     true,
        files:    [],
        scanning: false,
        progress: '',
        filled:   [],
        error:    '',

        filesSelected(e) {
            this.files  = Array.from(e.target.files);
            this.filled = [];
            this.error  = '';
            this.progress = '';
        },

        async scan() {
            if (!this.files.length || this.scanning) return;
            this.scanning = true;
            this.filled   = [];
            this.error    = '';

            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const allFilled = new Set();

            for (let i = 0; i < this.files.length; i++) {
                this.progress = this.files.length > 1
                    ? `Scanning document ${i + 1} of ${this.files.length}…`
                    : 'Scanning…';

                const fd = new FormData();
                fd.append('document', this.files[i]);
                fd.append('_token', token);

                try {
                    const res  = await fetch(this.$el.dataset.url, { method: 'POST', body: fd });
                    const text = await res.text();
                    console.log('[Scan] status:', res.status, 'body:', text.substring(0, 500));
                    let data;
                    try { data = JSON.parse(text); } catch { data = null; }

                    if (!res.ok || !data) {
                        this.error = `[${res.status}] ` + (data?.error || text.substring(0, 200));
                        continue;
                    }

                    this.applyFields(data, allFilled);
                } catch (e) {
                    this.error = `Could not reach the server for document ${i + 1}. Please try again.`;
                }
            }

            this.filled   = Array.from(allFilled);
            this.progress = '';
            this.scanning = false;
            if (this.filled.length > 0) this.open = false;
        },

        applyFields(data, filled) {
            const map = {
                full_name:            'Full name',
                company_name:         'Company name',
                nationality:          'Nationality',
                dob:                  'Date of birth',
                gender:               'Gender',
                passport_number:      'Passport no.',
                passport_expiry:      'Passport expiry',
                eid_number:           'Emirates ID',
                eid_expiry:           'EID expiry',
                trade_license_no:     'Trade licence no.',
                trade_license_expiry: 'Licence expiry',
                legal_form:           'Legal form',
                address:              'Address',
                phone:                'Phone',
                email:                'Email',
            };

            if (data.document_type === 'passport' || data.document_type === 'emirates_id') {
                window.dispatchEvent(new CustomEvent('set-client-type', { detail: 'individual' }));
            }

            for (const [key, label] of Object.entries(map)) {
                if (!data[key]) continue;
                const input = document.querySelector(`[name="${key}"]`);
                if (!input) continue;
                input.value = data[key];
                input.dispatchEvent(new Event('input',  { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                input.classList.add('ring-2', 'ring-blue-300', 'bg-blue-50');
                filled.add(label);
            }
        },
    }
}
</script>
@endsection
