@extends('layouts.tenant')
@section('title', 'Employees — ' . $tenant->name)
@section('page-title', 'Employees')
@section('page-subtitle', 'Staff records and salary setup')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="flex gap-3">
        <a href="{{ route('tenant.accounting.payroll.index', $tenant->slug) }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">← Payroll Runs</a>
        <a href="{{ route('tenant.accounting.payroll.employees.create', $tenant->slug) }}" class="px-4 py-2 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">+ Add Employee</a>
    </div>
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
@endif

{{-- Department quick-add --}}
<details class="mb-4 bg-white border border-gray-200 rounded-xl">
    <summary class="px-4 py-3 text-sm font-medium cursor-pointer">Manage Departments</summary>
    <div class="px-4 pb-4">
        <form method="POST" action="{{ route('tenant.accounting.payroll.departments.store', $tenant->slug) }}" class="flex gap-3 mt-3">
            @csrf
            <input type="text" name="code" placeholder="Code (e.g. OPS)" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 w-28" required>
            <input type="text" name="name" placeholder="Department name" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 flex-1" required>
            <button class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Add</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($departments as $dept)
            <span class="px-3 py-1 bg-gray-100 rounded-full text-xs">{{ $dept->code }} — {{ $dept->name }}</span>
            @endforeach
        </div>
    </div>
</details>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">ID</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Department</th>
                <th class="px-4 py-3 text-left">Designation</th>
                <th class="px-4 py-3 text-right">Basic</th>
                <th class="px-4 py-3 text-right">Gross</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($employees as $emp)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-mono text-xs text-gray-500">{{ $emp->employee_id }}</td>
                <td class="px-4 py-2.5 font-medium">{{ $emp->full_name }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $emp->department?->name ?? '—' }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $emp->designation ?? '—' }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($emp->basic_salary, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold">{{ number_format($emp->gross_salary, 2) }}</td>
                <td class="px-4 py-2.5">
                    <span class="px-2 py-0.5 rounded text-xs {{ $emp->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">{{ $emp->status }}</span>
                </td>
                <td class="px-4 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.payroll.employees.show', [$tenant->slug, $emp->id]) }}" class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No employees yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
