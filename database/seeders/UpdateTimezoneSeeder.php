<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateTimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting timezone conversion from UTC to Asia/Karachi...');
        
        // Convert GeneratorLogs
        $this->convertGeneratorLogs();
        
        // Convert GeneratorWriteLogs
        $this->convertGeneratorWriteLogs();
        
        $this->command->info('Timezone conversion completed!');
    }
    
    private function convertGeneratorLogs()
    {
        $this->command->info('Converting GeneratorLogs...');
        
        $totalLogs = GeneratorLog::count();
        $this->command->info("Found {$totalLogs} GeneratorLogs to convert");
        
        if ($totalLogs == 0) {
            $this->command->info("No GeneratorLogs found to convert");
            return;
        }
        
        $logs = GeneratorLog::all();
        $updatedCount = 0;
        $errorCount = 0;
        
        foreach ($logs as $log) {
            try {
                // Convert UTC timestamp to Karachi time
                $utcTime = Carbon::parse($log->log_timestamp, 'UTC');
                // Add 5 hours to convert from UTC to PKT
                $karachiTime = $utcTime->addHours(5);
                
                // Update the log with Karachi time
                $log->update([
                    'log_timestamp' => $karachiTime
                ]);
                
                $updatedCount++;
                
                if ($updatedCount % 50 == 0) {
                    $this->command->info("Updated {$updatedCount}/{$totalLogs} GeneratorLogs...");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->error("Error updating GeneratorLog ID {$log->id}: " . $e->getMessage());
            }
        }
        
        $this->command->info("GeneratorLogs conversion completed: {$updatedCount} records updated, {$errorCount} errors");
    }
    
    private function convertGeneratorWriteLogs()
    {
        $this->command->info('Converting GeneratorWriteLogs...');
        
        $totalWriteLogs = GeneratorWriteLog::count();
        $this->command->info("Found {$totalWriteLogs} GeneratorWriteLogs to convert");
        
        if ($totalWriteLogs == 0) {
            $this->command->info("No GeneratorWriteLogs found to convert");
            return;
        }
        
        $writeLogs = GeneratorWriteLog::all();
        $updatedCount = 0;
        $errorCount = 0;
        
        foreach ($writeLogs as $writeLog) {
            try {
                // Convert UTC timestamp to Karachi time
                $utcTime = Carbon::parse($writeLog->write_timestamp, 'UTC');
                // Add 5 hours to convert from UTC to PKT
                $karachiTime = $utcTime->addHours(5);
                
                // Update the write log with Karachi time
                $writeLog->update([
                    'write_timestamp' => $karachiTime
                ]);
                
                $updatedCount++;
                
                if ($updatedCount % 100 == 0) {
                    $this->command->info("Updated {$updatedCount}/{$totalWriteLogs} GeneratorWriteLogs...");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->error("Error updating GeneratorWriteLog ID {$writeLog->id}: " . $e->getMessage());
            }
        }
        
        $this->command->info("GeneratorWriteLogs conversion completed: {$updatedCount} records updated, {$errorCount} errors");
    }
}
