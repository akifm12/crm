@extends('layouts.public')

@section('title', $news->title . ' — Blue Arrow Management Consultants')
@section('meta_description', \Illuminate\Support\Str::limit($news->summary, 155))

@section('content')

<article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <a href="{{ route('news') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">← Back to News</a>

    <div class="mt-6 flex items-center gap-2">
        <span class="text-[11px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded {{ $news->categoryBadgeColor() }}">{{ $news->category }}</span>
        @if ($news->isAiGenerated())
            <span class="text-[11px] font-medium text-purple-500">✨ BA-Digest</span>
        @endif
    </div>

    <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight">{{ $news->title }}</h1>

    <p class="mt-3 text-sm text-gray-400">
        📅 {{ $news->published_at->format('d M Y') }}
        @if ($news->source_name) · Source: {{ $news->source_name }} @endif
    </p>

    <p class="mt-8 text-lg text-gray-700 leading-relaxed">{{ $news->summary }}</p>

    @if ($news->body)
        <div class="mt-6 prose prose-gray max-w-none text-gray-600 leading-relaxed whitespace-pre-line">{{ $news->body }}</div>
    @endif

    @if ($news->source_url)
        <a href="{{ $news->source_url }}" target="_blank" rel="noopener noreferrer" class="mt-8 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
            Read original source →
        </a>
    @endif

    @if ($news->isAiGenerated())
        <div class="mt-10 rounded-2xl bg-purple-50 border border-purple-100 p-5 text-sm text-purple-800">
            ✨ This item is a BA-Digest — AI-assisted compliance commentary drafted by Blue Arrow. Always verify against primary
            regulatory sources before acting on it.
        </div>
    @endif

    @if ($related->isNotEmpty())
        <div class="mt-16 border-t border-gray-100 pt-10">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Related updates</h2>
            <div class="grid sm:grid-cols-3 gap-5">
                @foreach ($related as $item)
                    <a href="{{ route('news.show', $item) }}" class="block rounded-2xl border border-gray-100 bg-white p-5 hover:shadow-lg transition">
                        <h3 class="font-semibold text-gray-900 text-sm leading-snug">{{ $item->title }}</h3>
                        <p class="mt-2 text-xs text-gray-400">{{ $item->published_at->format('d M Y') }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</article>

@endsection
