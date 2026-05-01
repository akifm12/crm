{{-- resources/views/tenant/clients/_doc_row.blade.php --}}
<div class="border border-gray-200 rounded-xl p-4">
    <input type="hidden" name="doc_labels[{{ $type }}]" value="{{ $label }}">
    <input type="hidden" name="doc_required[{{ $type }}]" value="{{ $required ? '1' : '0' }}">

    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
                <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                @if($required)
                    <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-medium">Required</span>
                @else
                    <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">Optional</span>
                @endif
            </div>
            <div class="flex flex-wrap gap-3 items-center">
                <input type="file"
                       name="documents[{{ $type }}]"
                       accept=".pdf,.jpg,.jpeg,.png,.docx"
                       class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer">
                @if($has_expiry)
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 whitespace-nowrap">Expiry date:</label>
                    <input type="date"
                           name="doc_expiry[{{ $type }}]"
                           class="px-2 py-1 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                @endif
            </div>
        </div>
        {{-- Upload status indicator --}}
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
        </div>
    </div>
</div>
