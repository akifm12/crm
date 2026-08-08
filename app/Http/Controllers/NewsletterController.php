<?php
// app/Http/Controllers/NewsletterController.php

namespace App\Http\Controllers;

use App\Services\MailerSubscriberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request, MailerSubscriberService $mailer): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'name'  => ['nullable', 'string', 'max:150'],
        ]);

        $mailer->subscribe($validated['email'], $validated['name'] ?? '');

        return back()->with('status', 'You\'re subscribed — thanks for staying informed with Blue Arrow.');
    }
}
