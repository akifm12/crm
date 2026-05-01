@extends('layouts.admin')
@section('title', 'Dashboard — BlueArrow Portal')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stat cards --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Active tenants</p>
        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['tenants'] }}</p>
        <p class="mt-1 text-xs text-gray-500">Client portals running</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-amber-500 uppercase tracking-wide">Pending KYC</p>
        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['pending_kyc'] }}</p>
        <p class="mt-1 text-xs text-gray-500">Awaiting first review</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide">Under review</p>
        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['under_review'] }}</p>
        <p class="mt-1 text-xs text-gray-500">In progress</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-green-500 uppercase tracking-wide">Approved</p>
        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['approved_kyc'] }}</p>
        <p class="mt-1 text-xs text-gray-500">Total approved</p>
    </div>
</div>

{{-- Recent submissions --}}
<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Recent KYC submissions</h2>
        <a href="{{ route('kyc.submissions') }}" class="text-sm text-brand-500 hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Submitted</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($stats['recent_submissions'] as $sub)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3 font-medium text-gray-900">{{ $sub->full_name }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $sub->tenant->name }}</td>
                    <td class="px-6 py-3">
                        @php
                            $colors = [
                                'pending'      => 'bg-amber-100 text-amber-700',
                                'under_review' => 'bg-blue-100 text-blue-700',
                                'approved'     => 'bg-green-100 text-green-700',
                                'rejected'     => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$sub->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst(str_replace('_', ' ', $sub->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-500">{{ $sub->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-3">
                        <a href="{{ route('kyc.review', $sub->id) }}" class="text-brand-500 hover:underline text-xs">Review</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">No submissions yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Module quick-access --}}
<div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
    @foreach([
        ['label' => 'CRM', 'route' => 'crm.index', 'color' => 'indigo'],
        ['label' => 'Marketing', 'route' => 'marketing.index', 'color' => 'purple'],
        ['label' => 'Screening', 'route' => 'screening.index', 'color' => 'teal'],
        ['label' => 'WhatsApp', 'route' => 'whatsapp.index', 'color' => 'green'],
        ['label' => 'Accounting', 'route' => 'admin.accounting', 'color' => 'orange'],
    ] as $module)
    <a href="{{ route($module['route']) }}"
       class="bg-white rounded-xl border border-gray-200 p-4 text-center hover:border-brand-500 hover:shadow-sm transition">
        <p class="text-sm font-semibold text-gray-700">{{ $module['label'] }}</p>
        <p class="text-xs text-gray-400 mt-1">Open module</p>
    </a>
    @endforeach
</div>

@endsection
