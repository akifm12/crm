@extends('layouts.tenant')
@section('title', 'Data Export — ' . $tenant->name)
@section('page-title', 'Data Export')
@section('page-subtitle', 'Download accounting data as CSV')

@section('content')
<div class="max-w-lg space-y-4">
    @foreach([
        ['Journals', 'All journal entries and lines', 'journals', 'indigo'],
        ['Chart of Accounts', 'Full account list with types', 'accounts', 'blue'],
        ['Suppliers', 'Supplier register', 'suppliers', 'teal'],
        ['Bills', 'All supplier bills', 'bills', 'amber'],
    ] as [$label, $desc, $key, $color])
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-between">
        <div>
            <p class="font-semibold text-gray-800">{{ $label }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
        </div>
        <a href="{{ route('tenant.accounting.data.export.'.$key, $tenant->slug) }}"
           class="px-4 py-2 text-sm font-semibold text-white bg-{{ $color }}-600 rounded-lg hover:bg-{{ $color }}-700">
            Download CSV
        </a>
    </div>
    @endforeach
</div>
@endsection
