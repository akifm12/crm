<?php
// app/Http/Controllers/PublicAccountController.php

namespace App\Http\Controllers;

use App\Models\PublicUser;
use App\Services\MailerSubscriberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicAccountController extends Controller
{
    public function showRegister(): View
    {
        return view('public.account.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:public_users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $subscribe = $request->boolean('subscribed_to_updates', true);

        $user = PublicUser::create([
            'name'                    => $validated['name'],
            'email'                   => $validated['email'],
            'password'                => Hash::make($validated['password']),
            'subscribed_to_updates'   => $subscribe,
        ]);

        if ($subscribe) {
            app(MailerSubscriberService::class)->subscribe($user->email, $user->name);
        }

        Auth::guard('public')->login($user);
        $request->session()->regenerate();

        return redirect()->route('account.dashboard')->with('status', 'Welcome to Blue Arrow — your free account is ready.');
    }

    public function showLogin(): View
    {
        return view('public.account.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->string('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (! Auth::guard('public')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('public')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function dashboard(): View
    {
        $user = Auth::guard('public')->user();

        $deadlines = $user->deadlines()->orderBy('due_date')->get();

        return view('public.account.dashboard', compact('user', 'deadlines'));
    }
}
