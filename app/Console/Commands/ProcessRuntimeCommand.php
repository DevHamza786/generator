<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RuntimeTrackingService;

class ProcessRuntimeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runtime:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process generator logs and update runtime tracking based on voltage';

    protected $runtimeService;

    public function __construct(RuntimeTrackingService $runtimeService)
    {
        parent::__construct();
        $this->runtimeService = $runtimeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing runtime tracking...');

        try {
            $this->runtimeService->processLogs();

            $runningCount = $this->runtimeService->getRunningGenerators()->count();

            if ($runningCount > 0) {
                $this->info("Runtime tracking processed successfully. {$runningCount} generators currently running.");
            } else {
                $this->info('Runtime tracking processed successfully. No generators currently running.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process runtime tracking: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
