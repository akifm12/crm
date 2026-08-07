@extends('layouts.tenant')
@section('title', 'Chart of Accounts — ' . $tenant->name)
@section('page-title', 'Chart of Accounts')
@section('page-subtitle', 'Accounts used to post journal entries and build financial reports')

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Code</th>
                <th class="px-5 py-3">Account</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3">Normal balance</th>
                <th class="px-5 py-3 text-right">Balance (AED)</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($tree as $parent)
            <tr class="bg-gray-50">
                <td class="px-5 py-2.5 font-mono text-xs text-gray-500">{{ $parent->code }}</td>
                <td class="px-5 py-2.5 font-semibold text-gray-800">{{ $parent->name }}</td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ $parent->type }}</td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ $parent->normal_balance }}</td>
                <td class="px-5 py-2.5 text-right font-mono text-gray-500">{{ number_format($parent->balance(), 2) }}</td>
                <td class="px-5 py-2.5"></td>
            </tr>
            @foreach($accounts->where('parent_id', $parent->id) as $child)
            <tr>
                <td class="px-5 py-2.5 pl-8 font-mono text-xs text-gray-500">{{ $child->code }}</td>
                <td class="px-5 py-2.5 text-gray-700">
                    {{ $child->name }}
                    @if($child->is_system)
                    <span class="ml-1 text-[10px] font-medium text-gray-400 bg-gray-100 rounded px-1.5 py-0.5">system</span>
                    @endif
                </td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ $child->type }}</td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ $child->normal_balance }}</td>
                <td class="px-5 py-2.5 text-right font-mono text-gray-700">{{ number_format($child->balance(), 2) }}</td>
                <td class="px-5 py-2.5 text-right">
                    @unless($child->is_system)
                    <form method="POST" action="{{ route('tenant.accounting.coa.destroy', [$tenant->slug, $child->id]) }}"
                          onsubmit="return confirm('Remove this account?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Remove</button>
                    </form>
                    @endunless
                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5 mt-5 max-w-xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Add custom account</h3>
    <form method="POST" action="{{ route('tenant.accounting.coa.store', $tenant->slug) }}" class="space-y-3">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <select name="parent_id" required class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                <option value="">Parent category...</option>
                @foreach($tree as $parent)
                <option value="{{ $parent->id }}">{{ $parent->code }} — {{ $parent->name }}</option>
                @endforeach
            </select>
            <select name="normal_balance" required class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <input type="text" name="code" placeholder="Code (e.g. 1050)" required
                   class="text-sm border border-gray-200 rounded-lg px-3 py-2">
            <input type="text" name="name" placeholder="Account name" required
                   class="text-sm border border-gray-200 rounded-lg px-3 py-2">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Add account
        </button>
    </form>
</div>

@endsection
