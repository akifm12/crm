@extends('layouts.tenant')
@section('title', $employee->full_name . ' — ' . $tenant->name)
@section('page-title', $employee->full_name)
@section('page-subtitle', $employee->employee_number . ' · ' . $employee->position)

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500">Basic Salary</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">AED {{ number_format($employee->basic_salary, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500">Department</p>
        <p class="font-semibold text-gray-800 mt-1">{{ $employee->department?->name ?? '—' }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500">Join Date</p>
        <p class="font-semibold text-gray-800 mt-1">{{ $employee->join_date->format('d M Y') }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700">Payroll History</h3>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Run #</th>
                <th class="px-5 py-3">Period</th>
                <th class="px-5 py-3 text-right">Basic</th>
                <th class="px-5 py-3 text-right">Allowances</th>
                <th class="px-5 py-3 text-right">Deductions</th>
                <th class="px-5 py-3 text-right">Net Pay</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($employee->payrollItems()->with('payrollRun')->latest()->take(20)->get() as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5 font-mono text-xs text-blue-600">{{ $item->payrollRun?->run_number }}</td>
                <td class="px-5 py-2.5 text-gray-600">
                    {{ $item->payrollRun?->period_start->format('d M') }} — {{ $item->payrollRun?->period_end->format('d M Y') }}
                </td>
                <td class="px-5 py-2.5 text-right">{{ number_format($item->basic_salary, 2) }}</td>
                <td class="px-5 py-2.5 text-right text-green-700">{{ number_format($item->allowances, 2) }}</td>
                <td class="px-5 py-2.5 text-right text-red-600">{{ number_format($item->deductions, 2) }}</td>
                <td class="px-5 py-2.5 text-right font-semibold">{{ number_format($item->net_pay, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-6 text-center text-gray-400">No payroll runs yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
