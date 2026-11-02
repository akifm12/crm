@extends('layouts.tenant')
@section('title', $invoice->invoice_number . ' — Invoice')
@section('page-title', $invoice->invoice_number)
@section('page-subtitle', $invoice->invoice_date . ' · ' . $invoice->customer_name)

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Lines table --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Item / Description</th>
                        <th class="px-4 py-3 text-right w-20">Qty</th>
                        <th class="px-4 py-3 text-right w-28">Unit Price</th>
                        <th class="px-4 py-3 text-right w-20">VAT %</th>
                        <th class="px-4 py-3 text-right w-28">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($lines as $line)
                    <tr>
                        <td class="px-4 py-2.5">
                            @if($line->item)
                            <span class="text-xs font-medium text-gray-700">{{ $line->item->name }}</span>
                            @if($line->item->gold_purity) <span class="text-xs text-gray-400 ml-1">{{ $line->item->gold_purity }}‰</span> @endif
                            @if($line->gold_weight_grams) <span class="text-xs text-amber-600 ml-1">{{ $line->gold_weight_grams }}g</span> @endif
                            <br>
                            @endif
                            <span class="text-xs text-gray-500">{{ $line->description }}</span>
                            <span class="text-xs text-gray-400 ml-1">{{ $line->quantity }} {{ $line->unit }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ $line->quantity }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($line->unit_price, 4) }}</td>
                        <td class="px-4 py-2.5 text-right text-xs text-gray-500">{{ $line->vat_rate > 0 ? $line->vat_rate . '%' : '0%' }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($line->total_amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 text-xs text-gray-500">
                        <td class="px-4 py-2" colspan="4">Subtotal</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr class="bg-gray-50 text-xs text-gray-500">
                        <td class="px-4 py-2" colspan="4">Discount</td>
                        <td class="px-4 py-2 text-right font-mono text-red-500">- {{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="bg-gray-50 text-xs text-gray-500">
                        <td class="px-4 py-2" colspan="4">VAT</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($invoice->vat_amount, 2) }}</td>
                    </tr>
                    <tr class="bg-gray-50 font-semibold text-sm">
                        <td class="px-4 py-2" colspan="4">Total (AED)</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    @if($invoice->amount_paid > 0)
                    <tr class="bg-green-50 text-xs text-green-700">
                        <td class="px-4 py-2" colspan="4">Paid</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($invoice->amount_paid, 2) }}</td>
                    </tr>
                    <tr class="bg-amber-50 text-sm font-semibold text-amber-700">
                        <td class="px-4 py-2" colspan="4">Outstanding</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($invoice->amount_due, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($invoice->notes)
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-sm text-gray-600">
            <span class="text-xs font-semibold text-gray-500 uppercase mr-2">Notes</span>{{ $invoice->notes }}
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">Details</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt>
                    <dd><span class="px-2 py-0.5 rounded-full text-xs {{ $invoice->statusBadge() }}">{{ $invoice->STATUS }}</span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Invoice Date</dt><dd>{{ $invoice->invoice_date }}</dd></div>
                @if($invoice->due_date)
                <div class="flex justify-between"><dt class="text-gray-500">Due Date</dt><dd>{{ $invoice->due_date }}</dd></div>
                @endif
                @if($invoice->po_number)
                <div class="flex justify-between"><dt class="text-gray-500">PO #</dt><dd>{{ $invoice->po_number }}</dd></div>
                @endif
                @if($invoice->customer_trn)
                <div class="flex justify-between"><dt class="text-gray-500">Client TRN</dt><dd class="font-mono text-xs">{{ $invoice->customer_trn }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2">
            @if($invoice->STATUS === 'DRAFT')
            <a href="{{ route('tenant.accounting.invoices.edit', [$tenant->slug, $invoice->id]) }}" class="block w-full py-2 text-center text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Edit Invoice</a>
            <form action="{{ route('tenant.accounting.invoices.post', [$tenant->slug, $invoice->id]) }}" method="POST" onsubmit="return confirm('Post this invoice?')">
                @csrf
                <button class="w-full py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Post to Journal</button>
            </form>
            @endif

            @if(in_array($invoice->STATUS, ['APPROVED', 'PARTIAL']))
            <form action="{{ route('tenant.accounting.invoices.payments.store', [$tenant->slug, $invoice->id]) }}" method="POST" class="space-y-2">
                @csrf
                <p class="text-xs font-semibold text-gray-600">Record Payment</p>
                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5">
                <input type="number" name="amount" step="0.01" min="0.01" value="{{ number_format($invoice->amount_due, 2, '.', '') }}" placeholder="Amount" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5 font-mono">
                <select name="gl_account_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5" required>
                    <option value="">— cash/bank account —</option>
                    @foreach(\App\Models\ChartOfAccount::where('tenant_id', $tenant->id)->where('type', 'ASSET')->where('is_active', true)->orderBy('code')->get() as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} – {{ $acc->name }}</option>
                    @endforeach
                </select>
                <button class="w-full py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Record Receipt</button>
            </form>
            @endif

            @if($invoice->STATUS === 'DRAFT')
            <a href="{{ route('tenant.accounting.invoices.fix.form', [$tenant->slug, $invoice->id]) }}" class="block w-full py-2 text-center text-sm text-amber-700 border border-amber-300 rounded-lg hover:bg-amber-50">Adjust Prices</a>
            @endif

            @if(!in_array($invoice->STATUS, ['VOID', 'PAID']))
            <form action="{{ route('tenant.accounting.invoices.void', [$tenant->slug, $invoice->id]) }}" method="POST" onsubmit="return confirm('Void this invoice?')">
                @csrf
                <button class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Void</button>
            </form>
            @endif
        </div>

        <div class="flex gap-2">
            <a href="{{ route('tenant.accounting.invoices.pdf', [$tenant->slug, $invoice->id]) }}" target="_blank" class="flex-1 py-2 text-center text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">PDF</a>
            <a href="{{ route('tenant.accounting.invoices.index', $tenant->slug) }}" class="flex-1 py-2 text-center text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">← Back</a>
        </div>
    </div>
</div>
@endsection
