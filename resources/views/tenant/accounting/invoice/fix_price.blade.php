@extends('layouts.tenant')
@section('title', 'Adjust Prices — ' . $invoice->invoice_number)
@section('page-title', 'Adjust Prices')
@section('page-subtitle', $invoice->invoice_number . ' · ' . $invoice->customer_name)

@section('content')
<div class="max-w-2xl">
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 text-sm text-amber-800">
        Update unit prices to reflect the final agreed gold price. Line totals and VAT will be recalculated automatically.
    </div>

    <form action="{{ route('tenant.accounting.invoices.fix', [$tenant->slug, $invoice->id]) }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Current Price</th>
                        <th class="px-4 py-3 text-right w-36">New Price (AED)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($lines as $line)
                    <tr>
                        <td class="px-4 py-2.5">
                            <span class="text-gray-700">{{ $line->description }}</span>
                            @if($line->item) <span class="text-xs text-gray-400 ml-1">{{ $line->item->name }}</span> @endif
                            <span class="text-xs text-gray-400 ml-1">{{ $line->quantity }} {{ $line->unit }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">{{ $line->quantity }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs text-gray-500">{{ number_format($line->unit_price, 4) }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <input type="number" name="unit_prices[{{ $line->id }}]" step="0.0001" min="0"
                                   value="{{ $line->unit_price }}"
                                   class="w-full text-sm border border-gray-300 rounded px-2 py-1 text-right font-mono">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700">Update Prices</button>
            <a href="{{ route('tenant.accounting.invoices.show', [$tenant->slug, $invoice->id]) }}" class="px-5 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
