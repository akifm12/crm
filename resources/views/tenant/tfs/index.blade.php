@extends('layouts.tenant')
@section('title', 'TFS Submissions — ' . $tenant->name)
@section('page-title', 'TFS Submissions')
@section('page-subtitle', 'UAE Terror Finance Screening survey submissions')

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5 text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="flex items-center justify-between mb-6">
    <div></div>
    <a href="{{ route('tenant.tfs.create', $tenant->slug) }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New TFS Submission
    </a>
</div>

@if($submissions->isEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-gray-500 text-sm">No TFS submissions yet.</p>
    <a href="{{ route('tenant.tfs.create', $tenant->slug) }}" class="mt-3 inline-block text-blue-600 text-sm hover:underline">Create your first submission</a>
</div>
@else
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Title</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Names</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Response</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Links</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($submissions as $sub)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900 max-w-xs">
                    <a href="{{ route('tenant.tfs.show', [$tenant->slug, $sub->id]) }}" class="hover:text-blue-600">
                        {{ Str::limit($sub->title, 60) }}
                    </a>
                </td>
                <td class="px-4 py-3 text-gray-500">
                    {{ count($sub->alerted_names ?? []) ?: '—' }}
                </td>
                <td class="px-4 py-3">
                    @if($sub->recommended_response || $sub->selected_response)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sub->responseBadge() }}">
                        {{ $sub->responseLabel() }}
                    </span>
                    @else
                    <span class="text-gray-400 text-xs">Pending screening</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-500">
                    {{ $sub->submittedCount() }}/{{ $sub->totalUrls() }}
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sub->statusBadge() }}">
                        {{ ucfirst($sub->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                    {{ $sub->created_at->format('d M Y') }}
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('tenant.tfs.show', [$tenant->slug, $sub->id]) }}"
                       class="text-blue-600 hover:underline text-xs font-medium">View →</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($submissions->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $submissions->links() }}
    </div>
    @endif
</div>
@endif

@endsection
