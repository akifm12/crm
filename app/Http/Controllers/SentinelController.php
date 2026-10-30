<?php
// app/Http/Controllers/SentinelController.php
// Public product page for Sentinel AML (formerly the static site at sentinel.bluearrow.ae).

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SentinelController extends Controller
{
    // Sentinel API (sentinel_nginx), reached over loopback — see /opt/sentinel-aml
    private const API = 'http://127.0.0.1:8085/api';

    // Listed entities per source in the Standard data (the official lists and Wikidata PEPs that the
    // Essential and Complete plans screen against), written after each nightly import by daily_update.sh
    private const COUNTS_FILE = '/var/www/bluearrow.ae/scripts/standard_counts.json';

    // Used when the counts file is missing or unreadable — figures from the 2026-10-30 import
    private const FALLBACK_COUNTS = [
        'Australia (DFAT)' => 4010, 'Canada (SEMA)' => 5708, 'EU (Europe)' => 6269, 'OFAC (USA)' => 20000,
        'OFAC (USA) Non-SDN' => 486, 'PEP (Wikidata)' => 557052, 'Switzerland (SECO)' => 7105,
        'UAE (Local Terror List)' => 311, 'UK (FCDO)' => 6420, 'UN (Global)' => 1007,
        'US Export Controls (BIS/State)' => 6213,
    ];
    private const PEPS = 'PEP (Wikidata)';
    private const EXPORT = 'US Export Controls (BIS/State)';

    public function show(): View
    {
        return view('public.sentinel', ['stats' => self::stats()]);
    }

    /** Rounded-down coverage figures from the latest nightly import (also used on the home page). */
    public static function stats(): array
    {
        $c = rescue(fn () => json_decode(file_get_contents(self::COUNTS_FILE), true), null, false);
        if (! is_array($c) || array_sum($c) < 300_000) {
            $c = self::FALLBACK_COUNTS;
        }
        $peps = $c[self::PEPS] ?? 0;
        $export = $c[self::EXPORT] ?? 0;

        return [
            'total'     => self::round(array_sum($c)),
            'sanctions' => self::round(array_sum($c) - $peps - $export),
            'peps'      => self::round($peps),
            'export'    => self::round($export),
            'uae'       => (string) ($c['UAE (Local Terror List)'] ?? 311),
        ];
    }

    /** 1,440,399 → "1.4M+", 557,052 → "550k+", 6,213 → "6.2k+" — always rounded down so the claim stays true. */
    private static function round(int $n): string
    {
        if ($n >= 1_000_000) return floor($n / 100_000) / 10 . 'M+';
        if ($n >= 10_000)    { $p = 10 ** (strlen((string) $n) - 2); return floor($n / $p) * $p / 1000 . 'k+'; }
        if ($n >= 1_000)     return floor($n / 100) / 10 . 'k+';
        return $n . '+';
    }

    /** Free trial — Sentinel creates the account and emails the login details. */
    public function trial(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('trial', [
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['required', 'email', 'max:150'],
            'company' => ['nullable', 'string', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:40'],
        ]);

        try {
            // Sentinel trusts two proxy hops, so it reads the visitor's IP from here (rate limits, audit log)
            $res = Http::timeout(20)
                ->withHeaders(['X-Forwarded-For' => $request->ip()])
                ->post(self::API . '/auth/trial/', $data);
        } catch (\Throwable $e) {
            Log::error('Sentinel trial signup failed: ' . $e->getMessage());
            return $this->back('trial', 'error', 'We could not reach Sentinel just now. Please try again in a minute.');
        }

        if ($res->successful()) {
            return $this->back('trial', 'ok', 'Your trial account is ready. Check your inbox for your login details, then sign in at aml.bluearrow.ae.');
        }

        $message = match ($res->json('error')) {
            'trial_exists'   => 'A trial account already exists for this email. Check your inbox for your login details.',
            'account_exists' => 'An account already exists for this email. Please sign in at aml.bluearrow.ae.',
            default          => $res->json('message') ?? $res->json('error') ?? 'Something went wrong. Please try again.',
        };

        return $this->back('trial', 'error', $message);
    }

    /** Plan / demo enquiry — stored with the other contact messages and emailed to the team. */
    public function enquire(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('enquiry', [
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:150'],
            'company'  => ['required', 'string', 'max:150'],
            'phone'    => ['nullable', 'string', 'max:40'],
            'plan'     => ['required', 'in:Essential,Complete,Corporate API,Premium data,Not sure'],
            'sector'   => ['nullable', 'string', 'max:100'],
            'message'  => ['nullable', 'string', 'max:5000'],
        ]);

        $body = "[Sentinel enquiry] Plan: {$data['plan']}\n"
              . "Company: {$data['company']}\n"
              . 'Sector: ' . ($data['sector'] ?: 'not given') . "\n\n"
              . ($data['message'] ?: '(no message)');

        ContactMessage::create([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? null,
            'message' => $body,
        ]);

        try {
            Mail::raw("{$data['name']} <{$data['email']}>" . (($data['phone'] ?? '') ? " · {$data['phone']}" : '') . "\n\n{$body}",
                fn ($m) => $m->to('aml@bluearrow.ae')->replyTo($data['email'], $data['name'])
                             ->subject("Sentinel enquiry: {$data['company']} ({$data['plan']})"));
        } catch (\Throwable $e) {
            // The enquiry is already saved; a mail failure must not lose it
            Log::error('Sentinel enquiry email failed: ' . $e->getMessage());
        }

        return $this->back('enquiry', 'ok', 'Thanks, we have your request. We\'ll be in touch within one business day.');
    }

    private function back(string $form, string $status, string $message): RedirectResponse
    {
        return redirect()->to(route('sentinel') . ($form === 'trial' ? '#try-free' : '#enquire'))
            ->withInput()
            ->with("sentinel_{$form}", ['status' => $status, 'message' => $message]);
    }
}
