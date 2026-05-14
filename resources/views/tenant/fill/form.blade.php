<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Form — {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-2xl mx-auto py-8 px-4" x-data="fillForm()">

    {{-- Header --}}
    <div class="text-center mb-6">
        @if($tenant->logo_url)
        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-14 mx-auto mb-3 object-contain">
        @endif
        <h1 class="text-xl font-bold text-gray-900">{{ $tenant->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">KYC / AML Compliance Form</p>
        <p class="text-xs text-gray-400 mt-1">Link expires {{ $fillToken->expires_at->format('d M Y') }} · One-time use</p>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Step indicators --}}
    <div class="mb-5">
        <template x-if="clientType !== 'individual'">
            <div class="flex items-center">
                @php $cs = [1=>'Profile',2=>'Signatories',3=>'Shareholders',4=>'AML/CDD',5=>'Documents',6=>'Declarations',7=>'Review']; @endphp
                @foreach($cs as $n => $label)
                <div class="flex items-center {{ $n < count($cs) ? 'flex-1' : '' }}">
                    <div class="flex flex-col items-center">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                             :class="step==={{ $n }} ? 'bg-blue-600 text-white' : (step > {{ $n }} ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400')">
                            <template x-if="step > {{ $n }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="step <= {{ $n }}"><span>{{ $n }}</span></template>
                        </div>
                        <span class="text-xs mt-0.5 hidden sm:block whitespace-nowrap"
                              :class="step==={{ $n }} ? 'text-blue-600 font-semibold' : (step > {{ $n }} ? 'text-green-600' : 'text-gray-400')">{{ $label }}</span>
                    </div>
                    @if($n < count($cs))
                    <div class="flex-1 h-px mx-1 -mt-4" :class="step > {{ $n }} ? 'bg-green-400' : 'bg-gray-200'"></div>
                    @endif
                </div>
                @endforeach
            </div>
        </template>
        <template x-if="clientType === 'individual'">
            <div class="flex items-center">
                @php $is = [1=>'Profile',2=>'AML/CDD',3=>'Documents',4=>'Declarations',5=>'Review']; @endphp
                @foreach($is as $n => $label)
                <div class="flex items-center {{ $n < count($is) ? 'flex-1' : '' }}">
                    <div class="flex flex-col items-center">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                             :class="indStep==={{ $n }} ? 'bg-blue-600 text-white' : (indStep > {{ $n }} ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400')">
                            <template x-if="indStep > {{ $n }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="indStep <= {{ $n }}"><span>{{ $n }}</span></template>
                        </div>
                        <span class="text-xs mt-0.5 hidden sm:block whitespace-nowrap"
                              :class="indStep==={{ $n }} ? 'text-blue-600 font-semibold' : (indStep > {{ $n }} ? 'text-green-600' : 'text-gray-400')">{{ $label }}</span>
                    </div>
                    @if($n < count($is))
                    <div class="flex-1 h-px mx-1 -mt-4" :class="indStep > {{ $n }} ? 'bg-green-400' : 'bg-gray-200'"></div>
                    @endif
                </div>
                @endforeach
            </div>
        </template>
    </div>

    <form method="POST" action="{{ url("/{$tenant->slug}/fill/{$fillToken->token}/submit") }}"
          enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="client_type" :value="clientType">

    {{-- ── TYPE SELECTOR ──────────────────────────────────────────────── --}}
    <div x-show="step === 0 && indStep === 0" class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
        <p class="text-sm font-semibold text-gray-700 mb-3">I am applying as</p>
        <div class="grid grid-cols-2 gap-3">
            @foreach($sector['client_types'] as $typeKey => $typeLabel)
            <button type="button" @click="setType('{{ $typeKey }}')"
                    :class="clientType==='{{ $typeKey }}' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200'"
                    class="px-4 py-3 rounded-xl border text-sm font-semibold transition-all text-left">
                {{ $typeLabel }}
            </button>
            @endforeach
        </div>
        <div class="mt-4 flex justify-end">
            <button type="button" @click="clientType !== '' ? (clientType === 'individual' ? indStep = 1 : step = 1) : null"
                    :class="clientType !== '' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                    class="px-6 py-2.5 text-sm font-semibold text-white rounded-lg transition">
                Start →
            </button>
        </div>
    </div>

    {{-- ══ CORP 1 — Company Profile ══════════════════════════════════════ --}}
    <div x-show="clientType !== 'individual' && step === 1" x-cloak class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-800">Company profile</h2></div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company name <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" value="{{ $fillToken->client_name }}" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Trade licence number <span class="text-red-500">*</span></label>
                    <input type="text" name="trade_license_no" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Licence expiry date <span class="text-red-500">*</span></label>
                    <input type="date" name="trade_license_expiry" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">TRN number</label>
                    <input type="text" name="trn_number"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Legal form</label>
                    <select name="legal_form" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">— Select —</option>
                        @foreach(['LLC'=>'LLC','Sole Establishment'=>'Sole Establishment','Free Zone LLC'=>'Free Zone LLC','Free Zone Establishment'=>'Free Zone Establishment','Branch'=>'Branch of Foreign Company','Other'=>'Other'] as $v=>$l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Country of incorporation</label>
                    <input type="text" name="country_of_incorporation" placeholder="AE" maxlength="2"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Business activity / nature of business <span class="text-red-500">*</span></label>
                    <input type="text" name="business_activity" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone number <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email address</label>
                    <input type="email" name="email" value="{{ $fillToken->client_email }}"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Website</label>
                    <input type="text" name="website"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ejari number</label>
                    <input type="text" name="ejari_number"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Registered address</label>
                    <textarea name="registered_address" rows="2"
                              class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ CORP 2 — Signatories ══════════════════════════════════════════ --}}
    <div x-show="clientType !== 'individual' && step === 2" x-cloak class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-800">Authorised signatories</h2></div>
        <div class="p-5">
            <template x-for="(sig, i) in signatories" :key="i">
            <div class="border border-gray-200 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500" x-text="'Signatory ' + (i+1)"></span>
                    <button type="button" @click="signatories.splice(i,1)" x-show="signatories.length > 1" class="text-red-400 text-xs">Remove</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full name <span class="text-red-500">*</span></label>
                        <input type="text" :name="'signatories['+i+'][full_name]'" x-model="sig.full_name" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Position</label>
                        <input type="text" :name="'signatories['+i+'][position]'" x-model="sig.position"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                        <input type="text" :name="'signatories['+i+'][nationality]'" x-model="sig.nationality"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                        <input type="date" :name="'signatories['+i+'][dob]'" x-model="sig.dob"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Passport number</label>
                        <input type="text" :name="'signatories['+i+'][passport_number]'" x-model="sig.passport_number"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Passport expiry</label>
                        <input type="date" :name="'signatories['+i+'][passport_expiry]'" x-model="sig.passport_expiry"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Emirates ID</label>
                        <input type="text" :name="'signatories['+i+'][eid_number]'" x-model="sig.eid_number"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
            </template>
            <button type="button" @click="signatories.push({full_name:'',position:'',nationality:'',dob:'',passport_number:'',passport_expiry:'',eid_number:''})"
                    class="flex items-center gap-2 text-sm text-blue-600 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add another signatory
            </button>
        </div>
    </div>

    {{-- ══ CORP 3 — Shareholders & UBOs ═════════════════════════════════ --}}
    <div x-show="clientType !== 'individual' && step === 3" x-cloak class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-800">Shareholders & UBOs</h2></div>
        <div class="p-5 space-y-5">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Shareholders</h3>
                <template x-for="(sh, i) in shareholders" :key="i">
                <div class="border border-gray-200 rounded-xl p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-500" x-text="'Shareholder '+(i+1)"></span>
                        <button type="button" @click="shareholders.splice(i,1)" x-show="shareholders.length > 1" class="text-red-400 text-xs">Remove</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Full name <span class="text-red-500">*</span></label>
                            <input type="text" :name="'shareholders['+i+'][name]'" x-model="sh.name"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                            <input type="text" :name="'shareholders['+i+'][nationality]'" x-model="sh.nationality"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ownership %</label>
                            <input type="number" step="0.01" :name="'shareholders['+i+'][ownership_percentage]'" x-model="sh.ownership_percentage"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Passport / Reg. no.</label>
                            <input type="text" :name="'shareholders['+i+'][passport_number]'" x-model="sh.passport_number"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date of birth</label>
                            <input type="date" :name="'shareholders['+i+'][dob]'" x-model="sh.dob"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex items-center gap-2 pt-4">
                            <input type="checkbox" :name="'shareholders['+i+'][is_ubo]'" value="1" x-model="sh.is_ubo" :id="'sh_ubo_'+i" class="rounded border-gray-300 text-blue-600">
                            <label :for="'sh_ubo_'+i" class="text-xs text-gray-600">Also a UBO (25%+)</label>
                        </div>
                    </div>
                </div>
                </template>
                <button type="button" @click="shareholders.push({name:'',nationality:'',ownership_percentage:'',passport_number:'',dob:'',is_ubo:false})"
                        class="flex items-center gap-2 text-sm text-blue-600 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add shareholder
                </button>
            </div>
        </div>
    </div>

    {{-- ══ AML/CDD — Corp 4 / Ind 2 ═════════════════════════════════════ --}}
    <div x-show="(clientType !== 'individual' && step === 4) || (clientType === 'individual' && indStep === 2)" x-cloak class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-800">Source of funds & purpose</h2></div>
        <div class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Source of funds <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($sector['source_of_funds'] as $v => $l)
                    <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                        <input type="checkbox" name="source_of_funds[]" value="{{ $v }}" class="rounded border-gray-300 text-blue-600"> {{ $l }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Source of wealth</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['uae_business'=>'UAE business','foreign_business'=>'Foreign business','salary'=>'Salary / employment','inheritance'=>'Inheritance','property_sale'=>'Property sale','investment'=>'Investment','other'=>'Other'] as $v => $l)
                    <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                        <input type="checkbox" name="source_of_wealth[]" value="{{ $v }}" class="rounded border-gray-300 text-blue-600"> {{ $l }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Purpose of this business relationship <span class="text-red-500">*</span></label>
                <input type="text" name="purpose_of_relationship" required
                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    {{-- ══ IND 1 — Individual Profile ══════════════════════════════════ --}}
    <div x-show="clientType === 'individual' && indStep === 1" x-cloak class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-800">Personal details</h2></div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Full name (as per passport) <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" value="{{ $fillToken->client_name }}" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nationality <span class="text-red-500">*</span></label>
                    <input type="text" name="nationality" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date of birth <span class="text-red-500">*</span></label>
                    <input type="date" name="dob" required max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Passport number</label>
                    <input type="text" name="passport_number"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Passport expiry</label>
                    <input type="date" name="passport_expiry"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">⚠ At least one of Passport or Emirates ID is required.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Emirates ID number</label>
                    <input type="text" name="eid_number"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Emirates ID expiry</label>
                    <input type="date" name="eid_expiry"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone number <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email address</label>
                    <input type="email" name="email" value="{{ $fillToken->client_email }}"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Occupation</label>
                    <input type="text" name="occupation"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Employer / business name</label>
                    <input type="text" name="employer_name"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer">
                        <input type="checkbox" name="pep_status" value="1" class="rounded border-gray-300 text-blue-600 mt-0.5">
                        <div>
                            <p class="text-sm font-medium text-gray-700">I am a Politically Exposed Person (PEP)</p>
                            <p class="text-xs text-gray-400">I hold or have held a prominent public function in the last 12 months</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Documents — Corp 5 / Ind 3 ═══════════════════════════════════ --}}
    <div x-show="(clientType !== 'individual' && step === 5) || (clientType === 'individual' && indStep === 3)" x-cloak class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Document upload</h2>
            <p class="text-xs text-gray-400 mt-0.5">Please upload all available documents. Max 10MB per file. PDF, JPG, PNG accepted.</p>
        </div>
        <div class="p-5 space-y-3">
            <div x-show="clientType !== 'individual'">
                @foreach([
                    ['trade_license','Trade licence','true', false],
                    ['moa','Memorandum of Association (MoA)','true', false],
                    ['passport','Authorised signatory passport','true', false],
                    ['eid','Emirates ID (signatory)','false', false],
                    ['shareholder_passport','Shareholder passport(s)','false', true],
                    ['source_of_funds','Source of funds evidence','false', false],
                    ['other','Other document(s)','false', true],
                ] as [$type,$label,$req,$multiple])
                <div class="border border-gray-200 rounded-xl p-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $label }}
                                @if($req === 'true')<span class="text-red-500 ml-1">*</span>@endif
                                @if($multiple)<span class="text-xs text-gray-400 ml-1">(multiple allowed)</span>@endif
                            </p>
                        </div>
                        <input type="file" name="{{ $multiple ? 'documents_multi['.$type.'][]' : 'documents['.$type.']' }}"
                               {{ $multiple ? 'multiple' : '' }}
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                    </div>
                </div>
                @endforeach
            </div>
            <div x-show="clientType === 'individual'">
                @foreach([
                    ['passport','Passport','true', false],
                    ['eid','Emirates ID','false', false],
                    ['proof_of_address','Proof of address','false', false],
                    ['source_of_funds','Source of funds evidence','false', false],
                    ['other','Other document(s)','false', true],
                ] as [$type,$label,$req,$multiple])
                <div class="border border-gray-200 rounded-xl p-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $label }}
                                @if($req === 'true')<span class="text-red-500 ml-1">*</span>@endif
                                @if($multiple)<span class="text-xs text-gray-400 ml-1">(multiple allowed)</span>@endif
                            </p>
                        </div>
                        <input type="file" name="{{ $multiple ? 'documents_multi['.$type.'][]' : 'documents['.$type.']' }}"
                               {{ $multiple ? 'multiple' : '' }}
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══ Declarations — Corp 6 / Ind 4 ═══════════════════════════════ --}}
    <div x-show="(clientType !== 'individual' && step === 6) || (clientType === 'individual' && indStep === 4)" x-cloak class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Declarations</h2>
            <p class="text-xs text-gray-400 mt-0.5">Please read and confirm each declaration</p>
        </div>
        <div class="p-5 space-y-3">
            @php
            $corpDecls = $sector['declarations_corporate'] ?? ['pep','source_of_funds','sanctions','ubo'];
            $indDecls  = $sector['declarations_individual'] ?? ['pep','source_of_funds','sanctions'];
            $declLabels = [
                'pep'                 => ['PEP Declaration', 'I confirm that I am not a Politically Exposed Person (PEP), or have disclosed my PEP status.'],
                'supply_chain'        => ['Supply Chain Declaration', 'I confirm that all gold/precious metals are sourced from legitimate, legal sources.'],
                'cahra'               => ['CAHRA Declaration', 'I confirm no involvement with conflict-affected or high-risk area minerals.'],
                'source_of_funds'     => ['Source of Funds Declaration', 'I confirm all funds used are from legitimate and lawful sources.'],
                'sanctions'           => ['Sanctions Compliance', 'I confirm I am not subject to any applicable sanctions.'],
                'ubo'                 => ['Beneficial Ownership', 'I confirm the ownership structure disclosed is complete and accurate.'],
                'property'            => ['Property Transaction', 'I confirm the property transaction details provided are accurate.'],
                'beneficial_ownership'=> ['Beneficial Ownership Structure', 'I confirm full disclosure of all beneficial owners.'],
                'client_funds'        => ['Client Funds Handling', 'I confirm client monies are properly segregated and managed.'],
            ];
            $allDecls = array_unique(array_merge($corpDecls, $indDecls));
            @endphp
            @foreach($allDecls as $dt)
            @php
                [$title, $desc] = $declLabels[$dt] ?? [ucfirst($dt), ''];
                $inCorp = in_array($dt, $corpDecls);
                $inInd  = in_array($dt, $indDecls);
                $xshow  = $inCorp && $inInd ? 'true' : ($inCorp ? "clientType !== 'individual'" : "clientType === 'individual'");
            @endphp
            <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50"
                   x-show="{{ $xshow }}">
                <input type="checkbox" name="decl_{{ $dt }}" value="1" class="rounded border-gray-300 text-blue-600 mt-0.5 w-5 h-5 flex-shrink-0">
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- ══ Review — Corp 7 / Ind 5 ══════════════════════════════════════ --}}
    <div x-show="(clientType !== 'individual' && step === 7) || (clientType === 'individual' && indStep === 5)" x-cloak class="space-y-4 mb-4">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-blue-800">Ready to submit</p>
            <p class="text-xs text-blue-700 mt-1">Please review your information and confirm the declaration below before submitting.</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" required class="rounded border-gray-300 text-blue-600 mt-0.5 w-5 h-5 flex-shrink-0">
                <p class="text-sm text-gray-600">I confirm that all information provided in this form is <strong>true, accurate and complete</strong> to the best of my knowledge. I understand that providing false information may result in legal consequences under UAE Federal Decree-Law No. 20 of 2018.</p>
            </label>
        </div>
        <button type="submit"
                class="w-full py-3.5 text-sm font-bold text-white bg-green-600 rounded-xl hover:bg-green-700 transition">
            Submit KYC Form
        </button>
        <p class="text-center text-xs text-gray-400">Your information is submitted securely to {{ $tenant->name }}</p>
    </div>

    {{-- ── Navigation ──────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between" x-show="step > 0 || indStep > 0">
        <button type="button" @click="prevStep"
                x-show="(clientType !== 'individual' && step > 1) || (clientType === 'individual' && indStep > 1)"
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            ← Previous
        </button>
        <div x-show="(clientType !== 'individual' && step === 1) || (clientType === 'individual' && indStep === 1)"></div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400"
                  x-text="clientType !== 'individual' ? 'Step ' + step + ' of 7' : 'Step ' + indStep + ' of 5'"></span>
            <button type="button" @click="nextStep"
                    x-show="(clientType !== 'individual' && step < 7) || (clientType === 'individual' && indStep < 5)"
                    class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                Next →
            </button>
        </div>
    </div>

    </form>

    <p class="text-center text-xs text-gray-400 mt-6">Powered by Blue Arrow Management Consultants · AML Compliance Platform</p>
</div>

<script>
function fillForm() {
    return {
        clientType: '{{ $fillToken->client_type === "individual" ? "individual" : array_key_first(array_filter($sector["client_types"], fn($v) => true)) }}',
        step:    '{{ $fillToken->client_type }}' !== 'individual' ? 1 : 0,
        indStep: '{{ $fillToken->client_type }}' === 'individual' ? 1 : 0,
        signatories:  [{full_name:'',position:'',nationality:'',dob:'',passport_number:'',passport_expiry:'',eid_number:''}],
        shareholders: [{name:'',nationality:'',ownership_percentage:'',passport_number:'',dob:'',is_ubo:false}],
        setType(t) {
            this.clientType = t;
            this.step    = t !== 'individual' ? 1 : 0;
            this.indStep = t === 'individual' ? 1 : 0;
        },
        nextStep() {
            if (this.clientType !== 'individual' && this.step < 7) this.step++;
            else if (this.clientType === 'individual' && this.indStep < 5) this.indStep++;
            window.scrollTo(0, 0);
        },
        prevStep() {
            if (this.clientType !== 'individual' && this.step > 1) this.step--;
            else if (this.clientType === 'individual' && this.indStep > 1) this.indStep--;
            window.scrollTo(0, 0);
        },
    }
}
</script>
</body>
</html>
