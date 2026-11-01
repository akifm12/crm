@extends('layouts.tenant')
@section('title', 'New Bank Account — ' . $tenant->name)
@section('page-title', 'New Bank Account')
@section('page-subtitle', 'Add a bank or cash account')

@section('content')
<div class="max-w-lg">
    <form method="POST" action="{{ route('tenant.accounting.banking.store', $tenant->slug) }}" class="space-y-4">
        @csrf

        @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account Name *</label>
            <input type="text" name="account_name" value="{{ old('account_name') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. ADIB Current Account">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name *</label>
            <input type="text" name="bank_name" value="{{ old('bank_name') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. Abu Dhabi Islamic Bank">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
            <input type="text" name="account_number" value="{{ old('account_number') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Currency *</label>
            <select name="currency" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="AED" {{ old('currency','AED') === 'AED' ? 'selected' : '' }}>AED</option>
                <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Link to Chart of Account</label>
            <select name="chart_of_account_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— None —</option>
                @foreach($glAccounts as $acc)
                <option value="{{ $acc->id }}" {{ old('chart_of_account_id') == $acc->id ? 'selected' : '' }}>
                    {{ $acc->code }} — {{ $acc->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="pt-2 flex gap-3">
            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Create account
            </button>
            <a href="{{ route('tenant.accounting.banking.index', $tenant->slug) }}"
               class="px-5 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
