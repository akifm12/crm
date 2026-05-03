@extends('layouts.tenant')

@php
$isCorporate = $client->client_type !== 'individual';
$typeLabels  = ['corporate_local'=>'Corporate — Local','corporate_import'=>'Corporate — Import','corporate_export'=>'Corporate — Export','individual'=>'Individual'];
@endphp

@section('title', 'Edit ' . $client->displayName())
@section('page-title', 'Edit — ' . $client->displayName())
@section('page-subtitle', $typeLabels[$client->client_type] ?? $client->client_type)

@section('content')

<form method="POST" action="{{ route('tenant.clients.update', [$tenant->slug, $client->id]) }}"
      enctype="multipart/form-data" novalidate>
@csrf
@method('PATCH')

<div x-data="{ tab: 'profile' }">

    {{-- Tab bar --}}
    <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5 overflow-x-auto">
        @foreach(array_filter([
            ['profile',      'Profile'],
            $isCorporate ? ['signatories', 'Signatories'] : null,
            $isCorporate ? ['shareholders','Shareholders & UBOs'] : null,
            ['aml',          'AML / CDD'],
            ['declarations', 'Declarations'],
        ]) as $t)
        <button type="button" @click="tab='{{ $t[0] }}'"
                :class="tab==='{{ $t[0] }}' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">
            {{ $t[1] }}
        </button>
        @endforeach
    </div>

    {{-- ── PROFILE ───────────────────────────────────────────────────────── --}}
    <div x-show="tab==='profile'">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-700 pb-2 border-b border-gray-100">
                {{ $isCorporate ? 'Company profile' : 'Personal profile' }}
            </h3>

            @if($isCorporate)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('tenant.clients._field', ['name'=>'company_name','label'=>'Company name','required'=>true,'value'=>$client->company_name])
                @include('tenant.clients._field', ['name'=>'trade_license_no','label'=>'Trade licence number','value'=>$client->trade_license_no])
                @include('tenant.clients._field', ['name'=>'trade_license_issue','label'=>'Licence issue date','type'=>'date','value'=>$client->trade_license_issue?->format('Y-m-d')])
                @include('tenant.clients._field', ['name'=>'trade_license_expiry','label'=>'Licence expiry date','type'=>'date','value'=>$client->trade_license_expiry?->format('Y-m-d')])
                @include('tenant.clients._field', ['name'=>'trn_number','label'=>'TRN number','value'=>$client->trn_number])
                @include('tenant.clients._field', ['name'=>'ejari_number','label'=>'Ejari number','value'=>$client->ejari_number])
                @include('tenant.clients._select', ['name'=>'legal_form','label'=>'Legal form','value'=>$client->legal_form,'options'=>['LLC'=>'LLC','FZE'=>'FZE','FZCO'=>'FZCO','Sole Establishment'=>'Sole Establishment','Civil Company'=>'Civil Company','Branch'=>'Branch (Foreign)','Other'=>'Other']])
                @include('tenant.clients._country', ['name'=>'country_of_incorporation','label'=>'Country of incorporation','required'=>true,'value'=>$client->country_of_incorporation])
                @include('tenant.clients._field', ['name'=>'business_activity','label'=>'Business activity','required'=>true,'value'=>$client->business_activity])
                @include('tenant.clients._field', ['name'=>'nature_of_business','label'=>'Nature of business','value'=>$client->nature_of_business])
                @include('tenant.clients._field', ['name'=>'email','label'=>'Email','type'=>'email','value'=>$client->email])
                @include('tenant.clients._field', ['name'=>'phone','label'=>'Phone','value'=>$client->phone])
                @include('tenant.clients._field', ['name'=>'website','label'=>'Website','value'=>$client->website])
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('tenant.clients._textarea', ['name'=>'registered_address','label'=>'Registered address','value'=>$client->registered_address])
                @include('tenant.clients._textarea', ['name'=>'operating_address','label'=>'Operating address','value'=>$client->operating_address])
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('tenant.clients._field', ['name'=>'full_name','label'=>'Full name','required'=>true,'value'=>$client->full_name])
                @include('tenant.clients._field', ['name'=>'name_arabic','label'=>'Name in Arabic','value'=>$client->name_arabic])
                @include('tenant.clients._country', ['name'=>'nationality','label'=>'Nationality','required'=>true,'value'=>$client->nationality])
                @include('tenant.clients._field', ['name'=>'dob','label'=>'Date of birth','type'=>'date','value'=>$client->dob?->format('Y-m-d')])
                @include('tenant.clients._field', ['name'=>'passport_number','label'=>'Passport number','required'=>true,'value'=>$client->passport_number])
                @include('tenant.clients._field', ['name'=>'passport_expiry','label'=>'Passport expiry','type'=>'date','value'=>$client->passport_expiry?->format('Y-m-d')])
                @include('tenant.clients._field', ['name'=>'eid_number','label'=>'Emirates ID','value'=>$client->eid_number])
                @include('tenant.clients._field', ['name'=>'eid_expiry','label'=>'EID expiry','type'=>'date','value'=>$client->eid_expiry?->format('Y-m-d')])
                @include('tenant.clients._field', ['name'=>'occupation','label'=>'Occupation','value'=>$client->occupation])
                @include('tenant.clients._field', ['name'=>'employer_name','label'=>'Employer','value'=>$client->employer_name])
                @include('tenant.clients._field', ['name'=>'email','label'=>'Email','type'=>'email','value'=>$client->email])
                @include('tenant.clients._field', ['name'=>'phone','label'=>'Phone','value'=>$client->phone])
            </div>
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <input type="checkbox" name="pep_status" value="1" id="pep_status" {{ $client->pep_status ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600">
                <label for="pep_status" class="text-sm text-gray-700">Politically Exposed Person (PEP)</label>
            </div>
            @include('tenant.clients._field', ['name'=>'pep_details','label'=>'PEP details (if applicable)','value'=>$client->pep_details])
            @endif
        </div>
    </div>

    {{-- ── SIGNATORIES ───────────────────────────────────────────────────── --}}
    @if($isCorporate)
    <div x-show="tab==='signatories'" x-cloak
         x-data="{ sigs: {{ json_encode($client->signatories->map(fn($s) => ['full_name'=>$s->full_name,'position'=>$s->position,'nationality'=>$s->nationality,'dob'=>$s->dob?->format('Y-m-d'),'passport_number'=>$s->passport_number,'passport_expiry'=>$s->passport_expiry?->format('Y-m-d'),'eid_number'=>$s->eid_number])->toArray()) }} }">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Authorised signatories</h3>
                <button type="button" @click="sigs.push({full_name:'',position:'',nationality:'',passport_number:'',passport_expiry:'',eid_number:''})"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">+ Add signatory</button>
            </div>
            <template x-for="(sig, i) in sigs" :key="i">
                <div class="border border-gray-200 rounded-xl p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-700" x-text="'Signatory ' + (i+1)"></p>
                        <button type="button" @click="sigs.splice(i,1)" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Full name *</label>
                            <input type="text" :name="'signatories['+i+'][full_name]'" x-model="sig.full_name" required
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Position</label>
                            <input type="text" :name="'signatories['+i+'][position]'" x-model="sig.position"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                            <select :name="'signatories['+i+'][nationality]'" x-model="sig.nationality"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">— Select —</option>
                                @php $countryOpts = \App\Models\Country::orderBy('country_name')->get(['country_code','country_name']); @endphp
                        @foreach($countryOpts as $_c)
                        <option value="{{ $_c->country_code }}">{{ $_c->country_name }}</option>
                        @endforeach
                            </select></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                            <input type="date" :name="'signatories['+i+'][dob]'" x-model="sig.dob"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Passport number</label>
                            <input type="text" :name="'signatories['+i+'][passport_number]'" x-model="sig.passport_number"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Passport expiry</label>
                            <input type="date" :name="'signatories['+i+'][passport_expiry]'" x-model="sig.passport_expiry"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Emirates ID</label>
                            <input type="text" :name="'signatories['+i+'][eid_number]'" x-model="sig.eid_number"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                    </div>
                </div>
            </template>
            <p x-show="sigs.length === 0" class="text-sm text-gray-400 text-center py-6">No signatories added.</p>
        </div>
    </div>

    {{-- ── SHAREHOLDERS & UBOs ───────────────────────────────────────────── --}}
    <div x-show="tab==='shareholders'" x-cloak
         x-data="{
            shareholders: {{ json_encode($client->shareholders->map(fn($s) => ['shareholder_type'=>$s->shareholder_type,'name'=>$s->name,'nationality'=>$s->nationality,'dob'=>$s->dob?->format('Y-m-d'),'ownership_percentage'=>$s->ownership_percentage,'passport_number'=>$s->passport_number,'is_ubo'=>(bool)$s->is_ubo])->toArray()) }},
            ubos: {{ json_encode($client->ubos->map(fn($u) => ['full_name'=>$u->full_name,'nationality'=>$u->nationality,'dob'=>$u->dob?->format('Y-m-d'),'passport_number'=>$u->passport_number,'ownership_percentage'=>$u->ownership_percentage,'country_of_residence'=>$u->country_of_residence,'pep_status'=>(bool)$u->pep_status])->toArray()) }}
         }">
        <div class="space-y-5">
            {{-- Shareholders --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Shareholders</h3>
                    <button type="button" @click="shareholders.push({shareholder_type:'individual',name:'',nationality:'',ownership_percentage:'',passport_number:'',is_ubo:false})"
                            class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">+ Add shareholder</button>
                </div>
                <template x-for="(sh, i) in shareholders" :key="i">
                    <div class="border border-gray-200 rounded-xl p-4 mb-3">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-gray-700" x-text="'Shareholder ' + (i+1)"></p>
                            <button type="button" @click="shareholders.splice(i,1)" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select :name="'shareholders['+i+'][shareholder_type]'" x-model="sh.shareholder_type"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="individual">Individual</option><option value="corporate">Corporate</option>
                                </select></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                                <input type="text" :name="'shareholders['+i+'][name]'" x-model="sh.name"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                                <select :name="'shareholders['+i+'][nationality]'" x-model="sh.nationality"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">— Select —</option>
                                    @php $countryOpts = \App\Models\Country::orderBy('country_name')->get(['country_code','country_name']); @endphp
                        @foreach($countryOpts as $_c)
                        <option value="{{ $_c->country_code }}">{{ $_c->country_name }}</option>
                        @endforeach
                                </select></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                                <input type="date" :name="'shareholders['+i+'][dob]'" x-model="sh.dob"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Ownership %</label>
                                <input type="number" step="0.01" :name="'shareholders['+i+'][ownership_percentage]'" x-model="sh.ownership_percentage"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Passport number</label>
                                <input type="text" :name="'shareholders['+i+'][passport_number]'" x-model="sh.passport_number"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div class="flex items-center gap-2 mt-4">
                                <input type="checkbox" :name="'shareholders['+i+'][is_ubo]'" value="1" x-model="sh.is_ubo"
                                       class="rounded border-gray-300 text-blue-600">
                                <label class="text-xs text-gray-600">Is UBO</label>
                            </div>
                        </div>
                    </div>
                </template>
                <p x-show="shareholders.length === 0" class="text-sm text-gray-400 text-center py-4">No shareholders added.</p>
            </div>

            {{-- UBOs --}}
            <div class="bg-white rounded-xl border border-amber-200 p-5">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-amber-100">
                    <h3 class="text-sm font-semibold text-amber-800">Ultimate Beneficial Owners (UBOs)</h3>
                    <button type="button" @click="ubos.push({full_name:'',nationality:'',dob:'',passport_number:'',ownership_percentage:'',country_of_residence:'',pep_status:false})"
                            class="px-3 py-1.5 text-xs font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700">+ Add UBO</button>
                </div>
                <template x-for="(ubo, i) in ubos" :key="i">
                    <div class="border border-amber-100 rounded-xl p-4 mb-3 bg-amber-50">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-amber-800" x-text="'UBO ' + (i+1)"></p>
                            <button type="button" @click="ubos.splice(i,1)" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Full name *</label>
                                <input type="text" :name="'ubos['+i+'][full_name]'" x-model="ubo.full_name"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                                <select :name="'ubos['+i+'][nationality]'" x-model="ubo.nationality"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">— Select —</option>
                                    @php $countryOpts = \App\Models\Country::orderBy('country_name')->get(['country_code','country_name']); @endphp
                        @foreach($countryOpts as $_c)
                        <option value="{{ $_c->country_code }}">{{ $_c->country_name }}</option>
                        @endforeach
                                </select></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                                <input type="date" :name="'ubos['+i+'][dob]'" x-model="ubo.dob"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Passport number</label>
                                <input type="text" :name="'ubos['+i+'][passport_number]'" x-model="ubo.passport_number"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Ownership %</label>
                                <input type="number" step="0.01" :name="'ubos['+i+'][ownership_percentage]'" x-model="ubo.ownership_percentage"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Country of residence</label>
                                <input type="text" :name="'ubos['+i+'][country_of_residence]'" x-model="ubo.country_of_residence"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div class="flex items-center gap-2 mt-4">
                                <input type="checkbox" :name="'ubos['+i+'][pep_status]'" value="1" x-model="ubo.pep_status"
                                       class="rounded border-gray-300 text-blue-600">
                                <label class="text-xs text-gray-600">PEP</label>
                            </div>
                        </div>
                    </div>
                </template>
                <p x-show="ubos.length === 0" class="text-sm text-gray-400 text-center py-4">No UBOs added.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- ── AML / CDD ─────────────────────────────────────────────────────── --}}
    <div x-show="tab==='aml'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-700 pb-2 border-b border-gray-100">AML / CDD details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('tenant.clients._field', ['name'=>'purpose_of_relationship','label'=>'Purpose of business relationship','required'=>true,'value'=>$client->purpose_of_relationship])
                @if($isCorporate)
                @include('tenant.clients._field', ['name'=>'expected_monthly_volume','label'=>'Expected monthly volume (AED)','type'=>'number','value'=>$client->expected_monthly_volume])
                @include('tenant.clients._select', ['name'=>'expected_monthly_frequency','label'=>'Transaction frequency','value'=>$client->expected_monthly_frequency,'options'=>['1-5'=>'1–5 per month','6-15'=>'6–15 per month','16-30'=>'16–30 per month','30+'=>'More than 30 per month']])
                @endif
                @include('tenant.clients._select', ['name'=>'cdd_type','label'=>'CDD type','default'=>$client->cdd_type ?? 'standard','options'=>['standard'=>'Standard CDD','enhanced'=>'Enhanced Due Diligence (EDD)']])
            </div>

            {{-- Source of funds --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Source of funds</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach(['trading_revenue'=>'Trading revenue','salary'=>'Salary','investment'=>'Investment returns','inheritance'=>'Inheritance','loan'=>'Loan / financing','other'=>'Other'] as $v=>$l)
                    <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="source_of_funds[]" value="{{ $v }}"
                               {{ is_array($client->source_of_funds) && in_array($v, $client->source_of_funds) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700">{{ $l }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Source of wealth --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Source of wealth</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach(['uae_business'=>'UAE business','foreign_business'=>'Foreign business','salary'=>'Salary / employment','real_estate'=>'Real estate','inheritance'=>'Inheritance','savings'=>'Savings','investment'=>'Investment returns','other'=>'Other'] as $v=>$l)
                    <label class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="source_of_wealth[]" value="{{ $v }}"
                               {{ is_array($client->source_of_wealth) && in_array($v, $client->source_of_wealth) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700">{{ $l }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Risk rating --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Risk rating</label>
                <div class="flex gap-3">
                    @foreach(['low'=>['Low risk','border-green-200 bg-green-50','text-green-700'],'medium'=>['Medium risk','border-amber-200 bg-amber-50','text-amber-700'],'high'=>['High risk','border-red-200 bg-red-50','text-red-700']] as $v=>[$l,$border,$tcls])
                    <label class="flex-1 border {{ $border }} rounded-xl p-4 cursor-pointer text-center hover:opacity-80 transition">
                        <input type="radio" name="risk_rating" value="{{ $v }}" {{ $client->risk_rating===$v ? 'checked' : '' }} class="sr-only">
                        <span class="text-sm font-semibold {{ $tcls }}">{{ $l }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('tenant.clients._field', ['name'=>'next_review_date','label'=>'Next KYC review date','type'=>'date','value'=>$client->next_review_date?->format('Y-m-d')])
                @include('tenant.clients._textarea', ['name'=>'risk_notes','label'=>'Risk notes','value'=>$client->risk_notes])
            </div>
        </div>
    </div>

    {{-- ── DECLARATIONS ──────────────────────────────────────────────────── --}}
    <div x-show="tab==='declarations'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 pb-2 border-b border-gray-100 mb-4">Declarations</h3>
            <div class="space-y-3">
                @foreach([
                    ['decl_pep',            'Declaration 1 — PEP',                 'Politically Exposed Person declaration'],
                    ['decl_supply_chain',   'Declaration 2 — Gold supply chain',   'Supply chain sourcing declaration'],
                    ['decl_cahra',          'Declaration 3 — No CAHRA imports',    'Conflict-affected areas declaration'],
                    ['decl_source_of_funds','Declaration 4 — Source of funds',     'Source of funds & wealth declaration'],
                    ['decl_sanctions',      'Declaration 5 — Sanctions compliance','Sanctions compliance declaration'],
                    ['decl_ubo',            'Declaration 6 — Beneficial ownership','UBO disclosure declaration'],
                    ['decl_master_signed',  'Master declaration — physical signature','Signed copy obtained and uploaded'],
                ] as [$field, $title, $desc])
                <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $title }}</p>
                        <p class="text-xs text-gray-400">{{ $desc }}</p>
                    </div>
                    <input type="checkbox" name="{{ $field }}" value="1" {{ $client->$field ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-300 text-blue-600 ml-4">
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Save / Cancel buttons --}}
    <div class="flex items-center justify-between mt-5 pt-5">
        <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}"
           class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Cancel
        </a>
        <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Save changes
        </button>
    </div>

</div>
</form>

@endsection
