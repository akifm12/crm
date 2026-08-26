@extends('layouts.tenant')
@section('title', 'Statement of Accounts — ' . $tenant->name)
@section('page-title', 'Statement of Accounts')
@section('page-subtitle', $client ? $client->displayName() : 'Select a client to view their balance')

@section('content')

<form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
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
        <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
        <input type="date" name="from" value="{{ $from?->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
        <input type="date" name="as_of" value="{{ $asOf->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
    </div>
    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        View statement
    </button>
</form>

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

@if($client && $statement)

@if($clientInvoices->isEmpty())
<div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800 mb-5">
    No invoices are linked to this client yet. When creating or editing an invoice, pick this client from the
    "Select from client list" dropdown so it appears here and on their statement.
</div>
@endif

@php $exportParams = 'client_id='.$client->id.'&as_of='.$asOf->toDateString().($from ? '&from='.$from->toDateString() : ''); @endphp
<div class="flex justify-end gap-2 mb-3">
    <a href="{{ route('tenant.accounting.reports.client-statement.pdf', $tenant->slug) }}?{{ $exportParams }}" target="_blank"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
    <a href="{{ route('tenant.accounting.reports.client-statement.csv', $tenant->slug) }}?{{ $exportParams }}"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
</div>

{{-- Header cards --}}
<div class="grid grid-cols-2 gap-4 mb-5 max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Client</p>
        <p class="text-sm font-semibold text-gray-800">{{ $client->displayName() }}</p>
        @if($client->trn_number)<p class="text-xs text-gray-400">TRN: {{ $client->trn_number }}</p>@endif
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">
            Balance {{ $from ? 'from '.$from->format('d M Y').' to' : 'as of' }} {{ $asOf->format('d M Y') }}
        </p>
        <p class="text-2xl font-bold {{ $statement['balance'] > 0 ? 'text-red-600' : ($statement['balance'] < 0 ? 'text-green-600' : 'text-gray-800') }}">
            @if($statement['balance'] < 0)({{ number_format(abs($statement['balance']), 2) }})
            @else{{ number_format($statement['balance'], 2) }}
            @endif
            <span class="text-sm font-normal text-gray-400 ml-1">AED</span>
        </p>
        <p class="text-xs text-gray-400 mt-0.5">
            @if($statement['balance'] > 0) Client owes the business
            @elseif($statement['balance'] < 0) Business owes the client
            @else Settled
            @endif
        </p>
    </div>
</div>

{{-- Ledger table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="px-4 py-3 whitespace-nowrap">Date</th>
                <th class="px-4 py-3">Description</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">Debit</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">Credit</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">Balance</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @if($from)
            <tr class="bg-gray-50 text-xs font-semibold text-gray-500">
                <td class="px-4 py-2 whitespace-nowrap">{{ $from->format('d M Y') }}</td>
                <td class="px-4 py-2 italic">Opening balance</td>
                <td class="px-4 py-2"></td>
                <td class="px-4 py-2"></td>
                <td class="px-4 py-2 text-right font-mono font-semibold whitespace-nowrap
                    {{ $statement['opening_balance'] < 0 ? 'text-green-600' : ($statement['opening_balance'] > 0 ? 'text-gray-700' : 'text-gray-400') }}">
                    @if($statement['opening_balance'] < 0)({{ number_format(abs($statement['opening_balance']), 2) }})
                    @elseif($statement['opening_balance'] > 0){{ number_format($statement['opening_balance'], 2) }}
                    @else —
                    @endif
                </td>
            </tr>
            @endif
            @forelse($statement['rows'] as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ $row['date']->format('d M Y') }}</td>
                <td class="px-4 py-2.5 text-gray-700">
                    @if($row['invoice'])
                    <a href="{{ route('tenant.accounting.' . ($row['invoice']->module_type === 'real_estate' ? 're' : 'general') . '.invoices.show', [$tenant->slug, $row['invoice']->id]) }}" class="text-blue-600 hover:underline">{{ $row['description'] }}</a>
                    @else
                    {{ $row['description'] }}
                    @endif
                </td>
                <td class="px-4 py-2.5 text-right font-mono text-gray-700">
                    {{ $row['amount'] > 0 ? number_format($row['amount'], 2) : '' }}
                </td>
                <td class="px-4 py-2.5 text-right font-mono text-gray-700">
                    {{ $row['amount'] < 0 ? number_format(abs($row['amount']), 2) : '' }}
                </td>
                <td class="px-4 py-2.5 text-right font-mono font-semibold whitespace-nowrap
                    {{ $row['balance'] < 0 ? 'text-green-600' : ($row['balance'] > 0 ? 'text-gray-800' : 'text-gray-400') }}">
                    @if($row['balance'] < 0)({{ number_format(abs($row['balance']), 2) }})
                    @elseif($row['balance'] > 0){{ number_format($row['balance'], 2) }}
                    @else —
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">No posted transactions for this client yet.</td></tr>
            @endforelse
        </tbody>
        @if(collect($statement['rows'])->isNotEmpty())
        <tfoot>
            <tr class="border-t-2 border-gray-200 bg-gray-50 font-semibold text-sm">
                <td colspan="2" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Closing balance</td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3 text-right font-mono whitespace-nowrap
                    {{ $statement['balance'] < 0 ? 'text-green-600' : ($statement['balance'] > 0 ? 'text-gray-800' : 'text-gray-400') }}">
                    @if($statement['balance'] < 0)({{ number_format(abs($statement['balance']), 2) }})
                    @elseif($statement['balance'] > 0){{ number_format($statement['balance'], 2) }}
                    @else —
                    @endif
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
    </div>
</div>

@if($clientInvoices->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-5 mt-5 max-w-3xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Invoices</h3>
    <table class="w-full text-sm">
        <tbody class="divide-y divide-gray-50">
            @foreach($clientInvoices as $inv)
            <tr>
                <td class="py-2 text-gray-500">{{ $inv->invoice_date->format('d M Y') }}</td>
                <td class="py-2">
                    <a href="{{ route('tenant.accounting.' . ($inv->module_type === 'real_estate' ? 're' : 'general') . '.invoices.show', [$tenant->slug, $inv->id]) }}" class="text-blue-600 hover:underline">{{ $inv->invoice_number }}</a>
                </td>
                <td class="py-2 text-gray-500">{{ ucfirst($inv->status) }}</td>
                <td class="py-2 text-right font-mono">{{ number_format($inv->total, 2) }}</td>
                <td class="py-2 text-right font-mono text-gray-500">{{ number_format($inv->outstandingBalance(), 2) }} due</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endif

@endsection
