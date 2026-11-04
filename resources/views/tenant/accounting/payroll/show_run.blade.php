@extends('layouts.tenant')
@section('title', $run->run_number . ' — ' . $tenant->name)
@section('page-title', $run->run_number)
@section('page-subtitle', $run->pay_period_start . ' to ' . $run->pay_period_end)

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
@endif

{{-- Summary cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
    @foreach([['Employees',$run->employee_count,''],['Basic + Allowances',number_format($run->total_basic_salary+$run->total_allowances,2),'font-mono'],['Deductions',number_format($run->total_deductions,2),'font-mono text-red-600'],['Net Pay',number_format($run->total_net_pay,2),'font-mono text-blue-700 text-lg']] as [$label,$val,$cls])
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">{{ $label }}</p>
        <p class="font-semibold {{ $cls }}">{{ $val }}</p>
    </div>
    @endforeach
</div>

{{-- Status + actions --}}
<div class="flex items-center gap-3 mb-5">
    @php $colours = ['DRAFT'=>'bg-yellow-100 text-yellow-800','POSTED'=>'bg-green-100 text-green-800','VOID'=>'bg-red-100 text-red-800']; @endphp
    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $colours[$run->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $run->status }}</span>

    @if($run->status === 'DRAFT')
    <details class="relative">
        <summary class="px-4 py-1.5 text-sm font-medium bg-green-700 text-white rounded-lg cursor-pointer list-none">Post to Ledger ▾</summary>
        <div class="absolute top-full mt-1 left-0 bg-white border border-gray-200 rounded-xl shadow-lg p-4 z-10 w-80">
            <form method="POST" action="{{ route('tenant.accounting.payroll.post', [$tenant->slug, $run->id]) }}">
                @csrf
                <p class="text-xs text-gray-500 mb-3">Select the GL accounts for the journal entry.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Wage Expense Account (DR)</label>
                        <select name="wage_expense_account_id" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5">
                            @foreach(\App\Models\ChartOfAccount::where('tenant_id',app('tenant')->id)->where('account_type','EXPENSE')->orderBy('code')->get() as $a)
                            <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Bank / Salary Payable Account (CR)</label>
                        <select name="salary_payable_account_id" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5">
                            @foreach(\App\Models\ChartOfAccount::where('tenant_id',app('tenant')->id)->whereIn('account_type',['ASSET','LIABILITY'])->orderBy('code')->get() as $a)
                            <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-3 w-full py-1.5 text-sm font-medium bg-green-700 text-white rounded-lg">Confirm Post</button>
            </form>
        </div>
    </details>

    <form method="POST" action="{{ route('tenant.accounting.payroll.void', [$tenant->slug, $run->id]) }}" onsubmit="return confirm('Void this payroll run?')">
        @csrf
        <button class="px-4 py-1.5 text-sm border border-red-300 text-red-700 rounded-lg hover:bg-red-50">Void</button>
    </form>
    @endif

    @if($run->journalEntry)
    <a href="{{ route('tenant.accounting.journal.show', [$tenant->slug, $run->journalEntry->id]) }}" class="text-sm text-blue-600 hover:underline">View Journal Entry →</a>
    @endif
</div>

{{-- Lines table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-4 py-3 text-left">Employee</th>
                <th class="px-4 py-3 text-right">Basic</th>
                <th class="px-4 py-3 text-right">Allowances</th>
                <th class="px-4 py-3 text-right">Overtime</th>
                <th class="px-4 py-3 text-right">Deductions</th>
                <th class="px-4 py-3 text-right font-semibold">Net Pay</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($run->lines as $line)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5">
                    <a href="{{ route('tenant.accounting.payroll.employees.show', [$tenant->slug, $line->employee->id]) }}" class="font-medium hover:underline">{{ $line->employee->full_name }}</a>
                    <span class="text-gray-400 text-xs ml-2">{{ $line->employee->employee_id }}</span>
                </td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($line->basic_salary, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($line->housing_allowance + $line->transport_allowance + $line->other_allowances, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs">{{ number_format($line->overtime_amount, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs text-red-600">{{ number_format($line->total_deductions, 2) }}</td>
                <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold">{{ number_format($line->net_pay, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t-2 border-gray-200 bg-gray-50 font-semibold text-sm">
            <tr>
                <td colspan="5" class="px-4 py-3">Total Net Pay</td>
                <td class="px-4 py-3 text-right font-mono text-blue-700">{{ number_format($run->total_net_pay, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
