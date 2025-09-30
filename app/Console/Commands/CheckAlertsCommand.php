<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlertService;

class CheckAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for generator alerts based on current data';

    protected $alertService;

    public function __construct(AlertService $alertService)
    {
        parent::__construct();
        $this->alertService = $alertService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for alerts...');

        try {
            $this->alertService->checkAlerts();

            $activeCount = $this->alertService->getActiveAlertsCount();

            if ($activeCount > 0) {
                $this->warn("Found {$activeCount} active alerts");
            } else {
                $this->info('No active alerts found');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to check alerts: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
