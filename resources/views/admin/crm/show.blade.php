@extends('layouts.admin')
@section('title', $crm->company_name . ' — CRM')
@section('page-title', $crm->company_name)

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

{{-- Header card --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-700 flex-shrink-0">
                {{ strtoupper(substr($crm->company_name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $crm->company_name }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $crm->license_number ?? 'No licence' }}
                    @if($crm->legal_status) · {{ $crm->legal_status }} @endif
                    @if($crm->country_inc) · {{ $crm->country_inc }} @endif
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $crm->email ?? '' }} {{ $crm->telephone ? '· '.$crm->telephone : '' }}
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $crm->stageBadgeColor() }}">
                {{ $crm->stageLabel() }}
            </span>
            @if($crm->tenant)
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-700">
                Portal: {{ ucfirst($crm->portal_type) }}
            </span>
            @endif
            @if($crm->isLicenseExpired())
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-700">Licence expired</span>
            @elseif($crm->isLicenseExpiringSoon())
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-700">Licence expiring soon</span>
            @endif
        </div>

        {{-- Stage update --}}
        <form method="POST" action="{{ route('crm.stage', $crm->id) }}" class="flex items-center gap-2">
            @csrf @method('PATCH')
            <select name="stage" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach(['lead'=>'Lead','qualified'=>'Qualified','proposal_sent'=>'Proposal Sent','negotiation'=>'Negotiation','onboarding'=>'Onboarding','active'=>'Active','inactive'=>'Inactive'] as $v=>$l)
                <option value="{{ $v }}" {{ $crm->stage===$v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                Update stage
            </button>
        </form>
    </div>

    @if($crm->isLicenseExpired())
    <div class="mt-4 flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Trade licence expired on {{ $crm->license_expiry->format('d M Y') }}. Follow up required.
    </div>
    @endif
</div>

{{-- Tabs --}}
@php
    $docCount   = ($crm->documents ?? collect())->count();
    $noteCount  = ($crm->notes ?? collect())->count();
    $taskOpen   = ($crm->tasks ?? collect())->whereIn('status', ['pending','in_progress'])->count();
    $slaCount   = ($crm->slas ?? collect())->count();
    $qtCount    = ($crm->quotations ?? collect())->count();
@endphp

<div x-data="{ tab: 'overview' }">
    <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5 overflow-x-auto">
        @foreach([
            ['overview',   'Overview'],
            ['profile',    'Profile'],
            ['documents',  'Documents ('.$docCount.')'],
            ['notes',      'Communications ('.$noteCount.')'],
            ['tasks',      'Tasks ('.$taskOpen.' open)'],
            ['slas',       'SLAs ('.$slaCount.')'],
            ['quotations', 'Quotations ('.$qtCount.')'],
            ['portal',     'Portal'],
        ] as [$key,$label])
        <button @click="tab='{{ $key }}'"
                :class="tab==='{{ $key }}' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap flex-shrink-0">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── OVERVIEW ──────────────────────────────────────────────────────── --}}
    <div x-show="tab==='overview'">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-700">Key details</h3></div>
                <div class="p-5">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        @foreach([
                            ['Licence no.',    $crm->license_number],
                            ['Licence expiry', $crm->license_expiry?->format('d M Y')],
                            ['Legal form',     $crm->legal_status],
                            ['Country',        $crm->country_inc],
                            ['TRN',            $crm->trn],
                            ['Ejari',          $crm->ejari],
                            ['Regulator',      $crm->regulator],
                            ['Contact person', $crm->contact_person],
                            ['Email',          $crm->email],
                            ['Telephone',      $crm->telephone],
                            ['Website',        $crm->website],
                            ['Client since',   $crm->client_since?->format('d M Y')],
                        ] as [$k,$v])
                        <div><dt class="text-xs text-gray-400">{{ $k }}</dt><dd class="mt-0.5 text-gray-800">{{ $v ?? '—' }}</dd></div>
                        @endforeach
                    </dl>
                    @if($crm->address)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-xs text-gray-400 mb-1">Address</dt>
                        <dd class="text-sm text-gray-800">{{ $crm->address }}</dd>
                    </div>
                    @endif
                </div>
            </div>
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">At a glance</h3>
                    <div class="space-y-2.5">
                        @foreach([
                            ['Services',    $crm->services ? implode(', ', $crm->services) : 'None recorded', 'bg-gray-100 text-gray-600'],
                            ['Assigned to', $crm->assignee?->name ?? 'Unassigned',                            'bg-gray-100 text-gray-600'],
                            ['Active SLA',  $crm->activeSla()?->sla_reference ?? 'None',                      $crm->activeSla() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'],
                            ['Open tasks',  $taskOpen.' tasks',                                                $taskOpen > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500'],
                            ['Documents',   $docCount.' files',                                                'bg-gray-100 text-gray-600'],
                        ] as [$label,$value,$cls])
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ $label }}</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cls }}">{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                @php $latestNote = ($crm->notes ?? collect())->first(); @endphp
                @if($latestNote)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Latest note</h3>
                    <p class="text-xs text-gray-500 mb-1">
                        {{ $latestNote->author?->name ?? 'Unknown' }} · {{ $latestNote->created_at->diffForHumans() }}
                    </p>
                    <p class="text-sm text-gray-700 line-clamp-3">{{ $latestNote->body }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── PROFILE ───────────────────────────────────────────────────────── --}}
    <div x-show="tab==='profile'" x-cloak>
        <div class="space-y-5">
            @if(($crm->shareholders ?? collect())->count())
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-700">Shareholders</h3></div>
                <div class="divide-y divide-gray-100">
                    @foreach($crm->shareholders ?? [] as $sh)
                    <div class="px-5 py-3 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                        <div><span class="text-xs text-gray-400 block">Name</span>{{ $sh->shareholder_name }}</div>
                        <div><span class="text-xs text-gray-400 block">Nationality</span>{{ $sh->nationality ?? '—' }}</div>
                        <div><span class="text-xs text-gray-400 block">Passport</span>{{ $sh->passport ?? '—' }}</div>
                        <div><span class="text-xs text-gray-400 block">Passport expiry</span>
                            @if($sh->passport_expiry)
                                <span class="{{ $sh->passport_expiry->isPast() ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $sh->passport_expiry->format('d M Y') }}
                                </span>
                            @else —
                            @endif
                        </div>
                        <div><span class="text-xs text-gray-400 block">Ownership</span>
                            {{ $sh->ownership_percentage ? $sh->ownership_percentage.'%' : '—' }}
                            @if($sh->is_ubo)<span class="ml-1 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">UBO</span>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">General notes</h3>
                <p class="text-sm text-gray-700">{{ $crm->notes_text ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- ── DOCUMENTS ─────────────────────────────────────────────────────── --}}
    <div x-show="tab==='documents'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 mb-4">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Documents</h3>
                <button onclick="document.getElementById('doc-modal').classList.remove('hidden')"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Upload
                </button>
            </div>
            @if($docCount)
            <div class="divide-y divide-gray-100">
                @foreach($crm->documents ?? [] as $doc)
                <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800">{{ $doc->document_label }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $doc->file_name }} · {{ $doc->fileSizeFormatted() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($doc->expiry_date)
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                            {{ $doc->isExpired() ? 'bg-red-100 text-red-700' : ($doc->isExpiringSoon() ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $doc->isExpired() ? 'Expired' : 'Exp.' }} {{ $doc->expiry_date->format('d M Y') }}
                        </span>
                        @endif
                        <a href="{{ route('crm.documents.download', $doc->id) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">Download</a>
                        <form method="POST" action="{{ route('crm.documents.delete', $doc->id) }}" class="inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-5 py-12 text-center text-gray-400 text-sm">No documents uploaded yet.</div>
            @endif
        </div>

        {{-- Upload modal --}}
        <div id="doc-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Upload document</h3>
                    <button onclick="document.getElementById('doc-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('crm.documents.upload', $crm->id) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Document type</label>
                        <select name="document_type" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(\App\Models\CrmDocument::documentTypes() as $d)
                            <option value="{{ $d['type'] }}">{{ $d['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Document name</label>
                        <input type="text" name="document_label" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">File</label>
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.docx"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Expiry date (if applicable)</label>
                        <input type="date" name="expiry_date" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Upload</button>
                        <button type="button" onclick="document.getElementById('doc-modal').classList.add('hidden')"
                                class="flex-1 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── COMMUNICATIONS ────────────────────────────────────────────────── --}}
    <div x-show="tab==='notes'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Log interaction</h3>
                <form method="POST" action="{{ route('crm.notes.store', $crm->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select name="type" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['note'=>'Note','call'=>'Call','email'=>'Email','meeting'=>'Meeting','whatsapp'=>'WhatsApp'] as $v=>$l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Subject</label>
                        <input type="text" name="subject" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes <span class="text-red-500">*</span></label>
                        <textarea name="body" rows="4" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date / time</label>
                        <input type="datetime-local" name="interaction_at" value="{{ now()->format('Y-m-d\TH:i') }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Log interaction
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-3">
                @forelse($crm->notes ?? [] as $note)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-lg">{{ $note->typeIcon() }}</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $note->typeBadge() }}">
                                        {{ ucfirst($note->type) }}
                                    </span>
                                    @if($note->subject)
                                    <span class="text-sm font-semibold text-gray-800">{{ $note->subject }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $note->author?->name ?? 'Unknown' }} ·
                                    {{ $note->interaction_at ? $note->interaction_at->format('d M Y, H:i') : $note->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 mt-3 leading-relaxed">{{ $note->body }}</p>
                </div>
                @empty
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400 text-sm">
                    No communications logged yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── TASKS ─────────────────────────────────────────────────────────── --}}
    <div x-show="tab==='tasks'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Add task</h3>
                <form method="POST" action="{{ route('crm.tasks.store', $crm->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Task <span class="text-red-500">*</span></label>
                        <textarea name="task_description" rows="3" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Due date</label>
                        <input type="date" name="due_date" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Priority</label>
                        <select name="priority" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Assign to</label>
                        <select name="assigned_to" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Unassigned —</option>
                            @foreach($staff as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Add task
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-2">
                @forelse(($crm->tasks ?? collect())->sortBy('due_date') as $task)
                <div class="bg-white rounded-xl border {{ $task->isOverdue() ? 'border-red-200' : 'border-gray-200' }} p-4 flex items-start gap-3">
                    @if($task->status !== 'completed')
                    <form method="POST" action="{{ route('crm.tasks.complete', $task->id) }}" class="mt-0.5">
                        @csrf @method('PATCH')
                        <button type="submit" class="w-5 h-5 rounded border-2 border-gray-300 hover:border-green-500 flex-shrink-0 transition"></button>
                    </form>
                    @else
                    <div class="w-5 h-5 rounded bg-green-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 {{ $task->status === 'completed' ? 'line-through text-gray-400' : 'font-medium' }}">
                            {{ $task->task_description }}
                        </p>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $task->priorityBadge() }}">{{ ucfirst($task->priority) }}</span>
                            @if($task->due_date)
                            <span class="text-xs {{ $task->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                Due {{ $task->due_date->format('d M Y') }}
                                @if($task->isOverdue()) (overdue) @endif
                            </span>
                            @endif
                            @if($task->assignee)
                            <span class="text-xs text-gray-400">· {{ $task->assignee->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400 text-sm">No tasks yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── SLAs ──────────────────────────────────────────────────────────── --}}
    <div x-show="tab==='slas'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Create SLA from template</h3>
                <form method="POST" action="{{ route('crm.slas.store', $crm->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Template <span class="text-red-500">*</span></label>
                        <select name="sla_template_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Select template —</option>
                            @foreach($slaTemplates as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fee (AED)</label>
                        <input type="number" step="0.01" name="fee" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Start date</label>
                        <input type="date" name="start_date" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">End date</label>
                        <input type="date" name="end_date" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Create SLA
                    </button>
                </form>
                @if($slaTemplates->isEmpty())
                <p class="text-xs text-amber-600 mt-3">No SLA templates yet. Create them in Settings.</p>
                @endif
            </div>

            <div class="lg:col-span-2 space-y-3">
                @forelse($crm->slas ?? [] as $sla)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $sla->name }}</p>
                            <p class="text-xs text-gray-400">{{ $sla->sla_reference }}
                                @if($sla->start_date) · {{ $sla->start_date->format('d M Y') }} — {{ $sla->end_date?->format('d M Y') ?? 'ongoing' }} @endif
                                @if($sla->fee) · AED {{ number_format($sla->fee) }} {{ $sla->fee_frequency ? '/'.$sla->fee_frequency : '' }} @endif
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sla->statusBadge() }}">{{ ucfirst($sla->status) }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <form method="POST" action="{{ route('crm.slas.status', $sla->id) }}" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <select name="status" class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                @foreach(['draft','sent','signed','active','expired','terminated'] as $s)
                                <option value="{{ $s }}" {{ $sla->status===$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="text-xs text-blue-600 hover:underline">Update</button>
                        </form>
                        @if(!$sla->signed_copy_path)
                        <button onclick="document.getElementById('sla-upload-{{ $sla->id }}').classList.remove('hidden')"
                                class="text-xs text-purple-600 hover:underline">Upload signed copy</button>
                        @else
                        <span class="text-xs text-green-600 font-medium">✓ Signed copy uploaded</span>
                        @endif
                    </div>
                    <div id="sla-upload-{{ $sla->id }}" class="hidden mt-3 pt-3 border-t border-gray-100">
                        <form method="POST" action="{{ route('crm.slas.upload', $sla->id) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf
                            <input type="file" name="file" accept=".pdf" required
                                   class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700">
                            <input type="date" name="signed_date" value="{{ now()->toDateString() }}"
                                   class="text-xs border border-gray-200 rounded px-2 py-1 focus:outline-none">
                            <button type="submit" class="text-xs font-semibold text-white bg-blue-600 px-3 py-1 rounded-lg hover:bg-blue-700">Upload</button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400 text-sm">No SLAs yet. Create one from a template.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── QUOTATIONS ────────────────────────────────────────────────────── --}}
    <div x-show="tab==='quotations'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Create quotation</h3>
                <form method="POST" action="{{ route('crm.quotations.store', $crm->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Template <span class="text-red-500">*</span></label>
                        <select name="quotation_template_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Select template —</option>
                            @foreach($qtTemplates as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Generate quotation
                    </button>
                </form>
                @if($qtTemplates->isEmpty())
                <p class="text-xs text-amber-600 mt-3">No quotation templates yet. Create them in Settings.</p>
                @endif
            </div>

            <div class="lg:col-span-2 space-y-3">
                @forelse($crm->quotations ?? [] as $qt)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $qt->subject }}</p>
                            <p class="text-xs text-gray-400">{{ $qt->quotation_reference }}
                                · Issued {{ $qt->issued_date?->format('d M Y') }}
                                · Valid until {{ $qt->valid_until?->format('d M Y') }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $qt->statusBadge() }}">{{ ucfirst($qt->status) }}</span>
                    </div>
                    <div class="mt-3 text-sm">
                        <span class="text-gray-500">Subtotal: </span><span class="font-medium">AED {{ number_format($qt->subtotal, 2) }}</span>
                        <span class="text-gray-400 mx-2">+</span>
                        <span class="text-gray-500">VAT: </span><span class="font-medium">AED {{ number_format($qt->vat_amount, 2) }}</span>
                        <span class="text-gray-400 mx-2">=</span>
                        <span class="text-gray-700 font-bold">AED {{ number_format($qt->total_amount, 2) }}</span>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400 text-sm">No quotations yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── PORTAL ────────────────────────────────────────────────────────── --}}
    <div x-show="tab==='portal'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">

            @if($crm->tenant)
            {{-- Already converted --}}
            <div class="flex items-center gap-3 mb-5 p-3 bg-green-50 border border-green-200 rounded-lg">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold text-green-700">Portal active</p>
            </div>
            <dl class="space-y-3 text-sm mb-5">
                <div class="flex justify-between">
                    <dt class="text-gray-400">Portal URL</dt>
                    <dd><a href="{{ $crm->tenant->portalUrl() }}" target="_blank" class="text-blue-600 hover:underline font-mono text-xs">{{ $crm->tenant->portalUrl() }}</a></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Sector</dt>
                    <dd class="font-medium">{{ $crm->tenant->sectorLabel() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Status</dt>
                    <dd><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $crm->tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $crm->tenant->is_active ? 'Active' : 'Inactive' }}</span></dd>
                </div>
            </dl>
            <div class="flex gap-3">
                <a href="{{ $crm->tenant->portalUrl() }}" target="_blank"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Open portal
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <a href="{{ route('kyc.tenants.edit', $crm->tenant->id) }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Edit portal settings
                </a>
            </div>

            @else
            {{-- Convert to portal --}}
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Create compliance portal</h3>
            <p class="text-xs text-gray-400 mb-5">This will create a tenant portal for <strong>{{ $crm->company_name }}</strong> and set up their login credentials.</p>

            @if(session('success') && str_contains(session('success'), 'Portal created'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('crm.convert.portal', $crm->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Business sector <span class="text-red-500">*</span></label>
                    <select name="business_type" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">— Select sector —</option>
                        @foreach(\App\Support\SectorConfig::sectors() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Portal admin name <span class="text-red-500">*</span></label>
                    <input type="text" name="admin_name"
                           value="{{ $crm->contact_person ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Portal login email <span class="text-red-500">*</span></label>
                    <input type="email" name="admin_email" required
                           value="{{ $crm->email ?? '' }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Initial password <span class="text-red-500">*</span></label>
                    <input type="text" name="admin_password" required minlength="8"
                           value="{{ \Illuminate\Support\Str::random(10) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    <p class="text-xs text-gray-400 mt-1">Copy this before submitting — it won't be shown again.</p>
                </div>
                <button type="submit"
                        class="w-full py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Create compliance portal
                </button>
            </form>
            @endif
        </div>
    </div>

</div>
@endsection
