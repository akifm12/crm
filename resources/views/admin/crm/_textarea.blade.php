{{-- admin/crm/_textarea.blade.php --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
    <textarea name="{{ $name }}" rows="{{ $rows ?? 3 }}"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white resize-none">{{ old($name) }}</textarea>
</div>
