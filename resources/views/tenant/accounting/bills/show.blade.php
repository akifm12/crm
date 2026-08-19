@extends('layouts.tenant')
@section('title', $bill->bill_number . ' — ' . $tenant->name)
@section('page-title', $bill->bill_number)
@section('page-subtitle', $bill->supplier_name)

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

{{-- Status bar --}}
<div class="flex items-center gap-2 mb-5 flex-wrap">
    <a href="{{ route('tenant.accounting.bills.pdf', [$tenant->slug, $bill->id]) }}" target="_blank"
       class="px-4 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Print / Download PDF
    </a>
    @php
        $statusClass = match($bill->status) {
            'posted' => $bill->isFullyPaid() ? 'bg-green-50 text-green-700' : ($bill->isOverdue() ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-700'),
            'void'   => 'bg-gray-100 text-gray-400',
            default  => 'bg-amber-50 text-amber-700',
        };
        $statusLabel = match(true) {
            $bill->status === 'draft'                             => 'Draft',
            $bill->status === 'void'                             => 'Void',
            $bill->status === 'posted' && $bill->isFullyPaid()   => 'Paid',
            $bill->status === 'posted' && $bill->isOverdue()     => 'Overdue',
            default                                              => 'Posted',
        };
    @endphp
    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>

    @if($bill->status === 'draft')
    <a href="{{ route('tenant.accounting.bills.edit', [$tenant->slug, $bill->id]) }}"
       class="px-4 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Edit draft
    </a>
    <form method="POST" action="{{ route('tenant.accounting.bills.post', [$tenant->slug, $bill->id]) }}"
          onsubmit="return confirm('Post this bill? This will create a journal entry.')">
        @csrf
        <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Post bill
        </button>
    </form>
    <form method="POST" action="{{ route('tenant.accounting.bills.destroy', [$tenant->slug, $bill->id]) }}"
          onsubmit="return confirm('Delete this draft?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-3 py-1.5 text-xs text-red-500 hover:text-red-700">Delete draft</button>
    </form>
    @endif

    @if($bill->status === 'posted')
    @if($bill->journalEntry)
    <a href="{{ route('tenant.accounting.journal.show', [$tenant->slug, $bill->journalEntry->id]) }}"
       class="px-4 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        View journal entry
    </a>
    @endif
    <form method="POST" action="{{ route('tenant.accounting.bills.void', [$tenant->slug, $bill->id]) }}"
          onsubmit="return confirm('Void this bill? The journal entry will be reversed. This cannot be undone.')"
          class="inline-flex items-center gap-1">
        @csrf
        <input type="text" name="reason" placeholder="Reason (optional)" class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg w-32">
        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50">
            Void
        </button>
    </form>
    @endif
</div>

{{-- Bill details --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5 max-w-3xl">
    <div class="grid grid-cols-4 gap-4 text-sm mb-4">
        <div><p class="text-xs text-gray-400 mb-0.5">Bill date</p><p class="text-gray-800">{{ $bill->bill_date->format('d M Y') }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Due date</p><p class="text-gray-800">{{ $bill->due_date ? $bill->due_date->format('d M Y') : '—' }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Supplier ref</p><p class="text-gray-800">{{ $bill->reference ?: '—' }}</p></div>
        @if($bill->supplier_vat_number)
        <div><p class="text-xs text-gray-400 mb-0.5">Supplier VAT #</p><p class="text-gray-800">{{ $bill->supplier_vat_number }}</p></div>
        @endif
        @if($bill->status === 'posted')
        <div><p class="text-xs text-gray-400 mb-0.5">Amount paid</p><p class="text-gray-800">{{ number_format($bill->amount_paid, 2) }}</p></div>
        <div><p class="text-xs text-gray-400 mb-0.5">Outstanding</p><p class="text-gray-800 {{ $bill->isOverdue() ? 'text-red-600 font-semibold' : '' }}">{{ number_format($bill->outstandingBalance(), 2) }}</p></div>
        @endif
    </div>

    <table class="w-full text-sm mb-3">
        <thead>
            <tr class="text-left text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                <th class="py-2">Description</th>
                <th class="py-2">Category</th>
                <th class="py-2 text-right">Amount</th>
                <th class="py-2 text-right">VAT</th>
                <th class="py-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($bill->lines as $line)
            <tr>
                <td class="py-2">{{ $line->description }}</td>
                <td class="py-2 text-gray-500 text-xs">{{ App\Models\Bill::CATEGORIES[$line->category] ?? $line->category }}</td>
                <td class="py-2 text-right font-mono">{{ number_format($line->amount, 2) }}</td>
                <td class="py-2 text-right font-mono text-gray-500">
                    {{ number_format($line->vat_amount, 2) }}
                    <span class="text-xs text-gray-400">({{ str_replace('_', ' ', $line->vat_treatment) }})</span>
                </td>
                <td class="py-2 text-right font-mono">{{ number_format($line->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-100 text-sm">
                <td colspan="4" class="pt-2 text-right text-gray-500 pr-2">Subtotal</td>
                <td class="pt-2 text-right font-mono">{{ number_format($bill->subtotal, 2) }}</td>
            </tr>
            <tr class="text-sm">
                <td colspan="4" class="text-right text-gray-500 pr-2">VAT (Input)</td>
                <td class="text-right font-mono">{{ number_format($bill->vat_total, 2) }}</td>
            </tr>
            <tr class="text-sm font-semibold text-gray-800">
                <td colspan="4" class="text-right pr-2">Total</td>
                <td class="text-right font-mono">{{ number_format($bill->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($bill->notes)
    <p class="text-xs text-gray-500 mt-3">{{ $bill->notes }}</p>
    @endif
</div>

{{-- Payments --}}
@if($bill->status === 'posted')
<div class="bg-white rounded-xl border border-gray-200 p-5 max-w-3xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Payments</h3>

    <table class="w-full text-sm mb-4">
        <tbody class="divide-y divide-gray-50">
            @forelse($bill->payments as $payment)
            <tr>
                <td class="py-2 text-gray-500">{{ $payment->payment_date->format('d M Y') }}</td>
                <td class="py-2 text-gray-500">{{ str_replace('_', ' ', $payment->method ?? '—') }}</td>
                <td class="py-2 text-gray-500">{{ $payment->reference ?: '—' }}</td>
                <td class="py-2 text-right font-mono">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-3 text-center text-gray-400">No payments recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(! $bill->isFullyPaid())
    <form method="POST" action="{{ route('tenant.accounting.bills.payments.store', [$tenant->slug, $bill->id]) }}"
          class="flex flex-wrap gap-2 items-end">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
            <input type="date" name="payment_date" required value="{{ now()->toDateString() }}"
                   class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
            <input type="number" step="0.01" min="0.01" name="amount" required
                   value="{{ $bill->outstandingBalance() }}"
                   class="px-3 py-2 text-sm border border-gray-200 rounded-lg w-32">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Paid from</label>
            <select name="account" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="bank">Bank</option>
                <option value="cash">Cash</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Method</label>
            <select name="method" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="bank_transfer">Bank transfer</option>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Reference</label>
            <input type="text" name="reference" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Record payment
        </button>
    </form>
    @endif
</div>
@endif

@endsection
