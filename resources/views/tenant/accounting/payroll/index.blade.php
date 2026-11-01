@extends('layouts.tenant')
@section('title', 'Payroll — ' . $tenant->name)
@section('page-title', 'Payroll')
@section('page-subtitle', 'Payroll runs and history')

@section('content')
<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('tenant.accounting.payroll.run', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        + Run Payroll
    </a>
    <a href="{{ route('tenant.accounting.payroll.employees', $tenant->slug) }}"
       class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
        Employees
    </a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3">Run #</th>
                <th class="px-5 py-3">Period</th>
                <th class="px-5 py-3">Pay Date</th>
                <th class="px-5 py-3 text-right">Gross</th>
                <th class="px-5 py-3 text-right">Net</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($runs as $run)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5 font-mono text-xs text-blue-600">{{ $run->run_number }}</td>
                <td class="px-5 py-2.5 text-gray-700">{{ $run->period_start->format('d M') }} — {{ $run->period_end->format('d M Y') }}</td>
                <td class="px-5 py-2.5 text-gray-500">{{ $run->pay_date->format('d M Y') }}</td>
                <td class="px-5 py-2.5 text-right text-gray-800">{{ number_format($run->total_gross, 2) }}</td>
                <td class="px-5 py-2.5 text-right font-semibold text-gray-900">{{ number_format($run->total_net, 2) }}</td>
                <td class="px-5 py-2.5">
                    @php $sc = match($run->status) { 'posted'=>'bg-green-50 text-green-700','void'=>'bg-gray-100 text-gray-400',default=>'bg-amber-50 text-amber-700'}; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $sc }}">{{ ucfirst($run->status) }}</span>
                </td>
                <td class="px-5 py-2.5">
                    <a href="{{ route('tenant.accounting.payroll.show', [$tenant->slug, $run->id]) }}"
                       class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No payroll runs yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
