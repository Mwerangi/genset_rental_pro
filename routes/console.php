<?php

use App\Models\CompanySetting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Daily Close ───────────────────────────────────────────────────────────────
// Runs every minute; fires the actual close only when the time matches the
// configured day_close_time AND day_close_enabled is true.
Schedule::call(function () {
    try {
        $settings = CompanySetting::current();
        if (!$settings->day_close_enabled) {
            return;
        }
        $configuredTime = $settings->day_close_time ?? '23:00';
        if (now()->format('H:i') === $configuredTime) {
            Artisan::call('accounting:daily-close');
        }
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Daily close scheduler error: ' . $e->getMessage());
    }
})->everyMinute()->name('accounting-daily-close-check')->withoutOverlapping();
