@extends('layouts.admin')
@section('title', 'Tasks')
@section('page-title', 'Tasks')
@section('page-subtitle', 'All pending tasks grouped by client')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

{{-- Summary bar --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @php
        $totalTasks    = $clients->sum(fn($c) => $c->tasks->count());
        $overdueTasks  = $clients->sum(fn($c) => $c->tasks->filter->isOverdue()->count());
        $highPriority  = $clients->sum(fn($c) => $c->tasks->where('priority', 'high')->count());
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Total pending</p>
        <p class="text-2xl font-bold text-gray-800">{{ $totalTasks }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">Overdue</p>
        <p class="text-2xl font-bold {{ $overdueTasks > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $overdueTasks }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">High priority</p>
        <p class="text-2xl font-bold {{ $highPriority > 0 ? 'text-amber-600' : 'text-gray-800' }}">{{ $highPriority }}</p>
    </div>
</div>

{{-- Client list --}}
@if($clients->isEmpty())
<div class="bg-white rounded-xl border border-dashed border-gray-300 p-16 text-center">
    <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
    </svg>
    <p class="text-gray-400 text-sm">No pending tasks across any clients.</p>
</div>
@else
<div class="space-y-2">
    @foreach($clients as $client)
    @php
        $pending  = $client->tasks;
        $overdue  = $pending->filter->isOverdue()->count();
        $high     = $pending->where('priority', 'high')->count();
    @endphp
    <div x-data="{ open: false }" class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Client row (clickable header) --}}
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 transition">
            <div class="flex items-center gap-3">
                <svg :class="open ? 'rotate-90' : ''"
                     class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <div>
                    <a href="{{ route('crm.show', $client->id) }}"
                       @click.stop
                       class="text-sm font-semibold text-gray-800 hover:text-blue-600 transition">
                        {{ $client->company_name }}
                    </a>
                    @if($client->contact_person)
                    <p class="text-xs text-gray-400">{{ $client->contact_person }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($overdue > 0)
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">{{ $overdue }} overdue</span>
                @endif
                @if($high > 0)
                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $high }} high</span>
                @endif
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 font-medium">
                    {{ $pending->count() }} {{ Str::plural('task', $pending->count()) }}
                </span>
            </div>
        </button>

        {{-- Tasks table (expandable) --}}
        <div x-show="open" x-transition class="border-t border-gray-100">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-2.5 text-left font-medium">Task</th>
                        <th class="px-4 py-2.5 text-left font-medium">Due</th>
                        <th class="px-4 py-2.5 text-left font-medium">Priority</th>
                        <th class="px-4 py-2.5 text-left font-medium">Assigned to</th>
                        <th class="px-4 py-2.5 text-left font-medium">Status</th>
                        <th class="px-4 py-2.5 text-left font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($pending as $task)
                    <tr class="{{ $task->isOverdue() ? 'bg-red-50' : '' }} hover:bg-gray-50 transition">
                        <td class="px-5 py-3 text-gray-700 max-w-xs">{{ $task->task_description }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($task->due_date)
                                <span class="{{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                    {{ $task->due_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $task->priorityBadge() }}">
                                {{ ucfirst($task->priority ?? 'low') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $task->assignee?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $task->status === 'in_progress' ? 'In progress' : 'Pending' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('crm.tasks.complete', $task->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="text-xs text-gray-400 hover:text-green-600 transition whitespace-nowrap">
                                    Mark done
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
