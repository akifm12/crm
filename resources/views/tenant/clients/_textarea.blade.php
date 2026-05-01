{{-- resources/views/tenant/clients/_textarea.blade.php --}}
<div>
    <label for="{{ $name }}" class="block text-xs font-medium text-gray-600 mb-1">
        {{ $label }}
        @if(!empty($required)) <span class="text-red-500">*</span> @endif
    </label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows ?? 3 }}"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white resize-none">{{ old($name) }}</textarea>
</div>
