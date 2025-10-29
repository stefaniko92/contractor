<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule exchange rate sync to run daily at midnight
Schedule::command('app:sync-exchanges-rates')->daily();

// Schedule eFaktura client verification to run daily at 2 AM
Schedule::command('efaktura:verify-clients --limit=100')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
