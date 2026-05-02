{{-- resources/views/tenant/clients/_select.blade.php --}}
<div>
    <label for="{{ $name }}" class="block text-xs font-medium text-gray-600 mb-1">
        {{ $label }}
        @if(!empty($required)) <span class="text-red-500">*</span> @endif
    </label>
    <select id="{{ $name }}" name="{{ $name }}"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        @if(empty($default))
        <option value="">— Select —</option>
        @endif
        @foreach($options as $val => $lbl)
            <option value="{{ $val }}" {{ (old($name, $default ?? '') == $val) ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
    </select>
</div>
