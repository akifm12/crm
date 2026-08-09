@extends('layouts.admin')
@section('title', 'Public Users — Content Manager')
@section('page-title', 'Public Users')
@section('page-subtitle', 'Free accounts registered via the website and mobile app')

@section('content')
<div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-gray-700">{{ $users->count() }} account(s)</h2>
    </div>

    <div class="space-y-3">
        @forelse ($users as $user)
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900 text-sm">{{ $user->name }}</span>
                        @if ($user->subscribed_to_updates)
                            <span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">Subscribed</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $user->email }}</p>
                    <p class="mt-1 text-xs text-gray-400">
                        Joined {{ $user->created_at->format('d M Y') }}
                        · {{ $user->deadlines_count }} tracked deadline{{ $user->deadlines_count === 1 ? '' : 's' }}
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <form method="POST" action="{{ route('content.public-users.destroy', $user) }}" onsubmit="return confirm('Remove this account? This also deletes their tracked deadlines.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 whitespace-nowrap">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">No public accounts yet.</p>
        @endforelse
    </div>
</div>
@endsection
