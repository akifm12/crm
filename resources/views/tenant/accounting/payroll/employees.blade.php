@extends('layouts.tenant')
@section('title', 'Employees — ' . $tenant->name)
@section('page-title', 'Employees')
@section('page-subtitle', 'Staff register')

@section('content')
<div class="flex items-center justify-between mb-5">
    <a href="{{ route('tenant.accounting.payroll.index', $tenant->slug) }}" class="text-sm text-gray-500 hover:text-gray-700">← Payroll</a>
    <a href="{{ route('tenant.accounting.payroll.employees.create', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + New employee
    </a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">EMP #</th>
                <th class="px-5 py-3">Name</th>
                <th class="px-5 py-3">Position</th>
                <th class="px-5 py-3">Department</th>
                <th class="px-5 py-3 text-right">Basic Salary</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($employees as $emp)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5 font-mono text-xs text-blue-600">{{ $emp->employee_number }}</td>
                <td class="px-5 py-2.5 font-medium text-gray-800">{{ $emp->full_name }}</td>
                <td class="px-5 py-2.5 text-gray-600">{{ $emp->position }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ $emp->department?->name ?? '—' }}</td>
                <td class="px-5 py-2.5 text-right text-gray-800">{{ number_format($emp->basic_salary, 2) }}</td>
                <td class="px-5 py-2.5">
                    @php $sc = match($emp->status) {'active'=>'bg-green-50 text-green-700','terminated'=>'bg-red-50 text-red-600',default=>'bg-gray-100 text-gray-500'}; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $sc }}">{{ ucfirst($emp->status) }}</span>
                </td>
                <td class="px-5 py-2.5">
                    <a href="{{ route('tenant.accounting.payroll.employees.show', [$tenant->slug, $emp->id]) }}"
                       class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No employees yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
