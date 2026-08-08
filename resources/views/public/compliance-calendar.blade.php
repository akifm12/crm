@extends('layouts.public')

@section('title', 'Compliance Calendar — Blue Arrow Management Consultants')
@section('meta_description', 'Key recurring AML & DNFBP compliance deadlines for UAE businesses — goAML reporting, license renewals, sanctions re-screening and training obligations.')

@section('content')

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <span class="text-brand-600 font-semibold text-sm">📅 Stay on schedule</span>
    <h1 class="mt-2 text-4xl font-extrabold text-gray-900">Compliance Calendar</h1>
    <p class="mt-4 text-gray-600 max-w-2xl">
        Recurring AML/CFT obligations UAE DNFBPs commonly need to track. This is general guidance — always confirm
        specific dates and requirements with your MLRO or Blue Arrow's compliance team.
    </p>

    @if ($publicUser)
        <div x-data="{ showForm: false }" class="mt-10 rounded-2xl border border-brand-100 bg-brand-50 p-6 sm:p-8">
            <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-brand-800">My Deadlines</h2>
                    <p class="text-sm text-brand-700">Your own trade license, Ejari, passport and other renewal dates.</p>
                </div>
                <button @click="showForm = !showForm" class="shrink-0 inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                    + Add deadline
                </button>
            </div>

            @if (session('status'))
                <div class="mb-5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
            @endif

            <div x-show="showForm" x-cloak class="mb-6 bg-white rounded-xl border border-gray-200 p-5">
                <form method="POST" action="{{ route('account.deadlines.store') }}" class="grid sm:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 text-sm">
                            @foreach ($deadlineTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Due date</label>
                        <input type="date" name="due_date" required class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Custom label (optional)</label>
                        <input type="text" name="label" placeholder="e.g. Warehouse trade license" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes (optional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700">Save deadline</button>
                    </div>
                </form>
            </div>

            <div class="space-y-3">
                @forelse ($myDeadlines as $d)
                    @php [$badgeClass, $badgeText] = $d->badge(); @endphp
                    <div class="flex items-center justify-between gap-4 bg-white rounded-xl border border-gray-200 p-4">
                        <div class="flex items-center gap-3">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $badgeClass }}">{{ $badgeText }}</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $d->displayLabel() }}</p>
                                @if ($d->notes)<p class="text-xs text-gray-500">{{ $d->notes }}</p>@endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('account.deadlines.destroy', $d) }}" onsubmit="return confirm('Remove this deadline?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 whitespace-nowrap">Remove</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-brand-700">No personal deadlines yet — add your first one above.</p>
                @endforelse
            </div>
        </div>
    @else
        <div class="mt-10 rounded-2xl border border-dashed border-brand-200 bg-brand-50 p-8 text-center">
            <h2 class="text-lg font-bold text-brand-800">Track your own deadlines free</h2>
            <p class="mt-2 text-sm text-brand-700">Register a free account to add your trade license, Ejari, passport and other renewal dates and see them tracked here.</p>
            <a href="{{ route('account.register') }}" class="mt-4 inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-xl transition">
                Register Free →
            </a>
        </div>
    @endif

    <h2 class="mt-14 text-xl font-bold text-gray-900">General Compliance Calendar</h2>

    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('compliance-calendar') }}"
           class="px-3 py-1.5 rounded-full text-sm font-medium {{ ! $sector ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            All sectors
        </a>
        @foreach ($sectors as $slug => $label)
            <a href="{{ route('compliance-calendar', ['sector' => $slug]) }}"
               class="px-3 py-1.5 rounded-full text-sm font-medium {{ $sector === $slug ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-10 space-y-4">
        @forelse ($deadlines as $deadline)
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="sm:w-36 shrink-0">
                    @if ($deadline->next_due_date)
                        <div class="text-lg font-bold text-gray-900">{{ $deadline->next_due_date->format('d M Y') }}</div>
                    @else
                        <div class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ str($deadline->recurrence)->replace('_', ' ') }}</div>
                    @endif
                    <span class="mt-1 inline-block text-[11px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded {{ $deadline->categoryBadgeColor() }}">
                        {{ $deadline->category }}
                    </span>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ $deadline->title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $deadline->description }}</p>
                    <p class="mt-2 text-xs text-gray-400">
                        {{ ucfirst(str_replace('_', ' ', $deadline->recurrence)) }}
                        @if ($deadline->authority) · {{ $deadline->authority }} @endif
                        @if ($deadline->sector) · {{ $sectors[$deadline->sector] ?? $deadline->sector }} @endif
                    </p>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                No calendar items for this sector yet.
            </div>
        @endforelse
    </div>

    <div class="mt-12 rounded-2xl bg-gray-50 border border-gray-100 p-6 text-sm text-gray-500">
        ⚠️ This calendar provides general, indicative guidance only and is not a substitute for tenant-specific legal
        or regulatory advice. Contact our team to confirm obligations specific to your business.
    </div>
</section>

@endsection
