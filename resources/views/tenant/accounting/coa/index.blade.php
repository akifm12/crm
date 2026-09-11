@extends('layouts.tenant')
@section('title', 'Chart of Accounts — ' . $tenant->name)
@section('page-title', 'Chart of Accounts')
@section('page-subtitle', 'Accounts used to post journal entries and build financial reports')

@section('content')

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
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
            @foreach($flatTree as $row)
            @php $account = $row['account']; $depth = $row['depth']; @endphp
            <tr class="{{ $depth === 0 ? 'bg-gray-50' : '' }}">
                <td class="px-5 py-2.5 font-mono text-xs text-gray-500" style="padding-left: {{ 20 + $depth * 24 }}px">{{ $account->code }}</td>
                <td class="px-5 py-2.5 {{ $depth === 0 ? 'font-semibold text-gray-800' : 'text-gray-700' }}">
                    {{ $account->name }}
                    @if($account->is_system)
                    <span class="ml-1 text-[10px] font-medium text-gray-400 bg-gray-100 rounded px-1.5 py-0.5">system</span>
                    @endif
                </td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ $account->type }}</td>
                <td class="px-5 py-2.5 text-gray-500 capitalize">{{ $account->normal_balance }}</td>
                <td class="px-5 py-2.5 text-right font-mono {{ $depth === 0 ? 'text-gray-500' : 'text-gray-700' }}">{{ number_format($account->balance(), 2) }}</td>
                <td class="px-5 py-2.5 text-right">
                    @unless($account->is_system || $depth === 0)
                    <form method="POST" action="{{ route('tenant.accounting.coa.destroy', [$tenant->slug, $account->id]) }}"
                          onsubmit="return confirm('Remove this account?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Remove</button>
                    </form>
                    @endunless
                </td>
            </tr>
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
                <option value="">Parent account...</option>
                @foreach($flatTree as $row)
                <option value="{{ $row['account']->id }}">{{ str_repeat('— ', $row['depth']) }}{{ $row['account']->code }} — {{ $row['account']->name }}</option>
                @endforeach
            </select>
            <select name="normal_balance" required class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <input type="text" name="code" placeholder="Code (e.g. 3011)" required
                   class="text-sm border border-gray-200 rounded-lg px-3 py-2">
            <input type="text" name="name" placeholder="Account name" required
                   class="text-sm border border-gray-200 rounded-lg px-3 py-2">
        </div>
        <p class="text-xs text-gray-400">Pick any existing account as the parent — including a sub-account, to nest deeper (e.g. a shareholder account under Owners Equity).</p>
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Add account
        </button>
    </form>
</div>

@endsection
