@extends('layouts.public')

@section('title', 'Resources — Blue Arrow Management Consultants')
@section('meta_description', 'Free downloadable AML compliance checklists, guides and reference documents for UAE DNFBPs.')

@section('content')

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <span class="text-brand-600 font-semibold text-sm">📘 Free downloads</span>
    <h1 class="mt-2 text-4xl font-extrabold text-gray-900">Compliance Resources</h1>
    <p class="mt-4 text-gray-600 max-w-2xl">
        Practical checklists and reference guides drawn from real UAE DNFBP compliance requirements. Drafts for your
        team to review and adapt to your own policies.
    </p>

    <div class="mt-8 flex flex-wrap gap-2">
        <a href="{{ route('resources') }}"
           class="px-3 py-1.5 rounded-full text-sm font-medium {{ ! $sector ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            All sectors
        </a>
        @foreach ($sectors as $slug => $label)
            <a href="{{ route('resources', ['sector' => $slug]) }}"
               class="px-3 py-1.5 rounded-full text-sm font-medium {{ $sector === $slug ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-10 grid sm:grid-cols-2 gap-5">
        @forelse ($resources as $resource)
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">📄</span>
                    <span class="text-[11px] font-semibold uppercase tracking-wide bg-brand-50 text-brand-700 px-2 py-0.5 rounded">{{ $resource->category }}</span>
                </div>
                <h3 class="mt-4 font-semibold text-gray-900">{{ $resource->title }}</h3>
                <p class="mt-2 text-sm text-gray-500 flex-1">{{ $resource->description }}</p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-gray-400">{{ $resource->fileSizeHuman() }}</span>
                    <a href="{{ route('resources.download', $resource) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        Download PDF ↓
                    </a>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">
                No resources for this sector yet.
            </div>
        @endforelse
    </div>
</section>

@endsection
