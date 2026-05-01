{{-- admin/crm/_select.blade.php --}}
<div>
    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
    <select name="{{ $name }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
        <option value="">— Select —</option>
        @foreach($options as $v => $l)
        <option value="{{ $v }}" {{ old($name)==$v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
    </select>
</div>
