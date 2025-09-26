<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Generator;

class UpdateTo27thStreetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Updating generators to 27th Street...');

        // Update ID54da27c2
        $generator1 = Generator::where('generator_id', 'ID54da27c2')->first();
        if ($generator1) {
            $generator1->update([
                'name' => '27th Street Generator 1',
                'sitename' => '27th Street'
            ]);
            $this->command->info('Updated ID54da27c2:');
            $this->command->info('- Name: 27th Street Generator 1');
            $this->command->info('- Sitename: 27th Street');
        } else {
            $this->command->error('Generator ID54da27c2 not found!');
        }

        // Update IDbf822748
        $generator2 = Generator::where('generator_id', 'IDbf822748')->first();
        if ($generator2) {
            $generator2->update([
                'name' => '27th Street Generator 2',
                'sitename' => '27th Street'
            ]);
            $this->command->info('Updated IDbf822748:');
            $this->command->info('- Name: 27th Street Generator 2');
            $this->command->info('- Sitename: 27th Street');
        } else {
            $this->command->error('Generator IDbf822748 not found!');
        }

        // Show updated generators
        $this->command->info('Final results:');
        $generators = Generator::whereIn('generator_id', ['ID54da27c2', 'IDbf822748'])->get(['generator_id', 'name', 'sitename']);
        foreach ($generators as $generator) {
            $this->command->info($generator->generator_id . ' - ' . $generator->name . ' - ' . $generator->sitename);
        }
    }
}
