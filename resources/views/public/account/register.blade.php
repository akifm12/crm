@extends('layouts.public')

@section('title', 'Create a Free Account — Blue Arrow Management Consultants')
@section('meta_description', 'Create a free Blue Arrow account to track your own compliance deadlines and receive regulatory alerts.')

@section('content')

<section class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-8">
        <span class="text-brand-600 font-semibold text-sm">Free Account</span>
        <h1 class="mt-2 text-3xl font-extrabold text-gray-900">Track Your Own Deadlines</h1>
        <p class="mt-3 text-gray-600 text-sm">Register free to use the interactive compliance calendar and get compliance alerts by email.</p>
    </div>

    <form method="POST" action="{{ route('account.register') }}" class="space-y-5 bg-white rounded-2xl border border-gray-100 p-8 shadow-sm">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
            @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
            <input type="password" name="password_confirmation" required
                   class="w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
        </div>
        <label class="flex items-start gap-2 text-sm text-gray-600">
            <input type="checkbox" name="subscribed_to_updates" value="1" checked
                   class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            Send me compliance alerts &amp; industry updates by email
        </label>
        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            Create Free Account
        </button>
        <p class="text-center text-sm text-gray-500">
            Already have an account? <a href="{{ route('account.login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Sign in</a>
        </p>
    </form>
</section>

@endsection
