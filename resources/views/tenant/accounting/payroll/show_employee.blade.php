@extends('layouts.tenant')
@section('title', $employee->full_name . ' — ' . $tenant->name)
@section('page-title', $employee->full_name)
@section('page-subtitle', $employee->employee_id . ' · ' . ($employee->designation ?? 'Employee'))

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 space-y-5">
        {{-- Summary --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div><p class="text-xs text-gray-500 mb-1">Basic Salary</p><p class="text-lg font-semibold">{{ number_format($employee->basic_salary, 2) }}</p></div>
                <div><p class="text-xs text-gray-500 mb-1">Gross Salary</p><p class="text-lg font-semibold text-blue-700">{{ number_format($employee->gross_salary, 2) }}</p></div>
                <div><p class="text-xs text-gray-500 mb-1">Status</p>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($employee->status) }}</span>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold mb-4">Details</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-gray-500">Department</dt><dd>{{ $employee->department?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Join Date</dt><dd>{{ $employee->join_date?->format('d M Y') }}</dd></div>
                <div><dt class="text-xs text-gray-500">Nationality</dt><dd>{{ $employee->nationality ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Emirates ID</dt><dd>{{ $employee->emirates_id ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Passport</dt><dd>{{ $employee->passport_no ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Visa Expiry</dt><dd>{{ $employee->visa_expiry?->format('d M Y') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">IBAN</dt><dd class="font-mono text-xs">{{ $employee->iban ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Bank</dt><dd>{{ $employee->bank_name ?? '—' }}</dd></div>
            </dl>
        </div>

        {{-- Edit form --}}
        <details class="bg-white rounded-xl border border-gray-200">
            <summary class="px-5 py-3 text-sm font-medium cursor-pointer">Edit Employee</summary>
            <form method="POST" action="{{ route('tenant.accounting.payroll.employees.update', [$tenant->slug, $employee->id]) }}" class="p-5 pt-0">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><label class="block text-xs text-gray-500 mb-1">First Name</label><input type="text" name="first_name" value="{{ $employee->first_name }}" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Last Name</label><input type="text" name="last_name" value="{{ $employee->last_name }}" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Designation</label><input type="text" name="designation" value="{{ $employee->designation }}" class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Join Date</label><input type="date" name="join_date" value="{{ $employee->join_date?->format('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Basic Salary</label><input type="number" step="0.01" name="basic_salary" value="{{ $employee->basic_salary }}" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Housing Allowance</label><input type="number" step="0.01" name="housing_allowance" value="{{ $employee->housing_allowance }}" class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Transport Allowance</label><input type="number" step="0.01" name="transport_allowance" value="{{ $employee->transport_allowance }}" class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-1.5">
                            <option value="active" {{ $employee->status==='active'?'selected':'' }}>Active</option>
                            <option value="inactive" {{ $employee->status==='inactive'?'selected':'' }}>Inactive</option>
                            <option value="terminated" {{ $employee->status==='terminated'?'selected':'' }}>Terminated</option>
                        </select>
                    </div>
                    <div><label class="block text-xs text-gray-500 mb-1">IBAN</label><input type="text" name="iban" value="{{ $employee->iban }}" class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Bank Name</label><input type="text" name="bank_name" value="{{ $employee->bank_name }}" class="w-full border border-gray-300 rounded-lg px-3 py-1.5"></div>
                </div>
                <div class="mt-3"><button type="submit" class="px-4 py-1.5 text-sm font-medium bg-gray-800 text-white rounded-lg">Save Changes</button></div>
            </form>
        </details>
    </div>

    {{-- Recent payroll --}}
    <div>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100"><h3 class="text-sm font-semibold">Recent Payroll</h3></div>
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500"><tr><th class="px-3 py-2 text-left">Run</th><th class="px-3 py-2 text-right">Net Pay</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentRuns as $line)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2"><a href="{{ route('tenant.accounting.payroll.show', [$tenant->slug, $line->payrollRun->id]) }}" class="text-blue-600 hover:underline">{{ $line->payrollRun->run_number }}</a><br><span class="text-gray-400">{{ $line->payrollRun->pay_date }}</span></td>
                        <td class="px-3 py-2 text-right font-mono font-semibold">{{ number_format($line->net_pay, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="px-3 py-6 text-center text-gray-400">No runs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
