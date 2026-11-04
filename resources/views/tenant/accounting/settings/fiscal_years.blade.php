@extends('layouts.tenant')
@section('title', 'Fiscal Years — ' . $tenant->name)
@section('page-title', 'Fiscal Years')
@section('page-subtitle', 'Accounting period management')

@section('content')
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Start</th>
                        <th class="px-5 py-3">End</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($years as $year)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-2.5 font-medium text-gray-800">{{ $year->name }}</td>
                        <td class="px-5 py-2.5 text-gray-600">{{ $year->start_date->format('d M Y') }}</td>
                        <td class="px-5 py-2.5 text-gray-600">{{ $year->end_date->format('d M Y') }}</td>
                        <td class="px-5 py-2.5">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $year->status === 'open' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($year->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5">
                            @if($year->status === 'open')
                            <form method="POST" action="{{ route('tenant.accounting.settings.fiscal-years.close', [$tenant->slug, $year->id]) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-red-600 hover:underline"
                                        onclick="return confirm('Close this fiscal year? This cannot be easily reversed.')">
                                    Close
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('tenant.accounting.settings.fiscal-years.reopen', [$tenant->slug, $year->id]) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:underline">Reopen</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No fiscal years defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">New Fiscal Year</h3>
            <form method="POST" action="{{ route('tenant.accounting.settings.fiscal-years.store', $tenant->slug) }}" class="space-y-3">
                @csrf
                @if($errors->any())
                <div class="text-xs text-red-600 space-y-1">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
                @endif
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="FY 2025" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Start Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">End Date *</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Create
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
