@extends('layouts.tenant')
@section('title', 'New Employee — ' . $tenant->name)
@section('page-title', 'New Employee')
@section('page-subtitle', 'Add to staff register')

@section('content')
<div class="max-w-lg">
    <form method="POST" action="{{ route('tenant.accounting.payroll.employees.store', $tenant->slug) }}" class="space-y-4">
        @csrf

        @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
            <input type="text" name="position" value="{{ old('position') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select name="department_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— None —</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Basic Salary (AED) *</label>
                <input type="number" name="basic_salary" value="{{ old('basic_salary') }}" step="0.01" min="0" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Join Date *</label>
                <input type="date" name="join_date" value="{{ old('join_date') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>

        <div class="pt-2 flex gap-3">
            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Add employee
            </button>
            <a href="{{ route('tenant.accounting.payroll.employees', $tenant->slug) }}"
               class="px-5 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</a>
        </div>
    </form>
</div>
@endsection
