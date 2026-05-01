@extends('layouts.admin')
@section('title', 'New Client — CRM')
@section('page-title', 'Add new client')

@section('content')

<form method="POST" action="{{ route('crm.store') }}" x-data="crmForm()">
@csrf

<div class="max-w-4xl space-y-5">

    {{-- Company profile --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Company profile</h2>
            <p class="text-xs text-gray-400 mt-0.5">Core registration and contact information</p>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('admin.crm._field', ['name'=>'company_name','label'=>'Company name','required'=>true])
                @include('admin.crm._field', ['name'=>'license_number','label'=>'Trade licence number'])
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('admin.crm._field', ['name'=>'license_issue','label'=>'Licence issue date','type'=>'date'])
                @include('admin.crm._field', ['name'=>'license_expiry','label'=>'Licence expiry date','type'=>'date'])
                @include('admin.crm._field', ['name'=>'license_authority','label'=>'Licence authority'])
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('admin.crm._select', ['name'=>'legal_status','label'=>'Legal form','options'=>['LLC'=>'LLC','Sole Establishment'=>'Sole Establishment','Free Zone LLC'=>'Free Zone LLC','Free Zone Establishment'=>'Free Zone Establishment','Branch'=>'Branch of Foreign Company','Other'=>'Other']])
                @include('admin.crm._field', ['name'=>'country_inc','label'=>'Country of incorporation','value'=>'UAE'])
                @include('admin.crm._field', ['name'=>'regulator','label'=>'Regulator (if any)'])
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('admin.crm._field', ['name'=>'trn','label'=>'TRN number'])
                @include('admin.crm._field', ['name'=>'ejari','label'=>'Ejari number'])
            </div>
            @include('admin.crm._textarea', ['name'=>'address','label'=>'Registered address'])
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('admin.crm._field', ['name'=>'email','label'=>'Company email','type'=>'email'])
                @include('admin.crm._field', ['name'=>'telephone','label'=>'Telephone'])
                @include('admin.crm._field', ['name'=>'website','label'=>'Website'])
                @include('admin.crm._field', ['name'=>'contact_person','label'=>'Primary contact person'])
            </div>
        </div>
    </div>

    {{-- Shareholders --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Shareholders</h2>
        </div>
        <div class="p-6">
            <template x-for="(sh, i) in shareholders" :key="i">
            <div class="border border-gray-200 rounded-xl p-4 mb-3">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500" x-text="'Shareholder ' + (i+1)"></span>
                    <button type="button" @click="shareholders.splice(i,1)" x-show="shareholders.length > 1"
                            class="text-red-400 hover:text-red-600 text-xs">Remove</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full name</label>
                        <input type="text" :name="'shareholders['+i+'][shareholder_name]'" x-model="sh.name"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nationality</label>
                        <input type="text" :name="'shareholders['+i+'][nationality]'" x-model="sh.nationality"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Passport number</label>
                        <input type="text" :name="'shareholders['+i+'][passport]'" x-model="sh.passport"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Passport expiry</label>
                        <input type="date" :name="'shareholders['+i+'][passport_expiry]'" x-model="sh.passport_expiry"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ownership %</label>
                        <input type="number" step="0.01" :name="'shareholders['+i+'][ownership_percentage]'" x-model="sh.ownership_percentage"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <input type="checkbox" :name="'shareholders['+i+'][is_ubo]'" value="1" x-model="sh.is_ubo" :id="'sh_ubo_'+i" class="rounded border-gray-300 text-blue-600">
                        <label :for="'sh_ubo_'+i" class="text-xs text-gray-600">UBO (25%+)</label>
                    </div>
                </div>
            </div>
            </template>
            <button type="button" @click="shareholders.push({name:'',nationality:'',passport:'',passport_expiry:'',ownership_percentage:'',is_ubo:false})"
                    class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add shareholder
            </button>
        </div>
    </div>

    {{-- CRM settings --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">CRM & portal settings</h2>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('admin.crm._select', ['name'=>'stage','label'=>'Pipeline stage','options'=>['lead'=>'Lead','qualified'=>'Qualified','proposal_sent'=>'Proposal Sent','negotiation'=>'Negotiation','onboarding'=>'Onboarding','active'=>'Active','inactive'=>'Inactive']])
                @include('admin.crm._select', ['name'=>'assigned_to','label'=>'Assigned to','options'=>$staff->pluck('name','id')->toArray()])
                @include('admin.crm._field', ['name'=>'client_since','label'=>'Client since','type'=>'date'])
            </div>

            {{-- Services --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Services subscribed to</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach(['AML Consulting','KYC Management','Screening','goAML Filing','Risk Assessment','Compliance Training','Regulatory Advisory','Other'] as $svc)
                    <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                        <input type="checkbox" name="services[]" value="{{ $svc }}" class="rounded border-gray-300 text-blue-600">
                        {{ $svc }}
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Portal type --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tenant portal type <span class="text-red-500">*</span></label>
                <p class="text-xs text-gray-400 mb-3">A portal will be auto-created unless you select None. Slug is auto-generated from company name.</p>
                <div class="flex flex-wrap gap-3">
                    @foreach(['bullion'=>['Bullion','bg-amber-50 border-amber-200 text-amber-700'],'real_estate'=>['Real estate','bg-blue-50 border-blue-200 text-blue-700'],'other'=>['Other','bg-purple-50 border-purple-200 text-purple-700'],'none'=>['None (no portal)','bg-gray-50 border-gray-200 text-gray-600']] as $val=>[$label,$cls])
                    <label class="flex items-center gap-2 px-4 py-2.5 border rounded-xl cursor-pointer hover:opacity-80 transition {{ $cls }}">
                        <input type="radio" name="portal_type" value="{{ $val }}" class="sr-only" {{ $val==='none' ? 'checked' : '' }}>
                        <span class="text-sm font-semibold">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            @include('admin.crm._textarea', ['name'=>'notes','label'=>'Initial notes'])
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
            Create client
        </button>
        <a href="{{ route('crm.index') }}" class="px-6 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Cancel
        </a>
    </div>

</div>
</form>

<script>
function crmForm() {
    return {
        shareholders: [{ name:'', nationality:'', passport:'', passport_expiry:'', ownership_percentage:'', is_ubo:false }],
    }
}
</script>

@endsection
