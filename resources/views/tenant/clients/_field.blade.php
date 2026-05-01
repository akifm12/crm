{{-- resources/views/tenant/clients/_field.blade.php --}}
<div>
    <label for="{{ $name }}" class="block text-xs font-medium text-gray-600 mb-1">
        {{ $label }}
        @if(!empty($required)) <span class="text-red-500">*</span> @endif
    </label>
    <input type="{{ $type ?? 'text' }}"
           id="{{ $name }}"
           name="{{ $name }}"
           value="{{ old($name, $value ?? '') }}"
           {{ !empty($required) ? 'required' : '' }}
           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
    @if(!empty($hint))
        <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
    @endif
</div>
