{{-- resources/views/tenant/clients/_doc_row_multi.blade.php --}}
{{-- Multi-upload doc row: one row per file, each with its own expiry. Used for passport groups. --}}
<div x-data="{
    rows: [{ id: 0 }],
    next: 1,
    add()    { this.rows.push({ id: this.next++ }); },
    remove(id) { if (this.rows.length > 1) this.rows = this.rows.filter(r => r.id !== id); }
}" class="border border-gray-200 rounded-xl p-4">

    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
            @if($required)
                <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-medium">Required</span>
            @else
                <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">Optional</span>
            @endif
        </div>
        <button type="button" @click="add()"
                class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add another
        </button>
    </div>

    <div class="space-y-2">
        <template x-for="(row, idx) in rows" :key="row.id">
            <div class="flex flex-wrap items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                {{-- Hidden meta fields keyed to the indexed slot --}}
                <input type="hidden"
                       :name="`doc_labels[{{ $type }}_` + row.id + `]`"
                       value="{{ $label }}">
                <input type="hidden"
                       :name="`doc_required[{{ $type }}_` + row.id + `]`"
                       :value="idx === 0 ? '{{ $required ? '1' : '0' }}' : '0'">

                <span class="text-xs font-medium text-gray-400 w-4 text-center shrink-0" x-text="idx + 1"></span>

                <input type="file"
                       :name="`documents[{{ $type }}_` + row.id + `]`"
                       accept=".pdf,.jpg,.jpeg,.png,.docx"
                       class="flex-1 min-w-0 text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer">

                @if($has_expiry)
                <div class="flex items-center gap-1.5 shrink-0">
                    <label class="text-xs text-gray-400 whitespace-nowrap">Expiry:</label>
                    <input type="date"
                           :name="`doc_expiry[{{ $type }}_` + row.id + `]`"
                           class="px-2 py-1 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                </div>
                @endif

                <button type="button"
                        x-show="rows.length > 1"
                        @click="remove(row.id)"
                        title="Remove this entry"
                        class="shrink-0 text-gray-300 hover:text-red-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>
