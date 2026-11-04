@extends('layouts.tenant')
@section('title', 'Suppliers — ' . $tenant->name)
@section('page-title', 'Suppliers')
@section('page-subtitle', 'Manage vendor / supplier accounts')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Add supplier form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Add Supplier</h3>
        <form action="{{ route('tenant.accounting.suppliers.store', $tenant->slug) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Code *</label>
                <input type="text" name="code" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" placeholder="SUP-001" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                <input type="text" name="name" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">TRN</label>
                <input type="text" name="trn" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2" placeholder="100XXXXXXXXX00003">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                <input type="email" name="email" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                <input type="text" name="phone" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Payment Terms (days)</label>
                <input type="number" name="payment_terms_days" value="30" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div class="flex gap-3 text-xs">
                <label class="flex items-center gap-1"><input type="checkbox" name="is_vat_registered"> VAT Registered</label>
            </div>
            <button type="submit" class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Add Supplier
            </button>
        </form>
    </div>

    {{-- Suppliers table --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">TRN</th>
                    <th class="px-4 py-3 text-left">Contact</th>
                    <th class="px-4 py-3 text-center">VAT Reg</th>
                    <th class="px-4 py-3 w-16"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $s->code }}</td>
                    <td class="px-4 py-2.5 font-medium text-gray-800">{{ $s->name }}</td>
                    <td class="px-4 py-2.5 font-mono text-xs">{{ $s->trn ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-500">{{ $s->email ?: $s->phone ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @if($s->is_vat_registered)
                        <span class="text-xs px-1.5 py-0.5 bg-green-50 text-green-700 rounded">Yes</span>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <form action="{{ route('tenant.accounting.suppliers.destroy', [$tenant->slug, $s->id]) }}" method="POST" onsubmit="return confirm('Remove supplier?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Remove</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No suppliers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
