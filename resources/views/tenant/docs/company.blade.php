@extends('layouts.tenant')
@section('title', 'Company Documents — ' . $tenant->name)
@section('page-title', 'Company Documents')
@section('page-subtitle', 'DNFBP compliance, AML policy and regulatory documents')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold font-mono text-gray-900">{{ $stats['total'] }}</p>
        <p class="text-xs font-medium text-gray-400 mt-0.5">Total documents</p>
    </div>
    <div class="bg-white rounded-xl border {{ $stats['expired'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200' }} p-4 text-center">
        <p class="text-2xl font-bold font-mono {{ $stats['expired'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $stats['expired'] }}</p>
        <p class="text-xs font-medium text-gray-400 mt-0.5">Expired</p>
    </div>
    <div class="bg-white rounded-xl border {{ $stats['expiring'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-gray-200' }} p-4 text-center">
        <p class="text-2xl font-bold font-mono {{ $stats['expiring'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $stats['expiring'] }}</p>
        <p class="text-xs font-medium text-gray-400 mt-0.5">Expiring soon</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Upload form --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Upload document</h3>
            <form method="POST" action="{{ route('tenant.docs.company.upload', $tenant->slug) }}"
                  enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Document type</label>
                    <select name="document_type" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">— Select type —</option>
                        @foreach($docTypes as $dt)
                        <option value="{{ $dt['type'] }}">{{ $dt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Label / description</label>
                    <input type="text" name="document_label" required
                           placeholder="e.g. AML Policy v2.1 — 2026"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">File <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                           class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG, DOCX, XLSX — max 20MB</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry date (if applicable)</label>
                    <input type="date" name="expiry_date"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <input type="text" name="notes" placeholder="Optional notes..."
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit"
                        class="w-full py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload document
                </button>
            </form>
        </div>
    </div>

    {{-- Document list by category --}}
    <div class="lg:col-span-2 space-y-4">

        @forelse($docTypes as $dt)
        @php $typeDocs = $byType->get($dt['type'], collect()); @endphp

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-gray-700">{{ $dt['label'] }}</h3>
                    @if($typeDocs->count())
                    <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full font-mono">{{ $typeDocs->count() }}</span>
                    @endif
                </div>
                @if($typeDocs->filter(fn($d) => $d->isExpired())->count())
                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-semibold">⚠ Expired</span>
                @elseif($typeDocs->filter(fn($d) => $d->isExpiringSoon())->count())
                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-semibold">Expiring soon</span>
                @endif
            </div>

            @if($typeDocs->isEmpty())
            <div class="px-5 py-4 text-sm text-gray-400 italic">No documents uploaded yet.</div>
            @else
            <div class="divide-y divide-gray-100">
                @foreach($typeDocs as $doc)
                <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg {{ $doc->isExpired() ? 'bg-red-50' : 'bg-blue-50' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 {{ $doc->isExpired() ? 'text-red-400' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $doc->document_label }}</p>
                            <p class="text-xs text-gray-400">{{ $doc->file_name }} · {{ $doc->fileSizeFormatted() }}
                                @if($doc->uploader) · {{ $doc->uploader->name }} @endif
                                · {{ $doc->created_at->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($doc->expiry_date)
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                            {{ $doc->isExpired() ? 'bg-red-100 text-red-700' : ($doc->isExpiringSoon() ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $doc->isExpired() ? 'Expired' : 'Exp.' }} {{ $doc->expiry_date->format('d M Y') }}
                        </span>
                        @endif
                        <a href="{{ route('tenant.docs.company.download', [$tenant->slug, $doc->id]) }}"
                           class="text-sm font-medium text-blue-600 hover:underline">Download</a>
                        <form method="POST" action="{{ route('tenant.docs.company.delete', [$tenant->slug, $doc->id]) }}"
                              onsubmit="return confirm('Delete this document?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12 text-gray-400 text-sm">No document types configured.</div>
        @endforelse
    </div>
</div>

@endsection
