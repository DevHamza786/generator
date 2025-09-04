<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeneratorApiService;

class FetchGeneratorWriteLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:fetch-write-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch generator write logs from API and save to database';

    /**
     * Execute the console command.
     */
    public function handle(GeneratorApiService $apiService)
    {
        $this->info('Fetching generator write logs...');

        try {
            $apiService->fetchAndSaveWriteLogs();
            $this->info('Generator write logs fetched and saved successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to fetch generator write logs: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
