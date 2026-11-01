@extends('layouts.tenant')
@section('title', $bill->bill_number . ' — Bills')
@section('page-title', $bill->bill_number)
@section('page-subtitle', $bill->vendor_name . ' · ' . $bill->bill_date)

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Account</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-right w-20">Qty</th>
                        <th class="px-4 py-3 text-right w-28">Price</th>
                        <th class="px-4 py-3 text-right w-20">VAT</th>
                        <th class="px-4 py-3 text-right w-28">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($lines as $line)
                    <tr>
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-500">
                            {{ $line->account->code ?? '—' }}
                            <span class="text-gray-700 font-sans ml-1">{{ $line->account->name ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 text-xs">{{ $line->description }}</td>
                        <td class="px-4 py-2.5 text-right text-xs">{{ $line->quantity }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="px-4 py-2.5 text-right text-xs">{{ number_format($line->vat_amount, 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold">{{ number_format($line->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-200 text-sm">
                    <tr><td colspan="5" class="px-4 py-2 text-right text-gray-500">Subtotal</td><td class="px-4 py-2 text-right font-mono">{{ number_format($bill->subtotal, 2) }}</td></tr>
                    @if($bill->total_vat_amount > 0)
                    <tr><td colspan="5" class="px-4 py-2 text-right text-gray-500">VAT</td><td class="px-4 py-2 text-right font-mono">{{ number_format($bill->total_vat_amount, 2) }}</td></tr>
                    @endif
                    <tr class="bg-gray-50"><td colspan="5" class="px-4 py-2 text-right font-bold">Total (AED)</td><td class="px-4 py-2 text-right font-bold font-mono text-lg">{{ number_format($bill->total_amount, 2) }}</td></tr>
                    @if($bill->amount_paid > 0)
                    <tr><td colspan="5" class="px-4 py-2 text-right text-gray-500">Paid</td><td class="px-4 py-2 text-right font-mono text-green-700">{{ number_format($bill->amount_paid, 2) }}</td></tr>
                    <tr><td colspan="5" class="px-4 py-2 text-right font-bold text-red-700">Due</td><td class="px-4 py-2 text-right font-bold font-mono text-red-700">{{ number_format($bill->amount_due, 2) }}</td></tr>
                    @endif
                </tfoot>
            </table>
        </div>

        @if($bill->notes)
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-sm text-gray-600">
            <span class="font-medium text-gray-700">Notes:</span> {{ $bill->notes }}
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Status</h4>
            <span class="text-sm px-3 py-1 rounded-full
                @if($bill->status==='PAID') bg-green-50 text-green-700
                @elseif($bill->status==='APPROVED') bg-blue-50 text-blue-700
                @elseif($bill->status==='PARTIAL') bg-yellow-50 text-yellow-700
                @elseif($bill->status==='DRAFT') bg-gray-100 text-gray-600
                @else bg-red-50 text-red-600 @endif">
                {{ $bill->status }}
            </span>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2">
            @if($bill->status === 'DRAFT')
            <form action="{{ route('tenant.accounting.bills.post', [$tenant->slug, $bill->id]) }}" method="POST">
                @csrf
                <button class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Approve & Post</button>
            </form>
            <a href="{{ route('tenant.accounting.bills.edit', [$tenant->slug, $bill->id]) }}" class="block text-center py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Edit</a>
            @endif

            @if(in_array($bill->status, ['APPROVED','PARTIAL']) && $bill->amount_due > 0)
            <div x-data="{ open: false }">
                <button type="button" onclick="document.getElementById('payment-form').classList.toggle('hidden')" class="w-full py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">Record Payment</button>
                <div id="payment-form" class="hidden mt-3 border border-gray-200 rounded-lg p-4">
                    <form action="{{ route('tenant.accounting.bills.payments.store', [$tenant->slug, $bill->id]) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Date</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full text-sm border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Amount (AED)</label>
                            <input type="number" name="amount" step="0.01" value="{{ $bill->amount_due }}" class="w-full text-sm border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">GL Account (Cash/Bank)</label>
                            <input type="number" name="gl_account_id" placeholder="Account ID" class="w-full text-sm border border-gray-300 rounded px-2 py-1.5">
                        </div>
                        <button type="submit" class="w-full py-1.5 text-sm text-white bg-green-600 rounded hover:bg-green-700">Record</button>
                    </form>
                </div>
            </div>
            @endif

            @if($bill->status !== 'VOID')
            <form action="{{ route('tenant.accounting.bills.void', [$tenant->slug, $bill->id]) }}" method="POST" onsubmit="return confirm('Void this bill?')">
                @csrf
                <button class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Void Bill</button>
            </form>
            @endif
        </div>

        <a href="{{ route('tenant.accounting.bills.index', $tenant->slug) }}" class="block text-center py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
            ← Back to Bills
        </a>
    </div>
</div>
@endsection
