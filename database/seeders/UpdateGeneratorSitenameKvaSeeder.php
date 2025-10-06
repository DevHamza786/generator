<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Generator;

class UpdateGeneratorSitenameKvaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Starting update of generator sitename and kva values...\n";
        
        // Define the updates based on the provided data
        $updates = [
            'ID492ff2e5' => ['sitename' => 'Resort', 'kva_power' => 200],
            'ID42daf728' => ['sitename' => '27th Street A', 'kva_power' => null], // Empty kva
            'ID53da9f6e' => ['sitename' => '4000 Yard Catep', 'kva_power' => 200],
            'ID44406481' => ['sitename' => '4000 Yard Catep', 'kva_power' => 200],
            'ID4a2f3a40' => ['sitename' => 'Yacht', 'kva_power' => 500],
            'ID4a2fc645' => ['sitename' => '27th Street B', 'kva_power' => null], // Empty kva
            'ID50da533a' => ['sitename' => 'Crest Tower', 'kva_power' => 350],
            'ID54da27c2' => ['sitename' => 'yacht', 'kva_power' => 27], // 27kv converted to 27
            'ID54daa3c8' => ['sitename' => 'Crest Tower', 'kva_power' => 250],
            'IDbf822748' => ['sitename' => '30th Street', 'kva_power' => 60],
        ];
        
        $updatedCount = 0;
        $notFoundCount = 0;
        
        foreach ($updates as $generatorId => $updateData) {
            $generator = Generator::where('generator_id', $generatorId)->first();
            
            if ($generator) {
                $oldSitename = $generator->sitename;
                $oldKva = $generator->kva_power;
                
                // Update sitename
                $generator->sitename = $updateData['sitename'];
                
                // Update kva_power only if provided (not null)
                if ($updateData['kva_power'] !== null) {
                    $generator->kva_power = $updateData['kva_power'];
                }
                
                $generator->save();
                
                echo "Updated {$generatorId}:\n";
                echo "  Sitename: '{$oldSitename}' → '{$updateData['sitename']}'\n";
                if ($updateData['kva_power'] !== null) {
                    echo "  KVA: {$oldKva} → {$updateData['kva_power']}\n";
                } else {
                    echo "  KVA: {$oldKva} → (unchanged)\n";
                }
                echo "\n";
                
                $updatedCount++;
            } else {
                echo "Generator {$generatorId} not found in database\n";
                $notFoundCount++;
            }
        }
        
        echo "Update completed!\n";
        echo "Summary:\n";
        echo "- Generators updated: {$updatedCount}\n";
        echo "- Generators not found: {$notFoundCount}\n";
        echo "- Total processed: " . ($updatedCount + $notFoundCount) . "\n";
    }
}
