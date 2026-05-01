@extends('layouts.admin')
@section('title', 'Dashboard — BlueArrow Portal')
@section('page-title', 'Dashboard')

@section('content')

{{-- ── TOP STAT CARDS ───────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Active clients</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_clients'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['active_portals'] }} tenant portals live</p>
    </div>

    <div class="bg-white rounded-xl border {{ $stats['expired_licences'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200' }} p-5">
        <p class="text-xs font-semibold {{ $stats['expired_licences'] > 0 ? 'text-red-500' : 'text-gray-400' }} uppercase tracking-wide">Expired licences</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['expired_licences'] }}</p>
        <p class="text-xs {{ $stats['expiring_soon'] > 0 ? 'text-orange-500 font-semibold' : 'text-gray-400' }} mt-1">
            {{ $stats['expiring_soon'] }} expiring within 30 days
        </p>
    </div>

    <div class="bg-white rounded-xl border {{ $stats['overdue_tasks'] > 0 ? 'border-amber-200 bg-amber-50' : 'border-gray-200' }} p-5">
        <p class="text-xs font-semibold {{ $stats['overdue_tasks'] > 0 ? 'text-amber-500' : 'text-gray-400' }} uppercase tracking-wide">Open tasks</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['open_tasks'] }}</p>
        <p class="text-xs {{ $stats['overdue_tasks'] > 0 ? 'text-red-500 font-semibold' : 'text-gray-400' }} mt-1">
            {{ $stats['overdue_tasks'] }} overdue
        </p>
    </div>

    <div class="bg-white rounded-xl border {{ $stats['no_sla'] > 0 ? 'border-orange-200 bg-orange-50' : 'border-gray-200' }} p-5">
        <p class="text-xs font-semibold {{ $stats['no_sla'] > 0 ? 'text-orange-500' : 'text-gray-400' }} uppercase tracking-wide">Active SLAs</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['active_slas'] }}</p>
        <p class="text-xs {{ $stats['no_sla'] > 0 ? 'text-orange-500 font-semibold' : 'text-gray-400' }} mt-1">
            {{ $stats['no_sla'] }} active clients without SLA
        </p>
    </div>

</div>

{{-- ── PIPELINE BAR ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Client pipeline</h2>
    <div class="grid grid-cols-7 gap-2">
        @foreach([
            'lead'          => ['Lead',          'bg-gray-100 text-gray-600 border-gray-200'],
            'qualified'     => ['Qualified',     'bg-blue-50 text-blue-700 border-blue-200'],
            'proposal_sent' => ['Proposal Sent', 'bg-purple-50 text-purple-700 border-purple-200'],
            'negotiation'   => ['Negotiation',   'bg-amber-50 text-amber-700 border-amber-200'],
            'onboarding'    => ['Onboarding',    'bg-orange-50 text-orange-700 border-orange-200'],
            'active'        => ['Active',        'bg-green-50 text-green-700 border-green-200'],
            'inactive'      => ['Inactive',      'bg-red-50 text-red-700 border-red-200'],
        ] as $key => [$label, $cls])
        <a href="{{ route('crm.index') }}?stage={{ $key }}"
           class="border rounded-xl p-3 text-center hover:shadow-sm transition {{ $cls }}">
            <p class="text-2xl font-bold">{{ $pipeline[$key] ?? 0 }}</p>
            <p class="text-xs font-medium mt-0.5">{{ $label }}</p>
        </a>
        @endforeach
    </div>
</div>

{{-- ── ALERTS ROW ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Expired licences --}}
    <div class="bg-white rounded-xl border {{ $expired->count() > 0 ? 'border-red-200' : 'border-gray-200' }}">
        <div class="px-4 py-3 border-b {{ $expired->count() > 0 ? 'border-red-100 bg-red-50' : 'border-gray-100' }} rounded-t-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($expired->count() > 0)
                <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                @endif
                <h3 class="text-sm font-semibold {{ $expired->count() > 0 ? 'text-red-700' : 'text-gray-700' }}">
                    Expired licences
                </h3>
            </div>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $expired->count() > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $expired->count() }}
            </span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($expired as $client)
            <a href="{{ route('crm.show', $client->id) }}"
               class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 transition">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $client->company_name }}</p>
                    <p class="text-xs text-gray-400">{{ $client->license_number ?? 'No licence no.' }}</p>
                </div>
                <span class="text-xs font-semibold text-red-600 flex-shrink-0 ml-2">
                    {{ $client->license_expiry->format('d M Y') }}
                </span>
            </a>
            @empty
            <div class="px-4 py-6 text-center text-sm text-gray-400">All licences current ✓</div>
            @endforelse
        </div>
        @if($stats['expiring_soon'] > 0)
        <div class="px-4 py-2 border-t border-orange-100 bg-orange-50 rounded-b-xl">
            <p class="text-xs text-orange-600 font-medium">
                + {{ $stats['expiring_soon'] }} expiring within 30 days
            </p>
        </div>
        @endif
    </div>

    {{-- Overdue tasks --}}
    <div class="bg-white rounded-xl border {{ $overdue_tasks->count() > 0 ? 'border-amber-200' : 'border-gray-200' }}">
        <div class="px-4 py-3 border-b {{ $overdue_tasks->count() > 0 ? 'border-amber-100 bg-amber-50' : 'border-gray-100' }} rounded-t-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($overdue_tasks->count() > 0)
                <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                @endif
                <h3 class="text-sm font-semibold {{ $overdue_tasks->count() > 0 ? 'text-amber-700' : 'text-gray-700' }}">
                    Overdue tasks
                </h3>
            </div>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $overdue_tasks->count() > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $overdue_tasks->count() }}
            </span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($overdue_tasks as $task)
            <a href="{{ route('crm.show', $task->client->id) }}"
               class="flex items-start justify-between px-4 py-2.5 hover:bg-gray-50 transition gap-2">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ Str::limit($task->task_description, 40) }}</p>
                    <p class="text-xs text-gray-400">{{ $task->client?->company_name }}
                        @if($task->assignee) · {{ $task->assignee->name }} @endif
                    </p>
                </div>
                <span class="text-xs font-semibold text-red-600 flex-shrink-0">
                    {{ $task->due_date->format('d M') }}
                </span>
            </a>
            @empty
            <div class="px-4 py-6 text-center text-sm text-gray-400">No overdue tasks ✓</div>
            @endforelse
        </div>
    </div>

    {{-- Clients without SLA --}}
    <div class="bg-white rounded-xl border {{ $no_sla_clients->count() > 0 ? 'border-orange-200' : 'border-gray-200' }}">
        <div class="px-4 py-3 border-b {{ $no_sla_clients->count() > 0 ? 'border-orange-100 bg-orange-50' : 'border-gray-100' }} rounded-t-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($no_sla_clients->count() > 0)
                <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                @endif
                <h3 class="text-sm font-semibold {{ $no_sla_clients->count() > 0 ? 'text-orange-700' : 'text-gray-700' }}">
                    Active clients — no SLA
                </h3>
            </div>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $no_sla_clients->count() > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $no_sla_clients->count() }}
            </span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($no_sla_clients as $client)
            <a href="{{ route('crm.show', $client->id) }}"
               class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 transition">
                <p class="text-sm font-medium text-gray-800 truncate">{{ $client->company_name }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 flex-shrink-0 ml-2">
                    {{ ucfirst($client->stage) }}
                </span>
            </a>
            @empty
            <div class="px-4 py-6 text-center text-sm text-gray-400">All active clients have SLAs ✓</div>
            @endforelse
        </div>
    </div>

</div>

{{-- ── EXPIRING SOON + RECENT ACTIVITY ─────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Expiring within 30 days --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Licences expiring soon</h3>
            <span class="text-xs text-gray-400">Next 30 days</span>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($expiring as $client)
            <a href="{{ route('crm.show', $client->id) }}"
               class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 transition">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $client->company_name }}</p>
                    <p class="text-xs text-gray-400">{{ $client->license_number ?? '—' }}</p>
                </div>
                <span class="text-xs font-semibold text-orange-600 flex-shrink-0 ml-2">
                    {{ $client->license_expiry->format('d M Y') }}
                    <span class="text-gray-400 font-normal block text-right">{{ $client->license_expiry->diffForHumans() }}</span>
                </span>
            </a>
            @empty
            <div class="px-4 py-6 text-center text-sm text-gray-400">No licences expiring soon ✓</div>
            @endforelse
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Recent activity</h3>
            <a href="{{ route('crm.index') }}" class="text-xs text-blue-600 hover:underline">View all clients</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recent_activity as $note)
            <a href="{{ route('crm.show', $note->client->id) }}"
               class="flex items-start gap-3 px-4 py-2.5 hover:bg-gray-50 transition">
                <span class="text-base flex-shrink-0 mt-0.5">{{ $note->typeIcon() }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-800 truncate">
                        <span class="font-medium">{{ $note->client?->company_name }}</span>
                        @if($note->subject) — {{ $note->subject }} @endif
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $note->author?->name ?? 'Unknown' }} · {{ $note->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="text-xs px-1.5 py-0.5 rounded {{ $note->typeBadge() }} flex-shrink-0">
                    {{ ucfirst($note->type) }}
                </span>
            </a>
            @empty
            <div class="px-4 py-6 text-center text-sm text-gray-400">No activity yet.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
