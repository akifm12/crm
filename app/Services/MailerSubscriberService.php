<?php
// app/Services/MailerSubscriberService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailerSubscriberService
{
    /**
     * Forward a subscriber to the existing mailer.bluearrow.ae subscriber
     * system (PERMANENT integration — see routes/web.php marketing proxy).
     */
    public function subscribe(string $email, string $name = ''): void
    {
        try {
            Http::timeout(15)
                ->withOptions(['verify' => false])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://mailer.bluearrow.ae/contacts.php', [
                    'email' => $email,
                    'name'  => $name,
                ]);
        } catch (\Exception $e) {
            Log::error('Mailer subscribe forward failed', ['error' => $e->getMessage()]);
        }
    }
}
