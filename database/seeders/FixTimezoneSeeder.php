<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixTimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting timezone fix - adding 5 hours to all timestamps...');
        
        // Fix GeneratorLogs
        $this->fixGeneratorLogs();
        
        // Fix GeneratorWriteLogs
        $this->fixGeneratorWriteLogs();
        
        $this->command->info('Timezone fix completed!');
    }
    
    private function fixGeneratorLogs()
    {
        $this->command->info('Fixing GeneratorLogs timestamps...');
        
        $totalLogs = GeneratorLog::count();
        $this->command->info("Found {$totalLogs} GeneratorLogs to fix");
        
        if ($totalLogs == 0) {
            $this->command->info("No GeneratorLogs found to fix");
            return;
        }
        
        $updatedCount = 0;
        $errorCount = 0;
        
        // Use Eloquent to add 5 hours to all timestamps
        try {
            $logs = GeneratorLog::all();
            foreach ($logs as $log) {
                $log->log_timestamp = Carbon::parse($log->log_timestamp)->addHours(5);
                $log->save();
                $updatedCount++;
            }
            $this->command->info("GeneratorLogs fix completed: {$updatedCount} records updated");
        } catch (\Exception $e) {
            $this->command->error("Error fixing GeneratorLogs: " . $e->getMessage());
        }
    }
    
    private function fixGeneratorWriteLogs()
    {
        $this->command->info('Fixing GeneratorWriteLogs timestamps...');
        
        $totalWriteLogs = GeneratorWriteLog::count();
        $this->command->info("Found {$totalWriteLogs} GeneratorWriteLogs to fix");
        
        if ($totalWriteLogs == 0) {
            $this->command->info("No GeneratorWriteLogs found to fix");
            return;
        }
        
        $updatedCount = 0;
        $errorCount = 0;
        
        // Use Eloquent to add 5 hours to all timestamps
        try {
            $writeLogs = GeneratorWriteLog::all();
            foreach ($writeLogs as $writeLog) {
                $writeLog->write_timestamp = Carbon::parse($writeLog->write_timestamp)->addHours(5);
                $writeLog->save();
                $updatedCount++;
                
                if ($updatedCount % 100 == 0) {
                    $this->command->info("Fixed {$updatedCount}/{$totalWriteLogs} GeneratorWriteLogs...");
                }
            }
            $this->command->info("GeneratorWriteLogs fix completed: {$updatedCount} records updated");
        } catch (\Exception $e) {
            $this->command->error("Error fixing GeneratorWriteLogs: " . $e->getMessage());
        }
    }
}
