<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use Carbon\Carbon;

class AutoCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:logs {--days=10 : Number of days to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cleanup old logs and keep only specified days of data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Starting auto cleanup - keeping only latest {$days} days of data...");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')} ({$days} days ago)");
        
        // Cleanup GeneratorLogs
        $this->cleanupGeneratorLogs($cutoffDate);
        
        // Cleanup GeneratorWriteLogs
        $this->cleanupGeneratorWriteLogs($cutoffDate);
        
        $this->info('Auto cleanup completed successfully!');
        
        return 0;
    }
    
    private function cleanupGeneratorLogs($cutoffDate)
    {
        $this->info('Cleaning up GeneratorLogs...');
        
        // Count total records before cleanup
        $totalBefore = GeneratorLog::count();
        $this->info("Total GeneratorLogs before cleanup: {$totalBefore}");
        
        // Count records to be deleted
        $toDelete = GeneratorLog::where('log_timestamp', '<', $cutoffDate)->count();
        $this->info("GeneratorLogs to delete (older than cutoff): {$toDelete}");
        
        if ($toDelete > 0) {
            // Delete old records
            $deleted = GeneratorLog::where('log_timestamp', '<', $cutoffDate)->delete();
            $this->info("✅ Deleted {$deleted} old GeneratorLogs");
        } else {
            $this->info("✅ No old GeneratorLogs to delete");
        }
        
        // Count remaining records
        $totalAfter = GeneratorLog::count();
        $this->info("Total GeneratorLogs after cleanup: {$totalAfter}");
        
        // Show date range of remaining data
        $oldest = GeneratorLog::min('log_timestamp');
        $newest = GeneratorLog::max('log_timestamp');
        
        if ($oldest && $newest) {
            $this->info("Date range of remaining data:");
            $this->info("  Oldest: {$oldest}");
            $this->info("  Newest: {$newest}");
        }
    }
    
    private function cleanupGeneratorWriteLogs($cutoffDate)
    {
        $this->info('Cleaning up GeneratorWriteLogs...');
        
        // Count total records before cleanup
        $totalBefore = GeneratorWriteLog::count();
        $this->info("Total GeneratorWriteLogs before cleanup: {$totalBefore}");
        
        // Count records to be deleted
        $toDelete = GeneratorWriteLog::where('write_timestamp', '<', $cutoffDate)->count();
        $this->info("GeneratorWriteLogs to delete (older than cutoff): {$toDelete}");
        
        if ($toDelete > 0) {
            // Delete old records
            $deleted = GeneratorWriteLog::where('write_timestamp', '<', $cutoffDate)->delete();
            $this->info("✅ Deleted {$deleted} old GeneratorWriteLogs");
        } else {
            $this->info("✅ No old GeneratorWriteLogs to delete");
        }
        
        // Count remaining records
        $totalAfter = GeneratorWriteLog::count();
        $this->info("Total GeneratorWriteLogs after cleanup: {$totalAfter}");
        
        // Show date range of remaining data
        $oldest = GeneratorWriteLog::min('write_timestamp');
        $newest = GeneratorWriteLog::max('write_timestamp');
        
        if ($oldest && $newest) {
            $this->info("Date range of remaining data:");
            $this->info("  Oldest: {$oldest}");
            $this->info("  Newest: {$newest}");
        }
    }
}
