@extends('layouts.tenant')
@section('title', 'New VAT Filing — ' . $tenant->name)
@section('page-title', 'New VAT Filing')
@section('page-subtitle', 'Auto-calculates from posted invoices and bills')

@section('content')
<div class="max-w-lg">
    <form method="POST" action="{{ route('tenant.accounting.vat-filings.store', $tenant->slug) }}" class="space-y-4">
        @csrf

        @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700">
            VAT amounts are auto-calculated from all <strong>posted</strong> invoices and bills within the selected period.
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Period From *</label>
                <input type="date" name="period_from" value="{{ old('period_from') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Period To *</label>
                <input type="date" name="period_to" value="{{ old('period_to') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="pt-2 flex gap-3">
            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Calculate & create
            </button>
            <a href="{{ route('tenant.accounting.vat-filings.index', $tenant->slug) }}"
               class="px-5 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</a>
        </div>
    </form>
</div>
@endsection
