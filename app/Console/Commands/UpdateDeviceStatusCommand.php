<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeviceStatusService;
use App\Models\Generator;
use App\Models\GeneratorStatus;

class UpdateDeviceStatusCommand extends Command
{
    protected $signature = 'device:update-status {minutes=1}';
    protected $description = 'Update device status based on recent log data';

    protected $deviceStatusService;

    public function __construct(DeviceStatusService $deviceStatusService)
    {
        parent::__construct();
        $this->deviceStatusService = $deviceStatusService;
    }

    public function handle()
    {
        $minutes = $this->argument('minutes');

        $this->info("Updating device status (checking last {$minutes} minutes of data)...");

        try {
            // Clear expired manual overrides first
            $this->deviceStatusService->clearExpiredManualOverrides($minutes);
            $this->info("Cleared expired manual overrides...");

            // Get all generators
            $generators = Generator::all();
            $updatedCount = 0;
            $activeCount = 0;
            $poweredOnCount = 0;

            foreach ($generators as $generator) {
                // Update generator status (this handles manual overrides and runtime tracking)
                $status = $this->deviceStatusService->updateGeneratorStatus($generator->generator_id, $minutes);

                $updatedCount++;

                if ($status['is_active']) {
                    $activeCount++;
                }

                if ($status['power_status']) {
                    $poweredOnCount++;
                }

                // Log individual status (only for active devices to reduce noise)
                if ($status['is_active']) {
                    $this->line("✓ {$generator->generator_id} ({$generator->sitename}): {$status['status_text']} - {$status['power_text']}");
                }
            }

            $this->info("Status update completed!");
            $this->info("Total generators: " . $generators->count());
            $this->info("Active generators: {$activeCount}");
            $this->info("Powered on generators: {$poweredOnCount}");
            $this->info("Updated status records: {$updatedCount}");

            // Log summary to file
            $logMessage = now()->format('Y-m-d H:i:s') . " - Updated {$updatedCount} generators, {$activeCount} active, {$poweredOnCount} powered on\n";
            file_put_contents(storage_path('logs/device-status.log'), $logMessage, FILE_APPEND | LOCK_EX);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error updating device status: " . $e->getMessage());

            // Log error to file
            $errorMessage = now()->format('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
            file_put_contents(storage_path('logs/device-status.log'), $errorMessage, FILE_APPEND | LOCK_EX);

            return 1;
        }
    }
}
