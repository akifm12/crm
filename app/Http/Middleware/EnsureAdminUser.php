<?php
// app/Http/Middleware/EnsureAdminUser.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Tenant users (have tenant_id set) cannot access admin portal
        if ($user->tenant_id !== null) {
            // Redirect them to their own portal
            $tenant = $user->tenant;
            if ($tenant) {
                return redirect('/' . $tenant->slug);
            }
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'You do not have access to the admin portal.');
        }

        return $next($request);
    }
}
