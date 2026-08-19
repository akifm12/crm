@extends('layouts.tenant')

@php
    $isRE  = $moduleType === 'real_estate';
    $label = $isRE ? 'Rent Invoice' : 'Invoice';
    $rp    = $isRE ? 'tenant.accounting.re.invoices.' : 'tenant.accounting.general.invoices.';
@endphp

@section('title', $label . ' ' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    {{-- Header bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route($rp . 'index', $slug) }}"
               class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $invoice->invoice_number }}</h1>
                <p class="text-sm text-gray-500">{{ $invoice->client_name }}</p>
            </div>

            @if($invoice->status === 'draft')
                <span class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full">DRAFT</span>
            @elseif($invoice->status === 'posted')
                @if($invoice->isFullyPaid())
                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">PAID</span>
                @elseif($invoice->isOverdue())
                    <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">OVERDUE</span>
                @else
                    <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">POSTED</span>
                @endif
            @else
                <span class="bg-red-50 text-red-500 text-xs font-semibold px-3 py-1 rounded-full">VOID</span>
            @endif
        </div>

        <div class="flex gap-2 flex-wrap">
            {{-- PDF --}}
            @if($invoice->status !== 'void')
            <a href="{{ route($rp . 'pdf', [$slug, $invoice->id]) }}" target="_blank"
               class="inline-flex items-center gap-1.5 bg-gray-700 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                PDF
            </a>
            @endif

            @if($invoice->status === 'draft')
            <a href="{{ route($rp . 'edit', [$slug, $invoice->id]) }}"
               class="inline-flex items-center gap-1.5 border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                Edit
            </a>

            <form method="POST" action="{{ route($rp . 'post', [$slug, $invoice->id]) }}" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                        onclick="return confirm('Post this invoice? This will create a journal entry.')">
                    Post Invoice
                </button>
            </form>

            <form method="POST" action="{{ route($rp . 'destroy', [$slug, $invoice->id]) }}" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-600 border border-red-200 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-50 transition"
                        onclick="return confirm('Delete this draft?')">
                    Delete
                </button>
            </form>
            @endif

            @if($invoice->status === 'posted')
            <form method="POST" action="{{ route($rp . 'void', [$slug, $invoice->id]) }}" class="inline">
                @csrf
                <button type="submit" class="text-red-600 border border-red-200 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-50 transition"
                        onclick="return confirm('Void this invoice? This will reverse the journal entry.')">
                    Void
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Invoice details --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Client</div>
                <div class="font-medium text-gray-900">{{ $invoice->client_name }}</div>
                @if($invoice->client_vat_number)
                    <div class="text-gray-500 text-xs">TRN: {{ $invoice->client_vat_number }}</div>
                @endif
            </div>
            <div>
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Dates</div>
                <div class="text-gray-700">Invoice: <span class="font-medium">{{ $invoice->invoice_date->format('d M Y') }}</span></div>
                @if($invoice->due_date)
                    <div class="text-gray-700">Due: <span class="font-medium {{ $invoice->isOverdue() ? 'text-red-600' : '' }}">{{ $invoice->due_date->format('d M Y') }}</span></div>
                @endif
            </div>
            <div>
                @if($invoice->reference)
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Reference</div>
                    <div class="font-medium text-gray-900">{{ $invoice->reference }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Line items --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-5">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Description</th>
                    @if($isRE)
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Property Value</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Commission %</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Commission</th>
                    @else
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Qty</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Unit Price</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Amount</th>
                    @endif
                    <th class="text-right px-4 py-3 font-medium text-gray-600">VAT</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($invoice->lines as $line)
                <tr>
                    <td class="px-4 py-3 text-gray-900">
                        {{ $line->description }}
                        <div class="text-xs text-gray-400 mt-0.5">
                            @if($line->vat_treatment === 'standard') {{ $line->vat_rate }}% VAT
                            @elseif($line->vat_treatment === 'zero_rated') Zero Rated
                            @elseif($line->vat_treatment === 'exempt') Exempt
                            @elseif($line->vat_treatment === 'reverse_charge') Reverse Charge {{ $line->vat_rate }}%
                            @endif
                        </div>
                    </td>
                    @if($isRE)
                        <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($line->quantity, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($line->unit_price, 2) }}%</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($line->amount, 2) }}</td>
                    @else
                        <td class="px-4 py-3 text-right font-mono text-gray-700">{{ rtrim(rtrim(number_format($line->quantity, 4), '0'), '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($line->amount, 2) }}</td>
                    @endif
                    <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($line->vat_amount, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono font-medium text-gray-900">{{ number_format($line->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-gray-200 bg-gray-50">
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right text-sm text-gray-600 font-medium">{{ $isRE ? 'Total Commission' : 'Subtotal' }}</td>
                    <td class="px-4 py-2 text-right font-mono font-medium">{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right text-sm text-gray-600 font-medium">VAT</td>
                    <td class="px-4 py-2 text-right font-mono font-medium">{{ number_format($invoice->vat_total, 2) }}</td>
                </tr>
                <tr class="text-base font-bold">
                    <td colspan="5" class="px-4 py-3 text-right text-gray-900">Total (AED)</td>
                    <td class="px-4 py-3 text-right font-mono">{{ number_format($invoice->total, 2) }}</td>
                </tr>
                @if($invoice->status === 'posted')
                <tr class="text-sm">
                    <td colspan="5" class="px-4 py-2 text-right text-gray-500">Amount Paid</td>
                    <td class="px-4 py-2 text-right font-mono text-green-700">{{ number_format($invoice->amount_paid, 2) }}</td>
                </tr>
                <tr class="text-sm font-semibold {{ $invoice->outstandingBalance() > 0 ? 'text-orange-600' : 'text-green-600' }}">
                    <td colspan="5" class="px-4 py-2 text-right">Balance Due</td>
                    <td class="px-4 py-2 text-right font-mono">{{ number_format($invoice->outstandingBalance(), 2) }}</td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>

    @if($invoice->notes)
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Notes</div>
        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $invoice->notes }}</p>
    </div>
    @endif

    {{-- Payments --}}
    @if($invoice->status === 'posted')
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Payments Received</h2>

        @if($invoice->payments->isEmpty())
            <p class="text-sm text-gray-400">No payments recorded yet.</p>
        @else
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left pb-2 font-medium text-gray-600">Date</th>
                        <th class="text-left pb-2 font-medium text-gray-600">Method</th>
                        <th class="text-left pb-2 font-medium text-gray-600">Reference</th>
                        <th class="text-right pb-2 font-medium text-gray-600">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($invoice->payments as $pmt)
                    <tr>
                        <td class="py-2 text-gray-700">{{ $pmt->payment_date->format('d M Y') }}</td>
                        <td class="py-2 text-gray-600 capitalize">{{ $pmt->method ?? '—' }}</td>
                        <td class="py-2 text-gray-600">{{ $pmt->reference ?? '—' }}</td>
                        <td class="py-2 text-right font-mono font-medium">{{ number_format($pmt->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(!$invoice->isFullyPaid())
        <div class="border-t border-gray-100 pt-4">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Record Payment</h3>
            <form method="POST" action="{{ route($rp . 'payments.store', [$slug, $invoice->id]) }}"
                  class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Amount</label>
                    <input type="number" name="amount" step="0.01" min="0.01"
                           value="{{ number_format($invoice->outstandingBalance(), 2, '.', '') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Method</label>
                    <select name="method" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Account</label>
                    <select name="account" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="bank">Bank</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <label class="block text-xs text-gray-500 mb-1">Reference</label>
                    <input type="text" name="reference" placeholder="Cheque / transaction no."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                        Record
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
    @endif

    {{-- Journal entry link --}}
    @if($invoice->journalEntry)
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-sm text-gray-600 flex items-center justify-between">
        <span>Journal entry: <span class="font-mono text-gray-800">{{ $invoice->journalEntry->reference }}</span></span>
        <a href="{{ route('tenant.accounting.journal.show', [$slug, $invoice->journal_entry_id]) }}"
           class="text-blue-600 hover:underline text-xs font-medium">View →</a>
    </div>
    @endif
</div>
@endsection
