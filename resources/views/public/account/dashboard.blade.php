@extends('layouts.public')

@section('title', 'My Account — Blue Arrow Management Consultants')

@section('content')

<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between flex-wrap gap-4 mb-10">
        <div>
            <span class="text-brand-600 font-semibold text-sm">My Account</span>
            <h1 class="mt-2 text-3xl font-extrabold text-gray-900">Welcome back, {{ $user->name }}</h1>
        </div>
        <form method="POST" action="{{ route('account.logout') }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-gray-500 hover:text-gray-700">Sign out</button>
        </form>
    </div>

    @if (session('status'))
        <div class="mb-8 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3">{{ session('status') }}</div>
    @endif

    <div class="grid sm:grid-cols-3 gap-5 mb-10">
        <div class="rounded-2xl border border-gray-100 bg-white p-6">
            <div class="text-3xl font-extrabold text-brand-600">{{ $deadlines->count() }}</div>
            <p class="mt-1 text-sm text-gray-500">Tracked deadlines</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-6">
            <div class="text-3xl font-extrabold text-red-600">{{ $deadlines->filter(fn($d) => $d->due_date->isPast())->count() }}</div>
            <p class="mt-1 text-sm text-gray-500">Overdue</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-6">
            <div class="text-3xl font-extrabold text-amber-600">{{ $deadlines->filter(fn($d) => ! $d->due_date->isPast() && now()->diffInDays($d->due_date) <= 30)->count() }}</div>
            <p class="mt-1 text-sm text-gray-500">Due within 30 days</p>
        </div>
    </div>

    <div class="rounded-2xl bg-brand-50 border border-brand-100 p-8 text-center">
        <h2 class="text-xl font-bold text-brand-800">Manage your deadlines</h2>
        <p class="mt-2 text-sm text-brand-700">Add, edit and track trade license, Ejari, passport and other renewal dates.</p>
        <a href="{{ route('compliance-calendar') }}" class="mt-5 inline-flex items-center justify-center bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            Open My Deadlines →
        </a>
    </div>

    <p class="mt-8 text-xs text-gray-400 text-center">
        Mailing list: {{ $user->subscribed_to_updates ? 'Subscribed to compliance alerts & industry updates.' : 'Not subscribed to email alerts.' }}
    </p>
</section>

@endsection
