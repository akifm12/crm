<?php
// app/Http/Controllers/PublicDeadlineController.php

namespace App\Http\Controllers;

use App\Models\UserDeadline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicDeadlineController extends Controller
{
    private array $types = ['trade_license', 'ejari', 'passport', 'eid', 'dnfbp_registration', 'pi_insurance', 'regulatory_license', 'other'];

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Auth::guard('public')->user()->deadlines()->create($validated);

        return back()->with('status', 'Deadline added.');
    }

    public function update(Request $request, UserDeadline $deadline): RedirectResponse
    {
        abort_unless($deadline->public_user_id === Auth::guard('public')->id(), 403);

        $deadline->update($this->validated($request));

        return back()->with('status', 'Deadline updated.');
    }

    public function destroy(UserDeadline $deadline): RedirectResponse
    {
        abort_unless($deadline->public_user_id === Auth::guard('public')->id(), 403);

        $deadline->delete();

        return back()->with('status', 'Deadline removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type'     => ['required', 'in:' . implode(',', $this->types)],
            'label'    => ['nullable', 'string', 'max:150'],
            'due_date' => ['required', 'date'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
