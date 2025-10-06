<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SqliteTimezoneFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting SQLite timezone fix - adding 5 hours to all timestamps...');
        
        // Check if we're using SQLite
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        
        $this->command->info("Database driver: {$driver}");
        
        if ($driver === 'sqlite') {
            $this->fixSqliteTimestamps();
        } else {
            $this->fixMysqlTimestamps();
        }
        
        $this->command->info('Timezone fix completed!');
    }
    
    private function fixSqliteTimestamps()
    {
        $this->command->info('Using SQLite-compatible method...');
        
        // Fix GeneratorLogs
        $this->fixSqliteGeneratorLogs();
        
        // Fix GeneratorWriteLogs
        $this->fixSqliteGeneratorWriteLogs();
    }
    
    private function fixSqliteGeneratorLogs()
    {
        $this->command->info('Fixing GeneratorLogs timestamps (SQLite)...');
        
        $totalLogs = GeneratorLog::count();
        $this->command->info("Found {$totalLogs} GeneratorLogs to fix");
        
        if ($totalLogs == 0) {
            $this->command->info("No GeneratorLogs found to fix");
            return;
        }
        
        $updatedCount = 0;
        $errorCount = 0;
        
        // Process in batches to avoid memory issues
        $batchSize = 100;
        $offset = 0;
        
        do {
            $logs = GeneratorLog::offset($offset)->limit($batchSize)->get();
            
            foreach ($logs as $log) {
                try {
                    // Parse the timestamp and add 5 hours
                    $originalTime = Carbon::parse($log->log_timestamp);
                    $newTime = $originalTime->addHours(5);
                    
                    // Update using raw SQL for SQLite
                    DB::statement("UPDATE generator_logs SET log_timestamp = ? WHERE id = ?", [
                        $newTime->format('Y-m-d H:i:s'),
                        $log->id
                    ]);
                    
                    $updatedCount++;
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->command->error("Error updating GeneratorLog ID {$log->id}: " . $e->getMessage());
                }
            }
            
            $offset += $batchSize;
            
            if ($updatedCount % 500 == 0) {
                $this->command->info("Fixed {$updatedCount}/{$totalLogs} GeneratorLogs...");
            }
            
        } while ($logs->count() == $batchSize);
        
        $this->command->info("GeneratorLogs fix completed: {$updatedCount} records updated, {$errorCount} errors");
    }
    
    private function fixSqliteGeneratorWriteLogs()
    {
        $this->command->info('Fixing GeneratorWriteLogs timestamps (SQLite)...');
        
        $totalWriteLogs = GeneratorWriteLog::count();
        $this->command->info("Found {$totalWriteLogs} GeneratorWriteLogs to fix");
        
        if ($totalWriteLogs == 0) {
            $this->command->info("No GeneratorWriteLogs found to fix");
            return;
        }
        
        $updatedCount = 0;
        $errorCount = 0;
        
        // Process in batches to avoid memory issues
        $batchSize = 100;
        $offset = 0;
        
        do {
            $writeLogs = GeneratorWriteLog::offset($offset)->limit($batchSize)->get();
            
            foreach ($writeLogs as $writeLog) {
                try {
                    // Parse the timestamp and add 5 hours
                    $originalTime = Carbon::parse($writeLog->write_timestamp);
                    $newTime = $originalTime->addHours(5);
                    
                    // Update using raw SQL for SQLite
                    DB::statement("UPDATE generator_write_logs SET write_timestamp = ? WHERE id = ?", [
                        $newTime->format('Y-m-d H:i:s'),
                        $writeLog->id
                    ]);
                    
                    $updatedCount++;
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->command->error("Error updating GeneratorWriteLog ID {$writeLog->id}: " . $e->getMessage());
                }
            }
            
            $offset += $batchSize;
            
            if ($updatedCount % 500 == 0) {
                $this->command->info("Fixed {$updatedCount}/{$totalWriteLogs} GeneratorWriteLogs...");
            }
            
        } while ($writeLogs->count() == $batchSize);
        
        $this->command->info("GeneratorWriteLogs fix completed: {$updatedCount} records updated, {$errorCount} errors");
    }
    
    private function fixMysqlTimestamps()
    {
        $this->command->info('Using MySQL-compatible method...');
        
        try {
            // Update GeneratorLogs using MySQL DATE_ADD
            $result1 = DB::statement("UPDATE generator_logs SET log_timestamp = DATE_ADD(log_timestamp, INTERVAL 5 HOUR)");
            $affected1 = DB::affectedRows();
            $this->command->info("GeneratorLogs updated: {$affected1} records");
            
            // Update GeneratorWriteLogs using MySQL DATE_ADD
            $result2 = DB::statement("UPDATE generator_write_logs SET write_timestamp = DATE_ADD(write_timestamp, INTERVAL 5 HOUR)");
            $affected2 = DB::affectedRows();
            $this->command->info("GeneratorWriteLogs updated: {$affected2} records");
            
            $this->command->info("MySQL update completed: " . ($affected1 + $affected2) . " total records updated");
            
        } catch (\Exception $e) {
            $this->command->error("MySQL update failed: " . $e->getMessage());
            $this->command->info("Falling back to SQLite method...");
            $this->fixSqliteTimestamps();
        }
    }
}
