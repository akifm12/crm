@extends('layouts.admin')
@section('title', $quotation->quotation_reference . ' — Quotation')
@section('page-title', $quotation->quotation_reference)
@section('page-subtitle', $quotation->subject)

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

{{-- Header card --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-lg font-bold text-gray-900">{{ $quotation->subject }}</p>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $quotation->client?->company_name ?? 'Standalone quotation' }}
                · Issued {{ $quotation->issued_date?->format('d M Y') ?? '—' }}
                · Valid until {{ $quotation->valid_until?->format('d M Y') ?? '—' }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @php
                $statusColors = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-700','accepted'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','expired'=>'bg-amber-100 text-amber-700'];
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $statusColors[$quotation->status] ?? 'bg-gray-100 text-gray-500' }}">
                {{ ucfirst($quotation->status) }}
            </span>

            {{-- Download button --}}
            <button onclick="document.getElementById('download-modal').classList.remove('hidden')"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Word doc
            </button>

            {{-- Status update --}}
            <form method="POST" action="{{ route('quotations.status', $quotation->id) }}" class="flex items-center gap-2">
                @csrf @method('PATCH')
                <select name="status" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['draft','sent','accepted','rejected','expired'] as $s)
                    <option value="{{ $s }}" {{ $quotation->status===$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Update</button>
            </form>
        </div>
    </div>
</div>

{{-- Line items --}}
<div class="bg-white rounded-xl border border-gray-200 mb-5">
    <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-700">Line items</h3></div>
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-8">#</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-20">Qty</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-36">Unit price</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-36">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($quotation->line_items ?? [] as $i => $item)
            <tr class="{{ $i % 2 === 1 ? 'bg-gray-50' : '' }}">
                <td class="px-5 py-3 text-gray-400">{{ $i+1 }}</td>
                <td class="px-5 py-3 text-gray-800">{{ $item['description'] }}</td>
                <td class="px-5 py-3 text-right text-gray-600">{{ $item['qty'] ?? 1 }}</td>
                <td class="px-5 py-3 text-right text-gray-600">
                    {{ ($item['unit_price'] ?? 0) > 0 ? 'AED '.number_format($item['unit_price'], 2) : 'As agreed' }}
                </td>
                <td class="px-5 py-3 text-right text-gray-800 font-medium">
                    @php $amt = ($item['qty'] ?? 1) * ($item['unit_price'] ?? 0); @endphp
                    {{ $amt > 0 ? 'AED '.number_format($amt, 2) : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="border-t border-gray-200">
                <td colspan="4" class="px-5 py-2 text-right text-sm text-gray-500">Subtotal</td>
                <td class="px-5 py-2 text-right text-sm font-medium">AED {{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="px-5 py-2 text-right text-sm text-gray-500">VAT (5%)</td>
                <td class="px-5 py-2 text-right text-sm font-medium">AED {{ number_format($quotation->vat_amount, 2) }}</td>
            </tr>
            <tr class="bg-gray-50">
                <td colspan="4" class="px-5 py-3 text-right text-sm font-bold text-gray-800">Total</td>
                <td class="px-5 py-3 text-right text-sm font-bold text-gray-900">AED {{ number_format($quotation->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Terms --}}
@if($quotation->terms)
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Terms & conditions</h3>
    <div class="text-sm text-gray-600 space-y-1">
        @foreach(explode("\n", $quotation->terms) as $line)
        @if(trim($line))<p>{{ $line }}</p>@endif
        @endforeach
    </div>
</div>
@endif

{{-- Download modal — confirm recipient details for standalone --}}
<div id="download-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Download quotation</h3>
            <button onclick="document.getElementById('download-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        @if($quotation->client)
            {{-- Linked to CRM client — direct download --}}
            <p class="text-sm text-gray-600 mb-4">
                This quotation is for <strong>{{ $quotation->client->company_name }}</strong>. Click below to generate and download the Word document.
            </p>
            <a href="{{ route('quotations.download', $quotation->id) }}"
               class="block w-full text-center py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                Generate & download
            </a>
        @else
            {{-- Standalone — confirm recipient --}}
            <p class="text-sm text-gray-500 mb-4">Confirm the recipient details for the document:</p>
            <form method="GET" action="{{ route('quotations.download', $quotation->id) }}" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company / person name</label>
                    <input type="text" name="recipient_name" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="recipient_email"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <input type="text" name="recipient_address"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                    Generate & download
                </button>
            </form>
        @endif
    </div>
</div>

@endsection
