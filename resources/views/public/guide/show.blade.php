@extends('layouts.public')

@section('title', $t['title'] . ' — UAE AML Compliance Guide, Step ' . $t['step'] . ' — Blue Arrow')
@section('meta_description', $t['oneliner'])

@section('content')

{{-- ── PROGRESS + HEADER ────────────────────────────────────────────────── --}}
<section class="bg-gradient-to-b from-brand-50 to-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-12">
        <nav class="flex items-center justify-between gap-4 text-sm">
            <a href="{{ route('guide') }}" class="font-semibold text-brand-600 hover:text-brand-700">← Compliance Guide</a>
            <span class="text-gray-500">Step {{ $t['step'] }} of {{ count($all) }}</span>
        </nav>
        <div class="mt-3 grid gap-1.5" style="grid-template-columns: repeat({{ count($all) }}, minmax(0, 1fr))">
            @foreach ($all as $slug => $s)
                <a href="{{ route('guide.show', $slug) }}" title="{{ $s['step'] }}. {{ $s['title'] }}"
                   class="h-1.5 rounded-full {{ $s['step'] <= $t['step'] ? 'bg-brand-600' : 'bg-gray-200 hover:bg-gray-300' }}"></a>
            @endforeach
        </div>

        <div class="mt-10 text-4xl">{{ $t['icon'] }}</div>
        <h1 class="mt-3 text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900">{{ $t['title'] }}</h1>
        <p class="mt-4 text-xl text-gray-700 leading-relaxed max-w-3xl">{{ $t['oneliner'] }}</p>
        <p class="mt-4 text-sm text-gray-400">{{ $t['minutes'] }} min read · Last reviewed {{ \App\Support\ComplianceGuide::REVIEWED }}</p>
    </div>
</section>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- ── WHY IT MATTERS ──────────────────────────────────────────────── --}}
    <section class="py-10">
        <h2 class="text-sm font-bold uppercase tracking-wider text-brand-600">Why it matters</h2>
        <div class="mt-5 grid md:grid-cols-3 gap-4">
            @foreach ($t['why'] as [$head, $text])
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h3 class="font-bold text-gray-900">{{ $head }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── TOPIC-SPECIFIC BLOCK ────────────────────────────────────────── --}}
    @if ($x = $t['extra'] ?? null)
        <section class="py-10 border-t border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900">{{ $x['title'] }}</h2>
            @if ($x['intro'])<p class="mt-2 text-gray-600">{{ $x['intro'] }}</p>@endif

            @if ($x['table'] ?? false)
                <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200">
                    @foreach ($x['items'] as [$code, $name, $text])
                        <div class="flex flex-col sm:flex-row gap-1 sm:gap-6 p-4 sm:p-5 {{ $loop->odd ? 'bg-white' : 'bg-gray-50' }}">
                            <div class="sm:w-56 shrink-0">
                                <span class="inline-block font-mono text-sm font-bold text-brand-700 bg-brand-50 rounded px-2 py-0.5">{{ $code }}</span>
                                <span class="block mt-1 text-xs text-gray-500">{{ $name }}</span>
                            </div>
                            <p class="text-sm text-gray-700">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 grid sm:grid-cols-2 gap-4">
                    @foreach ($x['items'] as [$icon, $head, $text])
                        <div class="flex gap-4 rounded-2xl bg-gray-50 p-5">
                            <div class="text-2xl shrink-0">{{ $icon }}</div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $head }}</h3>
                                <p class="mt-1 text-sm text-gray-600 leading-relaxed">{{ $text }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- ── WHAT TO DO ──────────────────────────────────────────────────── --}}
    <section class="py-10 border-t border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900">What you need to do</h2>
        <ol class="mt-6 space-y-5">
            @foreach ($t['steps'] as $i => [$head, $text])
                <li class="flex gap-4">
                    <span class="shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-white text-sm font-bold">{{ $i + 1 }}</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $head }}</h3>
                        <p class="mt-0.5 text-sm text-gray-600">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- ── IN YOUR BUSINESS ────────────────────────────────────────────── --}}
    <section class="py-10 border-t border-gray-100"
             x-data="{ sector: (() => { try { return localStorage.getItem('guide_sector') || 'gold' } catch (e) { return 'gold' } })() }"
             x-init="$watch('sector', v => { try { localStorage.setItem('guide_sector', v) } catch (e) {} })">
        <h2 class="text-2xl font-bold text-gray-900">What it looks like in your business</h2>
        <div class="mt-5 flex flex-wrap gap-2">
            @foreach ($sectors as $key => $s)
                <button type="button" @click="sector = '{{ $key }}'"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition"
                        :class="sector === '{{ $key }}' ? 'bg-brand-600 border-brand-600 text-white' : 'border-gray-200 text-gray-600 hover:border-gray-300'">
                    {{ $s['icon'] }} {{ $s['label'] }}
                </button>
            @endforeach
        </div>
        @foreach ($sectors as $key => $s)
            <div x-show="sector === '{{ $key }}'" @if (! $loop->first) x-cloak @endif class="mt-5 rounded-2xl bg-brand-50 border border-brand-100 p-6">
                <p class="text-gray-800 leading-relaxed">{{ $t['sectors'][$key] }}</p>
            </div>
        @endforeach
    </section>

    {{-- ── COMMON MISTAKES ─────────────────────────────────────────────── --}}
    <section class="py-10 border-t border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900">Common mistakes</h2>
        <div class="mt-6 grid md:grid-cols-3 gap-4">
            @foreach ($t['mistakes'] as [$head, $text])
                <div class="rounded-2xl border border-red-100 bg-red-50/60 p-5">
                    <h3 class="font-semibold text-red-900"><span class="text-red-500">✗</span> {{ $head }}</h3>
                    <p class="mt-2 text-sm text-red-900/80 leading-relaxed">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── HOW WE HELP ─────────────────────────────────────────────────── --}}
    <section class="py-10 border-t border-gray-100">
        <div class="rounded-2xl bg-slate-900 text-white p-6 sm:p-8">
            <h2 class="text-xl font-bold">How Blue Arrow helps</h2>
            <p class="mt-2 text-slate-300 leading-relaxed">{{ $t['help'] }}</p>
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach ($t['links'] as [$label, $route])
                    <a href="{{ route($route) }}" class="inline-flex items-center rounded-lg bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-semibold">{{ $label }} →</a>
                @endforeach
                <a href="{{ route('contact') }}" class="inline-flex items-center rounded-lg bg-white text-slate-900 hover:bg-slate-100 px-4 py-2 text-sm font-semibold">Talk to us</a>
            </div>
        </div>
    </section>

    {{-- ── PREV / NEXT ─────────────────────────────────────────────────── --}}
    <nav class="py-10 border-t border-gray-100 grid sm:grid-cols-2 gap-4">
        @if ($prev)
            <a href="{{ route('guide.show', $prev['slug']) }}" class="rounded-2xl border border-gray-200 p-5 hover:border-brand-300">
                <span class="text-xs text-gray-500">← Step {{ $prev['step'] }}</span>
                <span class="block mt-1 font-semibold text-gray-900">{{ $prev['title'] }}</span>
            </a>
        @else
            <a href="{{ route('guide') }}" class="rounded-2xl border border-gray-200 p-5 hover:border-brand-300">
                <span class="text-xs text-gray-500">← Overview</span>
                <span class="block mt-1 font-semibold text-gray-900">The Compliance Guide</span>
            </a>
        @endif
        @if ($next)
            <a href="{{ route('guide.show', $next['slug']) }}" class="rounded-2xl bg-brand-600 hover:bg-brand-700 p-5 text-white sm:text-right">
                <span class="text-xs text-brand-100">Next: step {{ $next['step'] }} →</span>
                <span class="block mt-1 font-semibold">{{ $next['title'] }}</span>
            </a>
        @else
            <a href="{{ route('contact') }}" class="rounded-2xl bg-brand-600 hover:bg-brand-700 p-5 text-white sm:text-right">
                <span class="text-xs text-brand-100">You've finished the guide →</span>
                <span class="block mt-1 font-semibold">Check where your business stands</span>
            </a>
        @endif
    </nav>

    <p class="pb-4 text-xs text-gray-400">
        General guidance only, not legal advice. Requirements can differ by supervisor and free zone — confirm specifics with your compliance officer or our team.
    </p>
</div>

@endsection
