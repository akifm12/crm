@extends('layouts.tenant')
@section('title', 'VAT Filing — ' . $tenant->name)
@section('page-title', 'VAT Return')
@section('page-subtitle', $vatFiling->period_from->format('d M Y') . ' — ' . $vatFiling->period_to->format('d M Y'))

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    {{-- Box 1/6 --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">VAT on Sales</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Standard-rated sales</span>
                <span>AED {{ number_format($vatFiling->standard_rated_sales, 2) }}</span>
            </div>
            <div class="flex justify-between font-semibold border-t border-gray-100 pt-2">
                <span>VAT on sales (5%)</span>
                <span class="text-red-600">AED {{ number_format($vatFiling->vat_on_sales, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Box 9/10/11 --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">VAT on Purchases</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Standard-rated purchases</span>
                <span>AED {{ number_format($vatFiling->standard_rated_purchases, 2) }}</span>
            </div>
            <div class="flex justify-between font-semibold border-t border-gray-100 pt-2">
                <span>VAT on purchases (input)</span>
                <span class="text-green-700">AED {{ number_format($vatFiling->vat_on_purchases, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border-2 {{ $vatFiling->net_vat_payable >= 0 ? 'border-red-200' : 'border-green-200' }} p-5 mb-6 max-w-sm">
    <p class="text-sm text-gray-600 mb-1">Net VAT Payable</p>
    <p class="text-3xl font-bold {{ $vatFiling->net_vat_payable >= 0 ? 'text-red-600' : 'text-green-700' }}">
        AED {{ number_format($vatFiling->net_vat_payable, 2) }}
    </p>
    @if($vatFiling->due_date)
    <p class="text-xs text-gray-400 mt-2">Due: {{ $vatFiling->due_date->format('d M Y') }}</p>
    @endif
</div>

{{-- Actions --}}
<div class="flex gap-3 flex-wrap">
    @if($vatFiling->status === 'draft')
    <form method="POST" action="{{ route('tenant.accounting.vat-filings.submit', [$tenant->slug, $vatFiling->id]) }}">
        @csrf
        <div class="flex gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Reference</label>
                <input type="text" name="reference" placeholder="FTA reference (optional)"
                       class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Mark as Submitted
            </button>
        </div>
    </form>
    @endif

    @if($vatFiling->status === 'submitted')
    <form method="POST" action="{{ route('tenant.accounting.vat-filings.pay', [$tenant->slug, $vatFiling->id]) }}">
        @csrf
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
            Mark as Paid
        </button>
    </form>
    @endif
</div>

@if($vatFiling->submitted_at || $vatFiling->paid_at)
<div class="mt-4 bg-white rounded-xl border border-gray-200 p-5 max-w-sm">
    <h3 class="text-sm font-semibold text-gray-700 mb-2">Timeline</h3>
    <dl class="space-y-1 text-sm">
        @if($vatFiling->reference)
        <div class="flex justify-between"><dt class="text-gray-500">Reference</dt><dd class="font-mono">{{ $vatFiling->reference }}</dd></div>
        @endif
        @if($vatFiling->submitted_at)
        <div class="flex justify-between"><dt class="text-gray-500">Submitted</dt><dd>{{ $vatFiling->submitted_at->format('d M Y H:i') }}</dd></div>
        @endif
        @if($vatFiling->paid_at)
        <div class="flex justify-between"><dt class="text-gray-500">Paid</dt><dd>{{ $vatFiling->paid_at->format('d M Y H:i') }}</dd></div>
        @endif
    </dl>
</div>
@endif
@endsection
