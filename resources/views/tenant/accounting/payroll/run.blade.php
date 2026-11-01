@extends('layouts.tenant')
@section('title', 'Run Payroll — ' . $tenant->name)
@section('page-title', 'Run Payroll')
@section('page-subtitle', 'Process a new payroll period')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('tenant.accounting.payroll.process', $tenant->slug) }}" class="space-y-6">
        @csrf

        @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Payroll Period</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Period Start *</label>
                    <input type="date" name="period_start" value="{{ old('period_start') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Period End *</label>
                    <input type="date" name="period_end" value="{{ old('period_end') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pay Date *</label>
                    <input type="date" name="pay_date" value="{{ old('pay_date') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Employee Lines</h3>
                <p class="text-xs text-gray-400 mt-0.5">Adjust allowances and deductions as needed.</p>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-5 py-3">Employee</th>
                        <th class="px-5 py-3 text-right">Basic</th>
                        <th class="px-5 py-3 text-right">Allowances</th>
                        <th class="px-5 py-3 text-right">Deductions</th>
                        <th class="px-5 py-3 text-right">Net Pay</th>
                        <th class="px-5 py-3 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="payroll-lines">
                    @foreach($employees as $i => $emp)
                    <tr>
                        <td class="px-5 py-2.5">
                            <input type="hidden" name="items[{{ $i }}][employee_id]" value="{{ $emp->id }}">
                            <span class="font-medium text-gray-800">{{ $emp->full_name }}</span>
                            <span class="text-xs text-gray-400 ml-1">{{ $emp->position }}</span>
                        </td>
                        <td class="px-5 py-2.5 text-right">
                            <input type="number" name="items[{{ $i }}][basic_salary]" value="{{ old("items.$i.basic_salary", $emp->basic_salary) }}"
                                   step="0.01" min="0" required
                                   class="w-28 border border-gray-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </td>
                        <td class="px-5 py-2.5 text-right">
                            <input type="number" name="items[{{ $i }}][allowances]" value="{{ old("items.$i.allowances", 0) }}"
                                   step="0.01" min="0"
                                   class="w-28 border border-gray-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </td>
                        <td class="px-5 py-2.5 text-right">
                            <input type="number" name="items[{{ $i }}][deductions]" value="{{ old("items.$i.deductions", 0) }}"
                                   step="0.01" min="0"
                                   class="w-28 border border-gray-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </td>
                        <td class="px-5 py-2.5 text-right text-gray-500 text-xs">auto</td>
                        <td class="px-5 py-2.5 w-8">
                            <input type="checkbox" name="items[{{ $i }}][include]" value="1" checked class="rounded">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Process payroll
            </button>
            <a href="{{ route('tenant.accounting.payroll.index', $tenant->slug) }}"
               class="px-5 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</a>
        </div>
    </form>
</div>
@endsection
