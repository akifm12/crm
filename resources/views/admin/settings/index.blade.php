@extends('layouts.admin')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-subtitle', 'Manage templates, staff users and system preferences')

@section('content')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div x-data="{ tab: '{{ session('tab', 'sla') }}' }">

    {{-- Tab bar --}}
    <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5">
        @foreach([
            ['sla',        'SLA Templates'],
            ['quotations', 'Quotation Templates'],
            ['staff',      'Staff Users'],
        ] as [$key, $label])
        <button @click="tab='{{ $key }}'"
                :class="tab==='{{ $key }}' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                class="px-5 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── SLA TEMPLATES ─────────────────────────────────────────────────── --}}
    <div x-show="tab==='sla'">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700">SLA Templates</h2>
            <a href="{{ route('settings.sla.create') }}"
               class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New SLA template
            </a>
        </div>

        <div class="space-y-3">
            @forelse($slaTemplates as $t)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-800">{{ $t->name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $t->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $t->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">{{ $t->service_type }}</span>
                        </div>
                        @if($t->description)
                        <p class="text-xs text-gray-400">{{ $t->description }}</p>
                        @endif
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                            @if($t->duration) <span>Duration: {{ $t->duration }}</span> @endif
                            @if($t->default_fee) <span>Default fee: AED {{ number_format($t->default_fee) }} / {{ $t->fee_frequency }}</span> @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('settings.sla.edit', $t->id) }}"
                           class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('settings.sla.toggle', $t->id) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition border
                                           {{ $t->is_active ? 'text-red-600 border-red-200 hover:bg-red-50' : 'text-green-600 border-green-200 hover:bg-green-50' }}">
                                {{ $t->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                <p class="text-gray-400 text-sm mb-3">No SLA templates yet.</p>
                <a href="{{ route('settings.sla.create') }}" class="text-sm text-blue-600 hover:underline">Create your first template</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── QUOTATION TEMPLATES ───────────────────────────────────────────── --}}
    <div x-show="tab==='quotations'" x-cloak>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700">Quotation Templates</h2>
            <a href="{{ route('settings.qt.create') }}"
               class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New quotation template
            </a>
        </div>

        <div class="space-y-3">
            @forelse($qtTemplates as $t)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-800">{{ $t->name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $t->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $t->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full">{{ $t->service_type }}</span>
                        </div>
                        @if($t->description)
                        <p class="text-xs text-gray-400 mb-2">{{ $t->description }}</p>
                        @endif
                        <div class="space-y-1">
                            @foreach($t->line_items ?? [] as $item)
                            <p class="text-xs text-gray-500">· {{ $item['description'] }}
                                @if(!empty($item['unit_price']) && $item['unit_price'] > 0)
                                <span class="text-gray-400">— AED {{ number_format($item['unit_price'], 2) }}</span>
                                @endif
                            </p>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Valid for {{ $t->validity_days }} days</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('settings.qt.edit', $t->id) }}"
                           class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('settings.qt.toggle', $t->id) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg transition border
                                           {{ $t->is_active ? 'text-red-600 border-red-200 hover:bg-red-50' : 'text-green-600 border-green-200 hover:bg-green-50' }}">
                                {{ $t->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
                <p class="text-gray-400 text-sm mb-3">No quotation templates yet.</p>
                <a href="{{ route('settings.qt.create') }}" class="text-sm text-blue-600 hover:underline">Create your first template</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── STAFF USERS ───────────────────────────────────────────────────── --}}
    <div x-show="tab==='staff'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Add staff form --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Add staff user</h3>
                <form method="POST" action="{{ route('settings.staff.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                        <select name="role" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Add user
                    </button>
                </form>
            </div>

            {{-- Staff list --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Current staff</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($staff as $user)
                    <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $user->name }}
                                    @if($user->id === auth()->id())
                                    <span class="text-xs text-gray-400">(you)</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-700' :
                                   ($user->role === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                            @if($user->id !== auth()->id() && $user->role !== 'super_admin')
                            <form method="POST" action="{{ route('settings.staff.toggle', $user->id) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition">
                                    {{ $user->role === 'inactive' ? 'Activate' : 'Deactivate' }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
