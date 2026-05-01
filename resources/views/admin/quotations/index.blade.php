@extends('layouts.admin')
@section('title', 'Quotations')
@section('page-title', 'Quotations')
@section('page-subtitle', 'All quotations — standalone and client-linked')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div></div>
    <a href="{{ route('quotations.create') }}"
       class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New quotation
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Subject</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Client</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Total</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Valid until</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($quotations as $qt)
            @php
                $statusColors = ['draft'=>'bg-gray-100 text-gray-600','sent'=>'bg-blue-100 text-blue-700','accepted'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','expired'=>'bg-amber-100 text-amber-700'];
            @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $qt->quotation_reference }}</td>
                <td class="px-5 py-3 font-medium text-gray-900">{{ $qt->subject }}</td>
                <td class="px-5 py-3 text-gray-500 hidden md:table-cell">{{ $qt->client?->company_name ?? 'Standalone' }}</td>
                <td class="px-5 py-3 hidden lg:table-cell font-medium">
                    {{ $qt->total_amount > 0 ? 'AED '.number_format($qt->total_amount, 2) : 'TBD' }}
                </td>
                <td class="px-5 py-3 text-gray-500 hidden lg:table-cell text-xs">
                    {{ $qt->valid_until?->format('d M Y') ?? '—' }}
                    @if($qt->valid_until && $qt->valid_until->isPast() && $qt->status === 'sent')
                    <span class="text-red-500 font-semibold block">Expired</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$qt->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst($qt->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('quotations.show', $qt->id) }}" class="text-sm text-blue-600 hover:underline font-medium">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center">
                    <p class="text-gray-400 mb-3">No quotations yet.</p>
                    <a href="{{ route('quotations.create') }}" class="text-sm text-blue-600 hover:underline">Create your first quotation</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($quotations->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $quotations->links() }}</div>
    @endif
</div>

@endsection
