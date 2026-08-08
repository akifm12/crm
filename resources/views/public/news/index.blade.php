@extends('layouts.public')

@section('title', 'Regulatory & Industry News — Blue Arrow Management Consultants')
@section('meta_description', 'Continuously updated AML, sanctions and regulatory news for UAE DNFBPs, aggregated from regulatory sources plus Blue Arrow compliance insights.')

@section('content')

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <span class="inline-flex items-center gap-1.5 text-red-600 font-semibold text-sm">🔴 Live Regulatory Feed</span>
    <h1 class="mt-2 text-4xl font-extrabold text-gray-900">Regulatory &amp; Industry News</h1>
    <p class="mt-4 text-gray-600 max-w-2xl">
        Aggregated regulatory updates plus Blue Arrow's own BA-Digest compliance insights — refreshed automatically
        so you always see the latest.
    </p>

    <div class="mt-8 flex flex-wrap gap-2">
        <a href="{{ route('news') }}"
           class="px-3 py-1.5 rounded-full text-sm font-medium {{ ! $category ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            All
        </a>
        @foreach ($categories as $slug => $label)
            <a href="{{ route('news', ['category' => $slug]) }}"
               class="px-3 py-1.5 rounded-full text-sm font-medium {{ $category === $slug ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($items as $item)
            <a href="{{ route('news.show', $item) }}" class="block rounded-2xl border border-gray-100 bg-white p-6 hover:shadow-lg transition">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded {{ $item->categoryBadgeColor() }}">{{ $item->category }}</span>
                    @if ($item->isAiGenerated())
                        <span class="text-[11px] font-medium text-purple-500">✨ BA-Digest</span>
                    @endif
                </div>
                <h3 class="font-semibold text-gray-900 leading-snug">{{ $item->title }}</h3>
                <p class="mt-2 text-sm text-gray-500 line-clamp-3">{{ $item->summary }}</p>
                <p class="mt-4 text-xs text-gray-400">📅 {{ $item->published_at->format('d M Y') }} @if($item->source_name) · {{ $item->source_name }} @endif</p>
            </a>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                No updates published yet — check back soon.
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $items->links() }}
    </div>
</section>

@endsection
