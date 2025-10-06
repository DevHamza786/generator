<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto cleanup - keep only latest 10 days of data
        // Runs daily at 2:00 AM
        $schedule->command('cleanup:logs --days=10')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/cleanup.log'));

        // Device status update - check and update generator statuses
        // Runs every minute to ensure accurate status tracking
        $schedule->command('device:update-status')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/device-status.log'));

        // Alert checking - monitor for generator issues
        // Runs every 2 minutes to check for alerts
        $schedule->command('alerts:check')
                 ->everyTwoMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/alerts.log'));

        // Runtime tracking - process generator logs and track runtime based on voltage
        // Runs every minute to ensure accurate runtime tracking
        $schedule->command('runtime:process')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/runtime-tracking.log'));

        // Optional: Add email notification for cleanup results
        // Uncomment the line below if you want email notifications
        // ->emailOutputTo('admin@yourdomain.com');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
