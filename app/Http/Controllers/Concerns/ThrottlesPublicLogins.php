<?php
// app/Http/Controllers/Concerns/ThrottlesPublicLogins.php

namespace App\Http\Controllers\Concerns;

use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ThrottlesPublicLogins
{
    /**
     * Validates credentials against the `public` guard's provider and
     * enforces a 5-attempts-per-email+IP rate limit. Returns the matched
     * user on success; throws ValidationException on bad credentials or
     * lockout. Does NOT log the user in — callers decide session vs token.
     */
    protected function attemptPublicLogin(Request $request): PublicUser
    {
        $throttleKey = Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = PublicUser::where('email', $request->string('email'))->first();

        if (! $user || ! Auth::guard('public')->getProvider()->validateCredentials($user, $request->only('password'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        return $user;
    }
}
