@extends('layouts.tenant')
@section('title', 'Invoices — ' . $tenant->name)
@section('page-title', 'Invoices')
@section('page-subtitle', 'Sale, purchase and exchange invoices')

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

<div class="flex flex-wrap gap-3 mb-5 items-center justify-between">
    <form method="GET" class="flex gap-2">
        <select name="type" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
            <option value="">All types</option>
            @foreach(['purchase'=>'Purchase','sale'=>'Sale','exchange'=>'Exchange'] as $v=>$l)
            <option value="{{ $v }}" {{ request('type')===$v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
            <option value="">All statuses</option>
            @foreach(['draft'=>'Draft','posted'=>'Posted','void'=>'Void'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')===$v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('tenant.accounting.invoices.create', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + New invoice
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Number</th>
                <th class="px-5 py-3">Client</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Total</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($invoices as $invoice)
            <tr>
                <td class="px-5 py-2.5 font-mono text-xs text-gray-700">{{ $invoice->invoice_number }}</td>
                <td class="px-5 py-2.5 text-gray-700">{{ $invoice->client->displayName() }}</td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ $invoice->invoice_type }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ $invoice->invoice_date->format('d M Y') }}</td>
                <td class="px-5 py-2.5">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        {{ $invoice->status === 'posted' ? 'bg-green-50 text-green-700' : ($invoice->status === 'void' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </td>
                <td class="px-5 py-2.5 text-right font-mono">{{ number_format($invoice->total, 2) }} {{ $invoice->currency_code }}</td>
                <td class="px-5 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.invoices.show', [$tenant->slug, $invoice->id]) }}" class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-6 text-center text-sm text-gray-400">No invoices yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($invoices->hasPages())
<div class="mt-4">{{ $invoices->links() }}</div>
@endif

@endsection
