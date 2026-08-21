<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// FR7 — Scheduler cong bo ket qua cuoc thi (usecase "Announce winner").
// Chay bang: php artisan schedule:work (dev) hoac cron `php artisan schedule:run` (prod).
Schedule::command('contests:announce')->hourly();
