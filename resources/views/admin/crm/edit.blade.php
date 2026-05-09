@extends('layouts.admin')
@section('title', 'Edit — ' . $crm->company_name)
@section('page-title', 'Edit client')
@section('page-subtitle', $crm->company_name)

@section('content')

<form method="POST" action="{{ route('crm.update', $crm->id) }}" x-data="crmEditForm()">
@csrf @method('PATCH')

<div class="max-w-4xl space-y-5">

    @if($errors->any())
    <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
        <ul class="text-sm text-red-600 list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Company profile --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Company profile</h2>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('admin.crm._field', ['name'=>'company_name','label'=>'Company name','required'=>true,'value'=>$crm->company_name])
                @include('admin.crm._field', ['name'=>'license_number','label'=>'Trade licence number','value'=>$crm->license_number])
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('admin.crm._field', ['name'=>'license_issue','label'=>'Licence issue date','type'=>'date','value'=>$crm->license_issue?->format('Y-m-d')])
                @include('admin.crm._field', ['name'=>'license_expiry','label'=>'Licence expiry date','type'=>'date','value'=>$crm->license_expiry?->format('Y-m-d')])
                @include('admin.crm._field', ['name'=>'license_authority','label'=>'Licence authority','value'=>$crm->license_authority])
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('admin.crm._select', ['name'=>'legal_status','label'=>'Legal form','value'=>$crm->legal_status,'options'=>['LLC'=>'LLC','Sole Establishment'=>'Sole Establishment','Free Zone LLC'=>'Free Zone LLC','Free Zone Establishment'=>'Free Zone Establishment','Branch'=>'Branch of Foreign Company','Other'=>'Other']])
                @include('admin.crm._field', ['name'=>'country_inc','label'=>'Country of incorporation','value'=>$crm->country_inc])
                @include('admin.crm._field', ['name'=>'regulator','label'=>'Regulator (if any)','value'=>$crm->regulator])
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('admin.crm._field', ['name'=>'trn','label'=>'TRN number','value'=>$crm->trn])
                @include('admin.crm._field', ['name'=>'ejari','label'=>'Ejari number','value'=>$crm->ejari])
            </div>
            @include('admin.crm._textarea', ['name'=>'address','label'=>'Registered address','value'=>$crm->address])
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @include('admin.crm._field', ['name'=>'email','label'=>'Company email','type'=>'email','value'=>$crm->email])
                @include('admin.crm._field', ['name'=>'telephone','label'=>'Telephone','value'=>$crm->telephone])
                @include('admin.crm._field', ['name'=>'website','label'=>'Website','value'=>$crm->website])
                @include('admin.crm._field', ['name'=>'contact_person','label'=>'Primary contact person','value'=>$crm->contact_person])
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
            <h2 class="font-semibold text-gray-800">CRM settings</h2>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('admin.crm._select', ['name'=>'stage','label'=>'Pipeline stage','value'=>$crm->stage,'options'=>$stages])
                @include('admin.crm._field', ['name'=>'client_since','label'=>'Client since','type'=>'date','value'=>$crm->client_since?->format('Y-m-d')])
            </div>

            {{-- Services --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Services subscribed to</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @php $currentServices = is_array($crm->services) ? $crm->services : []; @endphp
                    @foreach(['AML Consulting','KYC Management','Screening','goAML Filing','Risk Assessment','Compliance Training','Regulatory Advisory','Other'] as $svc)
                    <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                        <input type="checkbox" name="services[]" value="{{ $svc }}"
                               {{ in_array($svc, $currentServices) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600">
                        {{ $svc }}
                    </label>
                    @endforeach
                </div>
            </div>

            @include('admin.crm._textarea', ['name'=>'notes','label'=>'Notes','value'=>$crm->notes])
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
            Save changes
        </button>
        <a href="{{ route('crm.show', $crm->id) }}" class="px-6 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Cancel
        </a>
    </div>

</div>
</form>

<script>
function crmEditForm() {
    return {
        shareholders: {{ json_encode($crm->shareholders->map(fn($s) => [
            'name'                => $s->shareholder_name ?? '',
            'nationality'         => $s->nationality ?? '',
            'passport'            => $s->passport ?? '',
            'passport_expiry'     => $s->passport_expiry ?? '',
            'ownership_percentage'=> $s->ownership_percentage ?? '',
            'is_ubo'              => (bool)($s->is_ubo ?? false),
        ])->toArray()) ?: '[{"name":"","nationality":"","passport":"","passport_expiry":"","ownership_percentage":"","is_ubo":false}]' }},
    }
}
</script>

@endsection
