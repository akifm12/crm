@extends('layouts.admin')

@section('title', 'Training Sessions')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Training Sessions</h1>
            <p class="text-sm text-gray-500 mt-0.5">All training programmes — click a session to view attendees</p>
        </div>
        <a href="{{ route('training-sessions.create') }}"
           class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Session
        </a>
    </div>

    @if($sessions->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <p class="text-gray-400 text-sm">No training sessions yet.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Training Programme</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Trainer</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Attendees</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($sessions as $session)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5 font-semibold text-gray-800">{{ $session->training_type }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ \Carbon\Carbon::parse($session->training_date)->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 text-gray-500">{{ $session->trainer ?: '—' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                {{ $session->attendee_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('training-sessions.show', ['date' => $session->training_date->format('Y-m-d'), 'type' => $session->training_type]) }}"
                               class="text-xs font-medium text-blue-600 hover:text-blue-800">
                                View →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
