@extends('layouts.public')

@section('title', 'AML Compliance Guide for UAE Businesses | Blue Arrow')
@section('meta_description', 'A plain-English, 5-step guide to AML compliance in the UAE: the rules, risk assessment, customer due diligence, sanctions screening and goAML reporting, for gold dealers, real estate, company service providers and accountants.')

@section('content')

{{-- ── HERO ─────────────────────────────────────────────────────────────── --}}
<section class="bg-gradient-to-b from-brand-50 via-white to-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-10 text-center">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 text-brand-700 text-xs font-semibold px-3 py-1">📘 The Compliance Guide</span>
        <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900">AML compliance, <span class="text-brand-600">made simple.</span></h1>
        <p class="mt-5 text-lg text-gray-600 max-w-2xl mx-auto">
            Five things every regulated business in the UAE has to get right. Plain English, no jargon, about three minutes each.
        </p>
        <a href="{{ route('guide.show', $topics[0]['slug']) }}" class="mt-8 inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3.5 rounded-xl shadow-sm shadow-brand-600/20">
            Start with step 1 →
        </a>
    </div>
</section>

{{-- ── THE 5 STEPS ──────────────────────────────────────────────────────── --}}
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <ol class="relative space-y-4">
        <div class="absolute left-6 top-6 bottom-6 w-px bg-brand-100 hidden sm:block" aria-hidden="true"></div>
        @foreach ($topics as $t)
            <li class="relative">
                <a href="{{ route('guide.show', $t['slug']) }}" class="group flex gap-5 rounded-2xl border border-gray-100 bg-white p-5 sm:p-6 shadow-sm hover:shadow-lg hover:border-brand-200 transition">
                    <span class="relative z-10 shrink-0 inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-600 text-white text-lg font-bold ring-4 ring-white">{{ $t['step'] }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-lg font-bold text-gray-900 group-hover:text-brand-600">{{ $t['icon'] }} {{ $t['title'] }}</h2>
                            <span class="text-xs text-gray-400">· {{ $t['nav'] }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">{{ $t['oneliner'] }}</p>
                    </div>
                    <span class="hidden sm:flex shrink-0 items-center text-sm font-semibold text-brand-600 whitespace-nowrap">{{ $t['minutes'] }} min →</span>
                </a>
            </li>
        @endforeach
    </ol>
</section>

{{-- ── WHAT IT MEANS FOR MY BUSINESS ────────────────────────────────────── --}}
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14"
         x-data="{ sector: (() => { try { return localStorage.getItem('guide_sector') || 'gold' } catch (e) { return 'gold' } })() }"
         x-init="$watch('sector', v => { try { localStorage.setItem('guide_sector', v) } catch (e) {} })">
    <div class="rounded-3xl bg-slate-900 text-white p-6 sm:p-10">
        <h2 class="text-2xl sm:text-3xl font-bold">What does this mean for my business?</h2>
        <p class="mt-2 text-slate-300">Pick your sector for the short version.</p>

        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($sectors as $key => $s)
                <button type="button" @click="sector = '{{ $key }}'"
                        class="px-4 py-2 rounded-full text-sm font-semibold transition"
                        :class="sector === '{{ $key }}' ? 'bg-white text-slate-900' : 'bg-white/10 text-slate-200 hover:bg-white/20'">
                    {{ $s['icon'] }} {{ $s['label'] }}
                </button>
            @endforeach
        </div>

        @foreach ($sectors as $key => $s)
            <ol x-show="sector === '{{ $key }}'" @if (! $loop->first) x-cloak @endif class="mt-8 space-y-4">
                @foreach ($topics as $t)
                    <li class="flex gap-4">
                        <span class="shrink-0 inline-flex h-7 w-7 items-center justify-center rounded-full bg-brand-500 text-xs font-bold">{{ $t['step'] }}</span>
                        <p class="text-sm text-slate-200 leading-relaxed">
                            <a href="{{ route('guide.show', $t['slug']) }}" class="font-semibold text-white hover:text-sky-300">{{ $t['title'] }}:</a>
                            {{ $t['sectors'][$key] }}
                        </p>
                    </li>
                @endforeach
            </ol>
        @endforeach
    </div>
</section>

{{-- ── CTA ──────────────────────────────────────────────────────────────── --}}
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-6 text-center">
    <h2 class="text-2xl font-bold text-gray-900">Not sure where you stand?</h2>
    <p class="mt-2 text-gray-600">Tell us about your business and we'll tell you what applies, and what's missing.</p>
    <a href="{{ route('contact') }}" class="mt-6 inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl">Get a free consultation</a>
    <p class="mt-10 text-xs text-gray-400">General guidance only, not legal advice. Last reviewed {{ \App\Support\ComplianceGuide::REVIEWED }}.</p>
</section>

@endsection
