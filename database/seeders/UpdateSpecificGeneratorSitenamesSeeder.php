<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Generator;

class UpdateSpecificGeneratorSitenamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $updates = [
            'ID4A2FC645' => '27 street-B',
            'ID42DAF728' => '27 street-A',
            'ID53da9f6e' => '4000 yard Caterpillar (gate side)',
            'ID44406481' => '4000 yard Caterpillar (second one)',
            'IDbf822748' => '30th street 60KVA (Red)',
        ];

        foreach ($updates as $generatorId => $sitename) {
            $count = Generator::where('generator_id', $generatorId)->update([
                'sitename' => $sitename,
            ]);

            if ($count === 0) {
                $this->command?->warn("Generator not found: {$generatorId}");
            } else {
                $this->command?->info("Updated {$generatorId} -> {$sitename}");
            }
        }
    }
}
