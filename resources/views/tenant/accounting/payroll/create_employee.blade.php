@extends('layouts.tenant')
@section('title', 'Add Employee — ' . $tenant->name)
@section('page-title', 'Add Employee')
@section('page-subtitle', 'Create a new employee record')

@section('content')
<form method="POST" action="{{ route('tenant.accounting.payroll.employees.store', $tenant->slug) }}">
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Personal Details --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Personal Details</h3>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-xs text-gray-500 mb-1">First Name *</label><input type="text" name="first_name" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Last Name *</label><input type="text" name="last_name" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Nationality</label><input type="text" name="nationality" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Date of Birth</label><input type="date" name="date_of_birth" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Gender</label>
                <select name="gender" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">—</option><option>Male</option><option>Female</option>
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Marital Status</label>
                <select name="marital_status" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">—</option><option>Single</option><option>Married</option>
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Emirates ID</label><input type="text" name="emirates_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Passport No</label><input type="text" name="passport_no" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Visa No</label><input type="text" name="visa_no" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Visa Expiry</label><input type="date" name="visa_expiry" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Labour Card No</label><input type="text" name="labour_card_no" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
        </div>
    </div>

    {{-- Employment --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Employment</h3>
        <div class="space-y-3">
            <div><label class="block text-xs text-gray-500 mb-1">Department</label>
                <select name="department_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Designation</label><input type="text" name="designation" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Join Date *</label><input type="date" name="join_date" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Payment Frequency</label>
                <select name="payment_frequency" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="monthly">Monthly</option><option value="biweekly">Bi-weekly</option><option value="weekly">Weekly</option>
                </select>
            </div>
            <div><label class="block text-xs text-gray-500 mb-1">Annual Leave (days)</label><input type="number" name="annual_leave_days" value="30" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Sick Leave (days)</label><input type="number" name="sick_leave_days" value="15" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
        </div>
    </div>

    {{-- Salary --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Salary</h3>
        <div class="space-y-3">
            <div><label class="block text-xs text-gray-500 mb-1">Basic Salary *</label><input type="number" step="0.01" name="basic_salary" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Housing Allowance</label><input type="number" step="0.01" name="housing_allowance" value="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Transport Allowance</label><input type="number" step="0.01" name="transport_allowance" value="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Other Allowances</label><input type="number" step="0.01" name="other_allowances" value="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">GL Salary Account</label>
                <select name="salary_account_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($accounts as $acct)<option value="{{ $acct->id }}">{{ $acct->code }} — {{ $acct->name }}</option>@endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Bank --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Bank / WPS</h3>
        <div class="space-y-3">
            <div><label class="block text-xs text-gray-500 mb-1">Bank Name</label><input type="text" name="bank_name" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Account No</label><input type="text" name="bank_account_no" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">IBAN</label><input type="text" name="iban" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-xs text-gray-500 mb-1">WPS Routing Code</label><input type="text" name="wps_routing_code" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Notes</h3>
        <textarea name="notes" rows="5" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>

</div>

<div class="mt-5 flex gap-3">
    <button type="submit" class="px-6 py-2 text-sm font-medium bg-gray-800 text-white rounded-lg hover:bg-gray-700">Save Employee</button>
    <a href="{{ route('tenant.accounting.payroll.employees', $tenant->slug) }}" class="px-6 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
</div>
</form>
@endsection
