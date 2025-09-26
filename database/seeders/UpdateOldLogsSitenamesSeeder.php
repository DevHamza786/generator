<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\Generator;
use Illuminate\Support\Facades\DB;

class UpdateOldLogsSitenamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to update old logs with sitenames...');

        // Get all generators with their sitenames
        $generators = Generator::select('generator_id', 'sitename')->get()->keyBy('generator_id');

        // Update generator_logs table
        $this->command->info('Updating generator_logs table...');
        $logsUpdated = 0;

        GeneratorLog::chunk(1000, function ($logs) use ($generators, &$logsUpdated) {
            foreach ($logs as $log) {
                if (isset($generators[$log->generator_id]) && $generators[$log->generator_id]->sitename) {
                    $log->sitename = $generators[$log->generator_id]->sitename;
                    $log->save();
                    $logsUpdated++;
                }
            }
        });

        $this->command->info("Updated {$logsUpdated} generator logs with sitenames.");

        // Update generator_write_logs table
        $this->command->info('Updating generator_write_logs table...');
        $writeLogsUpdated = 0;

        GeneratorWriteLog::chunk(1000, function ($writeLogs) use ($generators, &$writeLogsUpdated) {
            foreach ($writeLogs as $writeLog) {
                if (isset($generators[$writeLog->generator_id]) && $generators[$writeLog->generator_id]->sitename) {
                    $writeLog->sitename = $generators[$writeLog->generator_id]->sitename;
                    $writeLog->save();
                    $writeLogsUpdated++;
                }
            }
        });

        $this->command->info("Updated {$writeLogsUpdated} generator write logs with sitenames.");

        // Show summary
        $totalLogs = GeneratorLog::count();
        $totalWriteLogs = GeneratorWriteLog::count();
        $logsWithSitename = GeneratorLog::whereNotNull('sitename')->count();
        $writeLogsWithSitename = GeneratorWriteLog::whereNotNull('sitename')->count();

        $this->command->info('=== SUMMARY ===');
        $this->command->info("Total generator logs: {$totalLogs}");
        $this->command->info("Generator logs with sitename: {$logsWithSitename}");
        $this->command->info("Total generator write logs: {$totalWriteLogs}");
        $this->command->info("Generator write logs with sitename: {$writeLogsWithSitename}");
        $this->command->info('Old data sitenames updated successfully!');
    }
}
