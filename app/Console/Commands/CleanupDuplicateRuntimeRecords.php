<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RuntimeTrackingService;

class CleanupDuplicateRuntimeRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runtime:cleanup-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate running runtime records for generators';

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
        $this->info('Cleaning up duplicate runtime records...');

        try {
            $duplicateCount = $this->runtimeService->cleanupDuplicateRunningRecords();
            
            if ($duplicateCount > 0) {
                $this->info("Successfully cleaned up {$duplicateCount} duplicate running records.");
            } else {
                $this->info('No duplicate running records found.');
            }

            // Also fix any corrupted records
            $corruptedCount = $this->runtimeService->fixCorruptedRecords();
            
            if ($corruptedCount > 0) {
                $this->info("Fixed {$corruptedCount} corrupted runtime records.");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to cleanup duplicate records: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}