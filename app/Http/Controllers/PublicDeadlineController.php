<?php
// app/Http/Controllers/PublicDeadlineController.php

namespace App\Http\Controllers;

use App\Http\Requests\UserDeadlineRequest;
use App\Models\UserDeadline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PublicDeadlineController extends Controller
{
    public function store(UserDeadlineRequest $request): RedirectResponse
    {
        Auth::guard('public')->user()->deadlines()->create($request->validated());

        return back()->with('status', 'Deadline added.');
    }

    public function update(UserDeadlineRequest $request, UserDeadline $deadline): RedirectResponse
    {
        abort_unless($deadline->public_user_id === Auth::guard('public')->id(), 403);

        $deadline->update($request->validated());

        return back()->with('status', 'Deadline updated.');
    }

    public function destroy(UserDeadline $deadline): RedirectResponse
    {
        abort_unless($deadline->public_user_id === Auth::guard('public')->id(), 403);

        $deadline->delete();

        return back()->with('status', 'Deadline removed.');
    }
}
