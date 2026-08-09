<?php
// app/Http/Controllers/NewsletterController.php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscribeRequest;
use App\Services\MailerSubscriberService;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request, MailerSubscriberService $mailer): RedirectResponse
    {
        $validated = $request->validated();

        $mailer->subscribe($validated['email'], $validated['name'] ?? '');

        return back()->with('status', 'You\'re subscribed — thanks for staying informed with Blue Arrow.');
    }
}
