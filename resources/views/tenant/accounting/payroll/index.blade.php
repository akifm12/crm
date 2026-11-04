@extends('layouts.tenant')
@section('title', 'Payroll — ' . $tenant->name)
@section('page-title', 'Payroll')
@section('page-subtitle', 'Payroll runs and salary management')

@section('content')
<div class="flex items-center justify-between mb-5">
    <div class="flex gap-3">
        <a href="{{ route('tenant.accounting.payroll.employees', $tenant->slug) }}" class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50">Employees ({{ $activeCount }})</a>
        <a href="{{ route('tenant.accounting.payroll.runs.create', $tenant->slug) }}" class="px-4 py-2 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">+ New Run</a>
    </div>
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Run #</th>
                <th class="px-4 py-3 text-left">Period</th>
                <th class="px-4 py-3 text-left">Pay Date</th>
                <th class="px-4 py-3 text-right">Employees</th>
                <th class="px-4 py-3 text-right">Net Pay</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($runs as $run)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-mono text-xs font-semibold">{{ $run->run_number }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $run->pay_period_start }} – {{ $run->pay_period_end }}</td>
                <td class="px-4 py-2.5 text-xs">{{ $run->pay_date }}</td>
                <td class="px-4 py-2.5 text-right text-xs">{{ $run->employee_count }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold">{{ number_format($run->total_net_pay, 2) }}</td>
                <td class="px-4 py-2.5">
                    @php $colours = ['DRAFT'=>'bg-yellow-100 text-yellow-800','POSTED'=>'bg-green-100 text-green-800','VOID'=>'bg-red-100 text-red-800']; @endphp
                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $colours[$run->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $run->status }}</span>
                </td>
                <td class="px-4 py-2.5 text-right">
                    <a href="{{ route('tenant.accounting.payroll.show', [$tenant->slug, $run->id]) }}" class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No payroll runs yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
