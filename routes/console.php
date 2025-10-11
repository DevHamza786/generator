<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule generator API fetching tasks
// Schedule::command('generator:fetch-logs')->everyThirtySeconds();
// Schedule::command('generator:fetch-status')->everyThirtySeconds();
// Schedule::command('generator:fetch-write-logs')->everyThirtySeconds();

// Schedule runtime tracking and monitoring tasks
Schedule::command('runtime:process')->everyFifteenSeconds();
// Schedule::command('device:update-status')->everyFifteenSeconds();
// Schedule::command('alerts:check')->everyTwoMinutes();
// Schedule::command('cleanup:logs --days=10')->dailyAt('02:00');
