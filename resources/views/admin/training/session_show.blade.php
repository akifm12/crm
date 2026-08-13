@extends('layouts.admin')

@section('title', $type . ' — ' . \Carbon\Carbon::parse($date)->format('d M Y'))

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <a href="{{ route('training-sessions.index') }}" class="text-xs text-gray-400 hover:text-gray-600 mb-1 inline-block">← All Sessions</a>
            <h1 class="text-xl font-bold text-gray-800">{{ $type }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($date)->format('d F Y') }} &middot; {{ $attendees->count() }} {{ Str::plural('attendee', $attendees->count()) }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('training-sessions.log', ['date' => $date, 'type' => $type]) }}"
               class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Log
            </a>

            <form method="POST" action="{{ route('training-sessions.email', ['date' => $date, 'type' => $type]) }}"
                  onsubmit="return confirm('Send certificate links to all client companies in this session?')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email Clients
                </button>
            </form>

            <button x-data @click="$dispatch('open-add-attendee')"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Attendee
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Attendees table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-8">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Company</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Expiry</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Certificate</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Client Link</th>
                    <th class="px-5 py-3 w-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($attendees as $i => $tr)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-5 py-3.5 font-medium text-gray-800">{{ $tr->employee_name }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $tr->employee_role ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $tr->client->company_name }}</td>
                    <td class="px-5 py-3.5 text-center">
                        @if($tr->status === 'completed')
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-green-50 text-green-700 rounded-full">Completed</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-amber-50 text-amber-700 rounded-full">Pending</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-gray-500 text-xs">
                        @if($tr->expiry_date)
                            <span class="{{ $tr->isExpired() ? 'text-red-600 font-semibold' : ($tr->isExpiringSoon() ? 'text-amber-600' : '') }}">
                                {{ $tr->expiry_date->format('d M Y') }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <a href="{{ route('crm.trainings.generate', $tr->id) }}"
                           class="inline-flex items-center gap-1 text-xs text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 font-medium px-2 py-1 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Generate
                        </a>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($tr->public_token)
                        <button x-data
                                @click="navigator.clipboard.writeText('{{ route('certificate.public.show', $tr->public_token) }}'); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy Link', 2000)"
                                class="text-xs text-indigo-600 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 font-medium px-2 py-1 rounded-lg transition">
                            Copy Link
                        </button>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <form method="POST" action="{{ route('crm.trainings.delete', $tr->id) }}"
                              onsubmit="return confirm('Remove this attendee?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-300 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-8 text-center text-gray-400 text-sm">No attendees yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- Add Attendee Modal --}}
<div x-data="{ open: false }" @open-add-attendee.window="open = true"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none">

    <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 z-10">
        <h2 class="text-base font-bold text-gray-800 mb-4">Add Attendee</h2>

        <form method="POST" action="{{ route('training-sessions.add-attendee', ['date' => $date, 'type' => $type]) }}" class="space-y-3">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Employee Name <span class="text-red-500">*</span></label>
                <input type="text" name="employee_name" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role / Position</label>
                    <input type="text" name="employee_role" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ID Number</label>
                    <input type="text" name="employee_id_number" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Company <span class="text-red-500">*</span></label>
                <select name="crm_client_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Select company…</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Signatory Name</label>
                    <input type="text" name="signatory_name" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Signatory Title</label>
                    <input type="text" name="signatory_title" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Certificate Template</label>
                <select name="certificate_template" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="1">Classic — Gold &amp; Navy</option>
                    <option value="2">Modern — Blue &amp; White</option>
                    <option value="3">Prestige — Dark</option>
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Add Attendee
                </button>
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
