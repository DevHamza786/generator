<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Generator;

class UpdateGeneratorSitenamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update generator sitenames based on their IDs
        $generatorSitenames = [
            'IDbf822748' => '30th Street',
            'ID54daa3c8' => 'Resort 200 KVA',
            'ID54da27c2' => 'Crest Tower 250 KVA',
            'ID4a2f3a40' => 'Yacht 500 KVA',
            'ID53da9f6e' => '4000 Yard 200 KVA',
            'ID44406481' => '4000 Yard 200 KVA',
            'ID50da533a' => 'Crest Tower 350 KVA',
            'ID350kva' => 'Crest Tower 350 KVA',
            // Additional mappings for any other IDs that might exist
            'ID492ff2e5' => 'Resort 200 KVA',
            'ID492ff2e6' => 'Resort 200 KVA',
            'ID492ff2e7' => 'Resort 200 KVA',
            'IDabc1234' => 'Axact #100',
            'IDabc567' => 'Axact #101',
            'IDabc890' => 'Axact #102',
            'ID1122334455' => 'Axact #103',
            'ID55da2f89' => 'Axact #104',
            'ID42daf728' => 'Axact #105',
            'ID4a2fc645' => 'Yacht 500 KVA',
        ];

        foreach ($generatorSitenames as $generatorId => $sitename) {
            Generator::where('generator_id', $generatorId)
                ->update(['sitename' => $sitename]);
        }

        $this->command->info('Generator sitenames updated successfully!');
    }
}
