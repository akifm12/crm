<?php
// app/Http/Controllers/Admin/PublicUserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicUserController extends Controller
{
    public function index(): View
    {
        return view('admin.public-users.index', [
            'users' => PublicUser::withCount('deadlines')->latest()->get(),
        ]);
    }

    public function destroy(PublicUser $user): RedirectResponse
    {
        $user->deadlines()->delete();
        $user->tokens()->delete();
        $user->delete();

        return back()->with('status', 'Account removed.');
    }
}
