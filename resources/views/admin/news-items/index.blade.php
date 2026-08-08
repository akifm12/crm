@extends('layouts.admin')
@section('title', 'News Feed — Content Manager')
@section('page-title', 'News Feed')
@section('page-subtitle', 'Auto-published regulatory news & BA-Digest insights — unpublish anything that isn\'t right')

@section('content')

@if (session('status'))
    <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
            <tr>
                <th class="text-left px-4 py-3">Title</th>
                <th class="text-left px-4 py-3">Category</th>
                <th class="text-left px-4 py-3">Origin</th>
                <th class="text-left px-4 py-3">Published</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($items as $item)
                <tr>
                    <td class="px-4 py-3 max-w-sm">
                        <a href="{{ route('news.show', $item) }}" target="_blank" class="font-medium text-gray-900 hover:text-blue-600 line-clamp-1">{{ $item->title }}</a>
                    </td>
                    <td class="px-4 py-3"><span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded {{ $item->categoryBadgeColor() }}">{{ $item->category }}</span></td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $item->origin === 'ai_digest' ? '✨ BA-Digest' : '📡 RSS' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $item->published_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <span class="text-[11px] font-semibold uppercase px-2 py-0.5 rounded {{ $item->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $item->is_published ? 'Live' : 'Hidden' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <form method="POST" action="{{ route('content.news.toggle', $item) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 mr-3">{{ $item->is_published ? 'Unpublish' : 'Publish' }}</button>
                        </form>
                        <form method="POST" action="{{ route('content.news.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this item?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">No news items yet — the scheduled pipeline will populate this automatically.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $items->links() }}</div>

@endsection
