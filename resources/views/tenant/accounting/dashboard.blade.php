@extends('layouts.tenant')

@section('title', 'Accounting')
@section('page-title', 'Bullion Accounting')
@section('page-subtitle', 'Invoicing, ledger, trial balance & balance sheet for ' . $tenant->name)

@section('content')

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <p class="text-sm text-gray-600 mb-4">
        The Bullion Accounting module is enabled for this portal. Journal, invoicing and reports will appear
        here as they are built out.
    </p>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('tenant.accounting.coa.index', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Chart of Accounts
        </a>
        <a href="{{ route('tenant.accounting.journal.index', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            Journal
        </a>
        <a href="{{ route('tenant.accounting.inventory.index', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            Inventory
        </a>
        <a href="{{ route('tenant.accounting.invoices.index', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            Invoices
        </a>
        <a href="{{ route('tenant.accounting.reports.trial-balance', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            Trial Balance
        </a>
        <a href="{{ route('tenant.accounting.reports.balance-sheet', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            Balance Sheet
        </a>
        <a href="{{ route('tenant.accounting.reports.income-statement', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            Income Statement
        </a>
        <a href="{{ route('tenant.accounting.reports.vat-summary', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            VAT Summary
        </a>
        <a href="{{ route('tenant.accounting.reports.client-statement', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            Client Statement
        </a>
        <a href="{{ route('tenant.accounting.reports.client-balances', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
            Client Balances
        </a>
    </div>
</div>

@endsection
