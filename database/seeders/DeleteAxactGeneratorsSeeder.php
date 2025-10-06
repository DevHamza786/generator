<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Generator;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\GeneratorRuntime;
use App\Models\Alert;

class DeleteAxactGeneratorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the generator IDs to delete
        $generatorIds = ['IDabc1234', 'IDabc567', 'IDabc890'];
        
        echo "Starting deletion of Axact generators and their associated data...\n";
        
        // Find the generators by their generator_id
        $generators = Generator::whereIn('generator_id', $generatorIds)->get();
        
        if ($generators->isEmpty()) {
            echo "No generators found with the specified IDs.\n";
            return;
        }
        
        $generatorDbIds = $generators->pluck('id')->toArray();
        
        echo "Found " . $generators->count() . " generators to delete:\n";
        foreach ($generators as $generator) {
            echo "- {$generator->generator_id} ({$generator->name})\n";
        }
        
        // Delete associated logs first (to maintain referential integrity)
        echo "\nDeleting associated logs...\n";
        
        // Delete GeneratorLogs
        $deletedLogs = GeneratorLog::whereIn('generator_id', $generatorDbIds)->delete();
        echo "Deleted {$deletedLogs} GeneratorLog records\n";
        
        // Delete GeneratorWriteLogs
        $deletedWriteLogs = GeneratorWriteLog::whereIn('generator_id', $generatorDbIds)->delete();
        echo "Deleted {$deletedWriteLogs} GeneratorWriteLog records\n";
        
        // Delete GeneratorRuntimes
        $deletedRuntimes = GeneratorRuntime::whereIn('generator_id', $generatorDbIds)->delete();
        echo "Deleted {$deletedRuntimes} GeneratorRuntime records\n";
        
        // Delete Alerts associated with these generators
        $deletedAlerts = Alert::whereIn('generator_id', $generatorDbIds)->delete();
        echo "Deleted {$deletedAlerts} Alert records\n";
        
        // Finally, delete the generators themselves
        echo "\nDeleting generators...\n";
        $deletedGenerators = Generator::whereIn('generator_id', $generatorIds)->delete();
        echo "Deleted {$deletedGenerators} Generator records\n";
        
        echo "\nDeletion completed successfully!\n";
        echo "Summary:\n";
        echo "- Generators deleted: {$deletedGenerators}\n";
        echo "- GeneratorLogs deleted: {$deletedLogs}\n";
        echo "- GeneratorWriteLogs deleted: {$deletedWriteLogs}\n";
        echo "- GeneratorRuntimes deleted: {$deletedRuntimes}\n";
        echo "- Alerts deleted: {$deletedAlerts}\n";
    }
}
