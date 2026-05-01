@extends('layouts.admin')
@section('title', ($template ? 'Edit' : 'New') . ' SLA Template')
@section('page-title', $template ? 'Edit SLA Template' : 'New SLA Template')

@section('content')

<form method="POST"
      action="{{ $template ? route('settings.sla.update', $template->id) : route('settings.sla.store') }}"
      class="max-w-4xl">
@csrf
@if($template) @method('PUT') @endif

<div class="space-y-5">

    {{-- Basic info --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Template details</h2>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Template name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $template?->name) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Service type <span class="text-red-500">*</span></label>
                    <input type="text" name="service_type" value="{{ old('service_type', $template?->service_type) }}" required
                           placeholder="e.g. Compliance & AML Support"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Short description</label>
                <textarea name="description" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $template?->description) }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                    <input type="text" name="duration" value="{{ old('duration', $template?->duration) }}"
                           placeholder="e.g. 12 months"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Default fee (AED)</label>
                    <input type="number" step="0.01" name="default_fee" value="{{ old('default_fee', $template?->default_fee) }}"
                           placeholder="0 = to be agreed"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fee frequency</label>
                    <select name="fee_frequency" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Select —</option>
                        @foreach(['monthly'=>'Monthly','quarterly'=>'Quarterly','annual'=>'Annual','fixed'=>'Fixed / One-time','per session'=>'Per session'] as $v=>$l)
                        <option value="{{ $v }}" {{ old('fee_frequency', $template?->fee_frequency)===$v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Service scope --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Service content</h2>
            <p class="text-xs text-gray-400 mt-0.5">These fields will be pulled into the SLA document when this template is used</p>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Scope of work <span class="text-red-500">*</span></label>
                <textarea name="scope_of_work" rows="8" required
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">{{ old('scope_of_work', $template?->scope_of_work) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Use bullet points with "- " prefix for list items</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Client obligations</label>
                <textarea name="client_obligations" rows="6"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">{{ old('client_obligations', $template?->client_obligations) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Deliverables</label>
                <textarea name="deliverables" rows="6"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">{{ old('deliverables', $template?->deliverables) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Legal clauses --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Legal clauses</h2>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Payment terms</label>
                <textarea name="payment_terms" rows="4"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">{{ old('payment_terms', $template?->payment_terms) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Termination clause</label>
                <textarea name="termination_clause" rows="4"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">{{ old('termination_clause', $template?->termination_clause) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Governing law</label>
                <textarea name="governing_law" rows="3"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">{{ old('governing_law', $template?->governing_law) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
            {{ $template ? 'Update template' : 'Create template' }}
        </button>
        <a href="{{ route('settings.index') }}" class="px-6 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            Cancel
        </a>
    </div>

</div>
</form>

@endsection
