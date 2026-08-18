@extends('layouts.tenant')
@section('title', 'Inventory Options — ' . $tenant->name)
@section('page-title', 'Inventory Options')
@section('page-subtitle', 'Manage custom form types for inventory items')

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

<div class="max-w-lg space-y-5">

    {{-- Form Types --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Item Form Types</h3>
        <p class="text-xs text-gray-400 mb-4">These appear in the "Form" dropdown when creating an inventory item. Built-in types cannot be removed.</p>

        <table class="w-full text-sm mb-4">
            <tbody class="divide-y divide-gray-100">
                @foreach($defaultFormTypes as $type)
                <tr>
                    <td class="py-2 text-gray-700">{{ ucfirst($type) }}</td>
                    <td class="py-2 text-right">
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded">Built-in</span>
                    </td>
                </tr>
                @endforeach
                @foreach($customFormTypes as $type)
                <tr>
                    <td class="py-2 text-gray-700">{{ ucfirst($type) }}</td>
                    <td class="py-2 text-right">
                        <form method="POST" action="{{ route('tenant.accounting.inventory.options.destroy', $tenant->slug) }}"
                              onsubmit="return confirm('Remove {{ addslashes(ucfirst($type)) }}?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="form_type" value="{{ $type }}">
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if(empty($customFormTypes))
                <tr>
                    <td colspan="2" class="py-3 text-xs text-gray-400 italic">No custom types added yet.</td>
                </tr>
                @endif
            </tbody>
        </table>

        <form method="POST" action="{{ route('tenant.accounting.inventory.options.store', $tenant->slug) }}"
              class="flex gap-2">
            @csrf
            <input type="text" name="form_type" required placeholder="e.g. Granule, Powder…" autocomplete="off"
                   class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg">
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 whitespace-nowrap">
                Add type
            </button>
        </form>
    </div>

    <a href="{{ route('tenant.accounting.inventory.create', $tenant->slug) }}"
       class="text-xs text-gray-400 hover:text-gray-600">← Back to new inventory item</a>
</div>

@endsection
