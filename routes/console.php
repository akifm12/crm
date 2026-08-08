<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Public site content engine ──────────────────────────────────────────────
Schedule::command('content:fetch-rss')->hourly();
Schedule::command('content:ai-digest')->dailyAt('07:00');
