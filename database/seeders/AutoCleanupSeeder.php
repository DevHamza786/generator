<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoCleanupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting auto cleanup - keeping only latest 10 days of data...');
        
        // Calculate cutoff date (10 days ago from now)
        $cutoffDate = Carbon::now()->subDays(10);
        $this->command->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')} (10 days ago)");
        
        // Cleanup GeneratorLogs
        $this->cleanupGeneratorLogs($cutoffDate);
        
        // Cleanup GeneratorWriteLogs
        $this->cleanupGeneratorWriteLogs($cutoffDate);
        
        $this->command->info('Auto cleanup completed!');
    }
    
    private function cleanupGeneratorLogs($cutoffDate)
    {
        $this->command->info('Cleaning up GeneratorLogs...');
        
        // Count total records before cleanup
        $totalBefore = GeneratorLog::count();
        $this->command->info("Total GeneratorLogs before cleanup: {$totalBefore}");
        
        // Count records to be deleted
        $toDelete = GeneratorLog::where('log_timestamp', '<', $cutoffDate)->count();
        $this->command->info("GeneratorLogs to delete (older than 10 days): {$toDelete}");
        
        if ($toDelete > 0) {
            // Delete old records
            $deleted = GeneratorLog::where('log_timestamp', '<', $cutoffDate)->delete();
            $this->command->info("✅ Deleted {$deleted} old GeneratorLogs");
        } else {
            $this->command->info("✅ No old GeneratorLogs to delete");
        }
        
        // Count remaining records
        $totalAfter = GeneratorLog::count();
        $this->command->info("Total GeneratorLogs after cleanup: {$totalAfter}");
        
        // Show date range of remaining data
        $oldest = GeneratorLog::min('log_timestamp');
        $newest = GeneratorLog::max('log_timestamp');
        
        if ($oldest && $newest) {
            $this->command->info("Date range of remaining data:");
            $this->command->info("  Oldest: {$oldest}");
            $this->command->info("  Newest: {$newest}");
        }
    }
    
    private function cleanupGeneratorWriteLogs($cutoffDate)
    {
        $this->command->info('Cleaning up GeneratorWriteLogs...');
        
        // Count total records before cleanup
        $totalBefore = GeneratorWriteLog::count();
        $this->command->info("Total GeneratorWriteLogs before cleanup: {$totalBefore}");
        
        // Count records to be deleted
        $toDelete = GeneratorWriteLog::where('write_timestamp', '<', $cutoffDate)->count();
        $this->command->info("GeneratorWriteLogs to delete (older than 10 days): {$toDelete}");
        
        if ($toDelete > 0) {
            // Delete old records
            $deleted = GeneratorWriteLog::where('write_timestamp', '<', $cutoffDate)->delete();
            $this->command->info("✅ Deleted {$deleted} old GeneratorWriteLogs");
        } else {
            $this->command->info("✅ No old GeneratorWriteLogs to delete");
        }
        
        // Count remaining records
        $totalAfter = GeneratorWriteLog::count();
        $this->command->info("Total GeneratorWriteLogs after cleanup: {$totalAfter}");
        
        // Show date range of remaining data
        $oldest = GeneratorWriteLog::min('write_timestamp');
        $newest = GeneratorWriteLog::max('write_timestamp');
        
        if ($oldest && $newest) {
            $this->command->info("Date range of remaining data:");
            $this->command->info("  Oldest: {$oldest}");
            $this->command->info("  Newest: {$newest}");
        }
    }
}
