<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Generator;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;

class PopulateClientsGeneratorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get unique clients from existing logs
        $uniqueClients = GeneratorLog::distinct()->pluck('client')->filter()->unique();

        foreach ($uniqueClients as $clientId) {
            if (empty($clientId)) continue;

            // Create client if it doesn't exist
            $client = Client::where('client_id', $clientId)->first();
            if (!$client) {
                $clientName = Client::extractClientName($clientId);
                $clientNumber = Client::extractClientNumber($clientId);

                $client = Client::create([
                    'name' => $clientName,
                    'client_id' => $clientId,
                    'display_name' => ucfirst($clientName) . ' #' . $clientNumber,
                    'description' => "Client {$clientName} with ID {$clientNumber}",
                    'is_active' => true
                ]);

                echo "Created client: {$client->display_name}\n";
            }

            // Get unique generators for this client
            $uniqueGenerators = GeneratorLog::where('client', $clientId)
                ->distinct()
                ->pluck('generator_id')
                ->filter()
                ->unique();

            foreach ($uniqueGenerators as $generatorId) {
                if (empty($generatorId)) continue;

                // Create generator if it doesn't exist
                $generator = Generator::where('client_id', $client->id)
                    ->where('generator_id', $generatorId)
                    ->first();

                if (!$generator) {
                    $generator = Generator::create([
                        'client_id' => $client->id,
                        'generator_id' => $generatorId,
                        'name' => "Generator {$generatorId}",
                        'description' => "Generator with ID {$generatorId}",
                        'location' => 'Unknown',
                        'is_active' => true
                    ]);

                    echo "Created generator: {$generator->name} for client: {$client->display_name}\n";
                }

                // Update logs with client_id and generator_id foreign keys
                GeneratorLog::where('client', $clientId)
                    ->where('generator_id', $generatorId)
                    ->update([
                        'client_id' => $client->id,
                        'generator_id' => $generator->id,
                        'generator_id_old' => $generatorId
                    ]);

                // Update write logs with client_id and generator_id foreign keys
                GeneratorWriteLog::where('client', $clientId)
                    ->where('generator_id', $generatorId)
                    ->update([
                        'client_id' => $client->id,
                        'generator_id' => $generator->id,
                        'generator_id_old' => $generatorId
                    ]);
            }
        }

        echo "Migration completed successfully!\n";
        echo "Total clients: " . Client::count() . "\n";
        echo "Total generators: " . Generator::count() . "\n";
        echo "Total logs: " . GeneratorLog::count() . "\n";
        echo "Total write logs: " . GeneratorWriteLog::count() . "\n";
    }
}
