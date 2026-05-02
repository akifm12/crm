{{-- resources/views/tenant/clients/_country.blade.php --}}
{{-- Usage: @include('tenant.clients._country', ['name'=>'country_field', 'label'=>'Country', 'value'=>$current, 'required'=>true]) --}}
@php
    $selected = old($name, $value ?? '');
    $countries = app(\App\Models\Country::class)->orderBy('country_name')->get();
@endphp
<div>
    <label for="{{ $name }}" class="block text-xs font-medium text-gray-600 mb-1">
        {{ $label ?? 'Country' }}
        @if(!empty($required)) <span class="text-red-500">*</span> @endif
    </label>
    <select id="{{ $name }}" name="{{ $name }}"
            {{ !empty($required) ? 'required' : '' }}
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        <option value="">— Select country —</option>
        @foreach($countries as $country)
        <option value="{{ $country->country_code }}"
                {{ $selected === $country->country_code ? 'selected' : '' }}>
            {{ $country->country_name }}
        </option>
        @endforeach
    </select>
</div>
