<?php
// app/Http/Controllers/BBook/BBookAuthController.php

namespace App\Http\Controllers\BBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BBookAuthController extends Controller
{
    public function create(string $slug)
    {
        $tenant = app('tenant'); // set by ResolveTenant

        if (Auth::guard('b_book')->check()) {
            return redirect()->route('bbook.dashboard', $slug);
        }

        return view('bbook.auth.login', compact('tenant'));
    }

    public function store(Request $request, string $slug)
    {
        $tenant = app('tenant');

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::guard('b_book')->attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Those credentials don\'t match our records.'])->onlyInput('email');
        }

        $user = Auth::guard('b_book')->user();

        if ((int) $user->tenant_id !== (int) $tenant->id) {
            Auth::guard('b_book')->logout();
            return back()->with('error', 'You do not have access to this B-Book portal.');
        }

        if (! $tenant->hasModule('b_book')) {
            Auth::guard('b_book')->logout();
            return back()->with('error', 'B-Book is not enabled for this portal.');
        }

        if (! $user->b_book_access) {
            Auth::guard('b_book')->logout();
            return back()->with('error', 'Your account does not have B-Book access.');
        }

        $request->session()->regenerate();

        return redirect()->route('bbook.dashboard', $slug);
    }

    public function destroy(Request $request, string $slug)
    {
        Auth::guard('b_book')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('bbook.login', $slug);
    }
}
