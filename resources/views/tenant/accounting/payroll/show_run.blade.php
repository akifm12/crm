@extends('layouts.tenant')
@section('title', $run->run_number . ' — ' . $tenant->name)
@section('page-title', 'Payroll Run ' . $run->run_number)
@section('page-subtitle', $run->period_start->format('d M') . ' — ' . $run->period_end->format('d M Y'))

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Gross</p>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($run->total_gross, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Deductions</p>
        <p class="text-xl font-bold text-red-600 mt-1">{{ number_format($run->total_deductions, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Net Pay</p>
        <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($run->total_net, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500">Pay Date</p>
        <p class="font-semibold text-gray-800 mt-1">{{ $run->pay_date->format('d M Y') }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Employee</th>
                <th class="px-5 py-3 text-right">Basic</th>
                <th class="px-5 py-3 text-right">Allowances</th>
                <th class="px-5 py-3 text-right">Deductions</th>
                <th class="px-5 py-3 text-right">Net Pay</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($run->items as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5">
                    <a href="{{ route('tenant.accounting.payroll.employees.show', [$tenant->slug, $item->employee_id]) }}"
                       class="font-medium text-blue-600 hover:underline">{{ $item->employee?->full_name }}</a>
                    <span class="text-xs text-gray-400 ml-1">{{ $item->employee?->position }}</span>
                </td>
                <td class="px-5 py-2.5 text-right">{{ number_format($item->basic_salary, 2) }}</td>
                <td class="px-5 py-2.5 text-right text-green-700">{{ number_format($item->allowances, 2) }}</td>
                <td class="px-5 py-2.5 text-right text-red-600">{{ number_format($item->deductions, 2) }}</td>
                <td class="px-5 py-2.5 text-right font-semibold">{{ number_format($item->net_pay, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 border-t border-gray-200">
            <tr>
                <td class="px-5 py-3 font-semibold text-gray-700">Totals</td>
                <td class="px-5 py-3 text-right font-semibold">{{ number_format($run->total_gross, 2) }}</td>
                <td class="px-5 py-3 text-right font-semibold text-green-700">
                    {{ number_format($run->items->sum('allowances'), 2) }}
                </td>
                <td class="px-5 py-3 text-right font-semibold text-red-600">{{ number_format($run->total_deductions, 2) }}</td>
                <td class="px-5 py-3 text-right font-bold text-gray-900">{{ number_format($run->total_net, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
