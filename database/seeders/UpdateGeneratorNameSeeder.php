<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Generator;

class UpdateGeneratorNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Updating generator name to 350kva...');

        // Update ID50da533a from 250kva to 350kva
        $generator = Generator::where('generator_id', 'ID50da533a')->first();

        if ($generator) {
            $generator->update([
                'name' => 'Crest Tower 350kva',
                'kva_power' => '350kva',
                'sitename' => 'Crest Tower 350 KVA'
            ]);

            $this->command->info('Updated ID50da533a:');
            $this->command->info('- Name: Crest Tower 350kva');
            $this->command->info('- kVA Power: 350kva');
            $this->command->info('- Sitename: Crest Tower 350 KVA');
        } else {
            $this->command->error('Generator ID50da533a not found!');
        }

        // Show updated generator
        $updatedGenerator = Generator::where('generator_id', 'ID50da533a')->first();
        if ($updatedGenerator) {
            $this->command->info('Final result:');
            $this->command->info($updatedGenerator->generator_id . ' - ' . $updatedGenerator->name . ' - ' . $updatedGenerator->kva_power . ' - ' . $updatedGenerator->sitename);
        }
    }
}
