<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Tenant user — redirect to their portal
        if ($user->tenant_id !== null) {
            $tenant = $user->tenant;
            if ($tenant && $tenant->is_active) {
                return redirect('/' . $tenant->slug);
            }
            // Tenant inactive
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your portal is currently inactive. Please contact your administrator.');
        }

        // Admin user — redirect to admin dashboard
        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
