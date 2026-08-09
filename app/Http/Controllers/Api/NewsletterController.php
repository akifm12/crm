<?php
// app/Http/Controllers/Api/NewsletterController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsletterSubscribeRequest;
use App\Services\MailerSubscriberService;
use Illuminate\Http\JsonResponse;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request, MailerSubscriberService $mailer): JsonResponse
    {
        $validated = $request->validated();

        $mailer->subscribe($validated['email'], $validated['name'] ?? '');

        return response()->json(['message' => "You're subscribed — thanks for staying informed with Blue Arrow."]);
    }
}
