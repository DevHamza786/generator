<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeneratorApiService;

class FetchGeneratorStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:fetch-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch generator status from API and save to database';

    /**
     * Execute the console command.
     */
    public function handle(GeneratorApiService $apiService)
    {
        $this->info('Fetching generator status...');

        try {
            $apiService->fetchAndSaveStatus();
            $this->info('Generator status fetched and saved successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to fetch generator status: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
