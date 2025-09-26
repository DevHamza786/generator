<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Generator;

class AddNewGeneratorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or find clients
        $resortClient = Client::firstOrCreate(
            ['client_id' => 'resort#100'],
            [
                'name' => 'resort',
                'display_name' => 'Resort',
                'description' => 'Resort client',
                'is_active' => true
            ]
        );

        $yachtClient = Client::firstOrCreate(
            ['client_id' => 'yacht#101'],
            [
                'name' => 'yacht',
                'display_name' => 'Yacht',
                'description' => 'Yacht client',
                'is_active' => true
            ]
        );

        $yardClient = Client::firstOrCreate(
            ['client_id' => 'yard#102'],
            [
                'name' => 'yard',
                'display_name' => '400 Yard',
                'description' => '400 Yard client',
                'is_active' => true
            ]
        );

        $crestClient = Client::firstOrCreate(
            ['client_id' => 'crest#103'],
            [
                'name' => 'crest',
                'display_name' => 'Crest Tower',
                'description' => 'Crest Tower client',
                'is_active' => true
            ]
        );

        // Add new generators
        $generators = [
            [
                'client_id' => $resortClient->id,
                'generator_id' => 'ID492ff2e5',
                'name' => 'Resort 200kva',
                'kva_power' => '200kva',
                'description' => 'Resort 200kva generator',
                'location' => 'Resort',
                'is_active' => true
            ],
            [
                'client_id' => $yachtClient->id,
                'generator_id' => 'ID54da27c2',
                'name' => 'Yacht 27kva',
                'kva_power' => '27kva',
                'description' => 'Yacht 27kva generator',
                'location' => 'Yacht',
                'is_active' => true
            ],
            [
                'client_id' => $yachtClient->id,
                'generator_id' => 'ID4a2f3a40',
                'name' => 'Yacht 500kva',
                'kva_power' => '500kva',
                'description' => 'Yacht 500kva generator',
                'location' => 'Yacht',
                'is_active' => true
            ],
            [
                'client_id' => $yardClient->id,
                'generator_id' => 'ID53da9f6e',
                'name' => '400yardA 200kva',
                'kva_power' => '200kva',
                'description' => '400yardA 200kva generator',
                'location' => '400 Yard A',
                'is_active' => true
            ],
            [
                'client_id' => $yardClient->id,
                'generator_id' => 'ID44406481',
                'name' => '400yardB 200kva',
                'kva_power' => '200kva',
                'description' => '400yardB 200kva generator',
                'location' => '400 Yard B',
                'is_active' => true
            ],
            [
                'client_id' => $crestClient->id,
                'generator_id' => 'ID50da533a',
                'name' => 'Crest Tower 250kva',
                'kva_power' => '250kva',
                'description' => 'Crest Tower 250kva generator',
                'location' => 'Crest Tower',
                'is_active' => true
            ],
            [
                'client_id' => $crestClient->id,
                'generator_id' => 'ID350kva',
                'name' => '350kva Crest Tower',
                'kva_power' => '350kva',
                'description' => '350kva Crest Tower generator',
                'location' => 'Crest Tower',
                'is_active' => true
            ]
        ];

        foreach ($generators as $generatorData) {
            Generator::updateOrCreate(
                ['generator_id' => $generatorData['generator_id']],
                $generatorData
            );
        }

        $this->command->info('New generators added successfully!');
    }
}
