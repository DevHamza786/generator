<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Generator;

class UpdateGeneratorNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update generator names based on their IDs
        $generatorNames = [
            'ID492ff2e5' => 'Resort 200kva',
            'ID54da27c2' => 'Yacht 27kva',
            'ID4a2f3a40' => 'Yacht 500kva',
            'ID53da9f6e' => '400yardA 200kva',
            'ID44406481' => '400yardB 200kva',
            'ID50da533a' => 'Crest Tower 250kva',
            'ID350kva' => '350kva Crest Tower',
            // Additional mappings for any other IDs that might exist
            'IDabc1234' => 'Axact #100',
            'IDabc567' => 'Axact #101',
            'IDabc890' => 'Axact #102',
            'ID1122334455' => 'Axact #103',
            'ID55da2f89' => 'Axact #104',
            'ID42daf728' => 'Axact #105',
            'ID4a2fc645' => 'Yacht 500kva',
            'ID54daa3c8' => '350kva Crest Tower',
            'IDbf822748' => '350kva Crest Tower',
        ];

        foreach ($generatorNames as $generatorId => $name) {
            Generator::where('generator_id', $generatorId)
                ->update(['name' => $name]);
        }

        $this->command->info('Generator names updated successfully!');
    }
}
