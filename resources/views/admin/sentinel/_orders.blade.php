{{-- Plan orders (self-service, routes/billing.js). $orders, $showClient --}}
@php
    $day = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('j M Y') : '—';
    $statusStyle = ['awaiting_payment' => 'bg-amber-50 text-amber-700', 'paid' => 'bg-emerald-50 text-emerald-700', 'cancelled' => 'bg-gray-100 text-gray-500'];
    $statusName = ['awaiting_payment' => 'Awaiting payment', 'paid' => 'Paid', 'cancelled' => 'Cancelled'];
@endphp
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
            <tr><th class="px-4 py-3 text-left">Order</th>@if ($showClient)<th class="px-4 py-3 text-left">Client</th>@endif
                <th class="px-4 py-3 text-left">Plan</th><th class="px-4 py-3 text-left">Total (incl. VAT)</th><th class="px-4 py-3 text-left">Billing</th>
                <th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-right"></th></tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        @forelse ($orders as $o)
            <tr>
                <td class="px-4 py-3"><p class="font-medium text-gray-900">#{{ $o['id'] }}</p><p class="text-xs text-gray-500">{{ $day($o['created_at']) }}</p></td>
                @if ($showClient)
                    <td class="px-4 py-3"><a href="{{ route('sentinel-admin.company', $o['company_id']) }}" class="text-blue-600 hover:underline">{{ $o['legal_name'] }}</a>
                        <p class="text-xs text-gray-500">{{ $o['requested_by_email'] ?? '' }}</p></td>
                @endif
                <td class="px-4 py-3">@include('admin.sentinel._plan', ['plan' => $o['plan']])</td>
                <td class="px-4 py-3 text-gray-700">AED {{ number_format($o['total_aed'], 2) }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $o['billing_email'] }}@if ($o['trn'])<br>TRN {{ $o['trn'] }}@endif @if ($o['trade_licence'])<br>Licence {{ $o['trade_licence'] }}@endif</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusStyle[$o['status']] ?? '' }}">{{ $statusName[$o['status']] ?? $o['status'] }}</span>
                    @if ($o['invoice_ref'])<p class="text-xs text-gray-500 mt-1">Invoice {{ $o['invoice_ref'] }}</p>@endif
                    @if ($o['paid_at'])<p class="text-xs text-gray-500">Paid {{ $day($o['paid_at']) }}</p>@endif</td>
                <td class="px-4 py-3 text-right">
                    @if ($o['status'] === 'awaiting_payment')
                        <form method="POST" action="{{ route('sentinel-admin.orders.paid', $o['id']) }}" class="inline-flex gap-2"
                              onsubmit="return confirm('Mark order #{{ $o['id'] }} paid? The plan switches on for a year and the client is emailed.')">
                            @csrf
                            <input name="invoice_ref" placeholder="Invoice no." class="w-28 px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                            <button class="px-3 py-1.5 text-sm font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Mark paid</button>
                        </form>
                        <form method="POST" action="{{ route('sentinel-admin.orders.cancel', $o['id']) }}" class="inline" onsubmit="return confirm('Cancel order #{{ $o['id'] }}?')">
                            @csrf <button class="ml-1 px-3 py-1.5 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No orders yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
