<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeneratorApiService;

class FetchGeneratorLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:fetch-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch generator logs from API and save to database';

    /**
     * Execute the console command.
     */
    public function handle(GeneratorApiService $apiService)
    {
        $this->info('Fetching generator logs...');

        try {
            $apiService->fetchAndSaveLogs();
            $this->info('Generator logs fetched and saved successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to fetch generator logs: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
