<?php
// app/Http/Middleware/EnsureBBookUser.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureBBookUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('b_book')->user();

        if (! $user) {
            return redirect()->route('bbook.login', $request->route('slug'));
        }

        $tenant = app('tenant'); // set by ResolveTenant, which must run before this middleware

        if ((int) $user->tenant_id !== (int) $tenant->id) {
            Auth::guard('b_book')->logout();
            return redirect()->route('bbook.login', $tenant->slug)
                ->with('error', 'You do not have access to this B-Book portal.');
        }

        if (! $tenant->hasModule('b_book')) {
            Auth::guard('b_book')->logout();
            abort(404, 'B-Book is not enabled for this portal.');
        }

        if (! $user->b_book_access) {
            Auth::guard('b_book')->logout();
            return redirect()->route('bbook.login', $tenant->slug)
                ->with('error', 'Your account does not have B-Book access.');
        }

        return $next($request);
    }
}
