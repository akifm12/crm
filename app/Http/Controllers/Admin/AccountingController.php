<?php
// app/Http/Controllers/Admin/AccountingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AccountingController extends Controller
{
    public function launch()
    {
        $user      = Auth::user();
        $secret    = config('services.accounting.secret');
        $email     = $user->email;
        $timestamp = time();

        // Build HMAC token: sha256(secret + email + timestamp)
        $token = hash_hmac('sha256', $email . '|' . $timestamp, $secret);

        $url = config('services.accounting.url')
            . '/auto-login'
            . '?email='     . urlencode($email)
            . '&ts='        . $timestamp
            . '&token='     . $token;

        return redirect()->away($url);
    }
}
