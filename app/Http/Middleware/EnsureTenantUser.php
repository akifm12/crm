<?php
// app/Http/Middleware/EnsureTenantUser.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTenantUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $tenant = app('tenant'); // Set by ResolveTenant middleware

        // Admin users (no tenant_id) can access any tenant portal
        // This lets BAMC admins manage all portals
        if ($user->tenant_id === null) {
            return $next($request);
        }

        // Tenant users can only access their own portal
        if ((int)$user->tenant_id !== (int)$tenant->id) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'You do not have access to this portal.');
        }

        return $next($request);
    }
}
