<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule generator API fetching tasks
Schedule::command('generator:fetch-logs')->everyThirtySeconds();
Schedule::command('generator:fetch-status')->everyThirtySeconds();
Schedule::command('generator:fetch-write-logs')->everyThirtySeconds();
