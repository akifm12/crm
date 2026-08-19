@extends('layouts.tenant')
@section('title', 'Suppliers — ' . $tenant->name)
@section('page-title', 'Suppliers')
@section('page-subtitle', 'Manage your supplier list for bills')

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-3 gap-6">

    {{-- Add supplier form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 self-start">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Add supplier</h3>
        <form method="POST" action="{{ route('tenant.accounting.suppliers.store', $tenant->slug) }}">
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">VAT number</label>
                    <input type="text" name="vat_number" value="{{ old('vat_number') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Contact name</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                </div>
                <button type="submit"
                        class="w-full px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Add supplier
                </button>
            </div>
        </form>
    </div>

    {{-- Suppliers list --}}
    <div class="col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">VAT #</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3 text-right">Bills</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" x-data="{ editing: null }">
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td class="px-5 py-3">
                            <span x-show="editing !== {{ $supplier->id }}" class="text-gray-800 font-medium">{{ $supplier->name }}</span>
                            <form x-show="editing === {{ $supplier->id }}" x-cloak
                                  method="POST" action="{{ route('tenant.accounting.suppliers.update', [$tenant->slug, $supplier->id]) }}">
                                @csrf @method('PUT')
                                <div class="space-y-2">
                                    <input type="text" name="name" value="{{ $supplier->name }}" required
                                           class="w-full px-2 py-1 text-sm border border-gray-200 rounded-lg">
                                    <input type="text" name="vat_number" value="{{ $supplier->vat_number }}" placeholder="VAT #"
                                           class="w-full px-2 py-1 text-sm border border-gray-200 rounded-lg">
                                    <input type="text" name="contact_name" value="{{ $supplier->contact_name }}" placeholder="Contact"
                                           class="w-full px-2 py-1 text-sm border border-gray-200 rounded-lg">
                                    <input type="email" name="email" value="{{ $supplier->email }}" placeholder="Email"
                                           class="w-full px-2 py-1 text-sm border border-gray-200 rounded-lg">
                                    <input type="text" name="phone" value="{{ $supplier->phone }}" placeholder="Phone"
                                           class="w-full px-2 py-1 text-sm border border-gray-200 rounded-lg">
                                    <input type="text" name="address" value="{{ $supplier->address }}" placeholder="Address"
                                           class="w-full px-2 py-1 text-sm border border-gray-200 rounded-lg">
                                    <div class="flex gap-2">
                                        <button type="submit" class="px-3 py-1 text-xs font-semibold text-white bg-blue-600 rounded-lg">Save</button>
                                        <button type="button" @click="editing = null" class="px-3 py-1 text-xs text-gray-500">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $supplier->vat_number ?: '—' }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $supplier->contact_name ?: '' }}
                            @if($supplier->email)<div class="text-gray-400">{{ $supplier->email }}</div>@endif
                            @if($supplier->phone)<div class="text-gray-400">{{ $supplier->phone }}</div>@endif
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500">{{ $supplier->bills_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <button type="button" @click="editing = (editing === {{ $supplier->id }} ? null : {{ $supplier->id }})"
                                    class="text-xs text-blue-600 hover:underline mr-3">Edit</button>
                            @if($supplier->bills_count === 0)
                            <form method="POST" action="{{ route('tenant.accounting.suppliers.destroy', [$tenant->slug, $supplier->id]) }}"
                                  class="inline" onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">
                            No suppliers yet. Add your first one on the left.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
