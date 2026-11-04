@extends('layouts.tenant')
@section('title', 'New Payroll Run — ' . $tenant->name)
@section('page-title', 'New Payroll Run')
@section('page-subtitle', 'Process salaries for the period')

@section('content')
<form method="POST" action="{{ route('tenant.accounting.payroll.runs.store', $tenant->slug) }}" id="payrollForm">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-5">
    <div><label class="block text-xs text-gray-500 mb-1">Period Start *</label><input type="date" name="pay_period_start" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-xs text-gray-500 mb-1">Period End *</label><input type="date" name="pay_period_end" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-xs text-gray-500 mb-1">Pay Date *</label><input type="date" name="pay_date" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-xs text-gray-500 mb-1">Notes</label><input type="text" name="notes" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto mb-5">
    <table class="w-full text-sm" id="linesTable">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
            <tr>
                <th class="px-3 py-3 text-left">Employee</th>
                <th class="px-3 py-3 text-right">Basic</th>
                <th class="px-3 py-3 text-right">Housing</th>
                <th class="px-3 py-3 text-right">Transport</th>
                <th class="px-3 py-3 text-right">OT Amt</th>
                <th class="px-3 py-3 text-right">Loan Ded.</th>
                <th class="px-3 py-3 text-right">Other Ded.</th>
                <th class="px-3 py-3 text-right font-semibold">Net Pay</th>
                <th class="px-3 py-3"></th>
            </tr>
        </thead>
        <tbody id="lineRows">
            @foreach($employees as $i => $emp)
            <tr class="border-t border-gray-100">
                <td class="px-3 py-2">
                    <input type="hidden" name="lines[{{ $i }}][employee_id]" value="{{ $emp->id }}">
                    <span class="font-medium text-xs">{{ $emp->full_name }}</span><br>
                    <span class="text-gray-400 text-xs">{{ $emp->employee_id }}</span>
                </td>
                <td class="px-2 py-1"><input type="number" step="0.01" name="lines[{{ $i }}][basic_salary]" value="{{ $emp->basic_salary }}" class="w-24 text-xs border border-gray-200 rounded px-2 py-1 text-right line-input" data-col="basic"></td>
                <td class="px-2 py-1"><input type="number" step="0.01" name="lines[{{ $i }}][housing_allowance]" value="{{ $emp->housing_allowance }}" class="w-24 text-xs border border-gray-200 rounded px-2 py-1 text-right line-input" data-col="housing"></td>
                <td class="px-2 py-1"><input type="number" step="0.01" name="lines[{{ $i }}][transport_allowance]" value="{{ $emp->transport_allowance }}" class="w-24 text-xs border border-gray-200 rounded px-2 py-1 text-right line-input" data-col="transport"></td>
                <td class="px-2 py-1"><input type="number" step="0.01" name="lines[{{ $i }}][overtime_amount]" value="0" class="w-20 text-xs border border-gray-200 rounded px-2 py-1 text-right line-input" data-col="overtime"></td>
                <td class="px-2 py-1"><input type="number" step="0.01" name="lines[{{ $i }}][loan_deduction]" value="0" class="w-20 text-xs border border-gray-200 rounded px-2 py-1 text-right line-input" data-col="loan"></td>
                <td class="px-2 py-1"><input type="number" step="0.01" name="lines[{{ $i }}][other_deductions]" value="0" class="w-20 text-xs border border-gray-200 rounded px-2 py-1 text-right line-input" data-col="other_ded"></td>
                <td class="px-3 py-2 text-right font-mono text-xs font-semibold net-cell">
                    <input type="hidden" name="lines[{{ $i }}][net_pay]" class="net-hidden" value="{{ $emp->gross_salary }}">
                    <span class="net-display">{{ number_format($emp->gross_salary, 2) }}</span>
                </td>
                <td class="px-2 py-1">
                    <input type="number" step="0.01" name="lines[{{ $i }}][days_absent]" value="0" placeholder="Abs.days" class="w-16 text-xs border border-gray-200 rounded px-2 py-1 text-right">
                    <input type="hidden" name="lines[{{ $i }}][other_allowances]" value="{{ $emp->other_allowances }}">
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="border-t-2 border-gray-200 bg-gray-50 font-semibold text-sm">
            <tr>
                <td colspan="7" class="px-3 py-3">Total Net Pay</td>
                <td class="px-3 py-3 text-right font-mono text-blue-700" id="grandTotal">0.00</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="flex gap-3">
    <button type="submit" class="px-6 py-2 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">Save Draft</button>
    <a href="{{ route('tenant.accounting.payroll.index', $tenant->slug) }}" class="px-6 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
</div>
</form>

<script>
function recalcRow(row) {
    const get = col => parseFloat(row.querySelector(`[data-col="${col}"]`)?.value || 0);
    const gross = get('basic') + get('housing') + get('transport') + get('overtime');
    const deduct = get('loan') + get('other_ded');
    const net = Math.max(0, gross - deduct);
    row.querySelector('.net-hidden').value = net.toFixed(2);
    row.querySelector('.net-display').textContent = net.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
    recalcTotal();
}
function recalcTotal() {
    const nets = [...document.querySelectorAll('.net-hidden')].reduce((s, el) => s + parseFloat(el.value||0), 0);
    document.getElementById('grandTotal').textContent = nets.toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2});
}
document.querySelectorAll('.line-input').forEach(el => el.addEventListener('input', () => recalcRow(el.closest('tr'))));
recalcTotal();
</script>
@endsection
