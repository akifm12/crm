@extends('layouts.public')

@section('title', 'Sign In — Blue Arrow Management Consultants')

@section('content')

<section class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-8">
        <span class="text-brand-600 font-semibold text-sm">Free Account</span>
        <h1 class="mt-2 text-3xl font-extrabold text-gray-900">Sign In</h1>
        <p class="mt-3 text-gray-600 text-sm">Access your deadline tracker and account settings.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('account.login') }}" class="space-y-5 bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            Remember me
        </label>
        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            Sign In
        </button>
        <p class="text-center text-sm text-gray-500">
            No account yet? <a href="{{ route('account.register') }}" class="font-semibold text-brand-600 hover:text-brand-700">Register free</a>
        </p>
    </form>
</section>

@endsection
