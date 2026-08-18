@extends('layouts.tenant')
@section('title', 'Statement of Accounts — ' . $tenant->name)
@section('page-title', 'Statement of Accounts')
@section('page-subtitle', $client ? $client->displayName() : 'Select a client to view their balance')

@section('content')

<form method="GET" class="mb-5 flex items-end gap-3">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Client</label>
        <select name="client_id" required class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white min-w-[240px]">
            <option value="">Select client...</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ $client && $client->id === $c->id ? 'selected' : '' }}>{{ $c->displayName() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">As of</label>
        <input type="date" name="as_of" value="{{ $asOf->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
    </div>
    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        View statement
    </button>
</form>

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4 flex items-center justify-between">
    <span>{{ session('success') }}</span>
    @if(session('receipt_payment_id'))
    <a href="{{ route('tenant.accounting.payments.receipt', [$tenant->slug, session('receipt_payment_id')]) }}" target="_blank"
       class="ml-4 px-3 py-1 text-xs font-semibold bg-green-700 text-white rounded hover:bg-green-800 whitespace-nowrap">
        Print Receipt
    </a>
    @endif
</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

@if($client && $statement)

<div class="flex justify-end gap-2 mb-3 max-w-3xl">
    <a href="{{ route('tenant.accounting.reports.client-statement.pdf', $tenant->slug) }}?client_id={{ $client->id }}&as_of={{ $asOf->toDateString() }}" target="_blank"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
    <a href="{{ route('tenant.accounting.reports.client-statement.csv', $tenant->slug) }}?client_id={{ $client->id }}&as_of={{ $asOf->toDateString() }}"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
</div>

<div class="grid grid-cols-3 gap-4 mb-5 max-w-3xl">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Client</p>
        <p class="text-sm font-semibold text-gray-800">{{ $client->displayName() }}</p>
        @if($client->trn_number)<p class="text-xs text-gray-400">TRN: {{ $client->trn_number }}</p>@endif
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 col-span-2">
        <p class="text-xs text-gray-400 mb-1">Net balance as of {{ $asOf->format('d M Y') }}</p>
        <p class="text-2xl font-bold {{ $statement['balance'] > 0 ? 'text-red-600' : ($statement['balance'] < 0 ? 'text-green-600' : 'text-gray-800') }}">
            {{ number_format(abs($statement['balance']), 2) }} AED
        </p>
        <p class="text-xs text-gray-400 mt-0.5">
            @if($statement['balance'] > 0) Client owes the business
            @elseif($statement['balance'] < 0) Business owes the client
            @else Settled
            @endif
        </p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden max-w-3xl">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Description</th>
                <th class="px-5 py-3 text-right">Amount</th>
                <th class="px-5 py-3 text-right">Balance</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($statement['rows'] as $row)
            <tr>
                <td class="px-5 py-2.5 text-gray-700">{{ $row['date']->format('d M Y') }}</td>
                <td class="px-5 py-2.5 text-gray-700">
                    @if($row['invoice'])
                    <a href="{{ route('tenant.accounting.invoices.show', [$tenant->slug, $row['invoice']->id]) }}" class="text-blue-600 hover:underline">{{ $row['description'] }}</a>
                    @else
                    {{ $row['description'] }}
                    @endif
                </td>
                <td class="px-5 py-2.5 text-right font-mono {{ $row['amount'] > 0 ? 'text-gray-700' : 'text-green-600' }}">
                    {{ $row['amount'] > 0 ? '+' : '' }}{{ number_format($row['amount'], 2) }}
                </td>
                <td class="px-5 py-2.5 text-right font-mono font-semibold text-gray-800">{{ number_format($row['balance'], 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-5 py-6 text-center text-sm text-gray-400">No posted transactions for this client yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($unlinkedDeposits->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-5 mt-5 max-w-3xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Unapplied deposits / margin</h3>
    <table class="w-full text-sm">
        <tbody class="divide-y divide-gray-50">
            @foreach($unlinkedDeposits as $deposit)
            <tr>
                <td class="py-2 text-gray-500">{{ $deposit->payment_date->format('d M Y') }}</td>
                <td class="py-2 text-gray-500">{{ $deposit->purpose ?: ucfirst($deposit->direction) }}</td>
                <td class="py-2 text-right font-mono">{{ number_format($deposit->amount, 2) }}</td>
                <td class="py-2 text-right">
                    <form method="POST" action="{{ route('tenant.accounting.deposits.apply', [$tenant->slug, $deposit->id]) }}" class="inline-flex items-center gap-1">
                        @csrf
                        <select name="invoice_id" required class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white">
                            <option value="">Apply to...</option>
                            @foreach($clientInvoices->whereIn('status', ['posted', 'pending_fix']) as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="text-xs text-blue-600 hover:underline">Apply</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 p-5 mt-5 max-w-3xl"
     x-data="{ hasInvoice: false, invoiceType: '' }">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Record payment / deposit</h3>
    <form method="POST" action="{{ route('tenant.accounting.clients.payments.store', [$tenant->slug, $client->id]) }}" class="flex flex-wrap gap-2 items-end">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Invoice</label>
            <select name="invoice_id" @change="hasInvoice = $event.target.value !== ''; invoiceType = $event.target.selectedOptions[0]?.dataset?.type || ''"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                <option value="">No invoice (margin/deposit)</option>
                @foreach($clientInvoices->whereIn('status', ['posted', 'pending_fix']) as $inv)
                <option value="{{ $inv->id }}" data-type="{{ $inv->invoice_type }}">{{ $inv->invoice_number }} ({{ ucfirst($inv->status === 'pending_fix' ? 'pending fix' : $inv->status) }})</option>
                @endforeach
            </select>
        </div>
        <div x-show="!hasInvoice">
            <label class="block text-xs font-medium text-gray-600 mb-1">Direction</label>
            <select name="direction" :required="!hasInvoice" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                <option value="in">Receipt from client</option>
                <option value="out">Payment to client</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
            <input type="date" name="payment_date" required value="{{ now()->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
            <input type="number" step="0.01" min="0.01" name="amount" required class="px-3 py-2 text-sm border border-gray-200 rounded-lg w-32">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Method</label>
            <select name="method" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank transfer</option>
                <option value="card">Card</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Reference</label>
            <input type="text" name="reference" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div x-show="!hasInvoice">
            <label class="block text-xs font-medium text-gray-600 mb-1">Purpose</label>
            <input type="text" name="purpose" value="Margin deposit" class="px-3 py-2 text-sm border border-gray-200 rounded-lg w-36">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Record
        </button>
    </form>
</div>

@endif

@endsection
