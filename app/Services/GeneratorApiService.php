<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\GeneratorLog;
use App\Models\GeneratorStatus;
use App\Models\GeneratorWriteLog;
use Carbon\Carbon;

class GeneratorApiService
{
    private $baseUrl;
    private $generatorIds;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config('generator.base_url');
        $this->generatorIds = explode(',', config('generator.generator_ids'));
        $this->timeout = config('generator.timeout');
    }

    /**
     * Fetch data from the log API and save to database
     */
    public function fetchAndSaveLogs()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/log');

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $logData) {
                        $this->saveLogData($logData, $data['client'] ?? 'unknown');
                    }
                    Log::info('Generator logs fetched and saved successfully');
                }
            } else {
                Log::error('Failed to fetch generator logs', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while fetching generator logs', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Fetch data from the get API and save status
     */
    public function fetchAndSaveStatus()
    {
        try {
            $response = Http::timeout($this->timeout)->post($this->baseUrl . '/get', [
                'ids' => $this->generatorIds
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data'])) {
                    foreach ($data['data'] as $generatorId => $statusData) {
                        $this->saveStatusData($generatorId, $statusData);
                    }
                    Log::info('Generator status fetched and saved successfully');
                }
            } else {
                Log::error('Failed to fetch generator status', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while fetching generator status', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Fetch data from the write API and save to database
     */
    public function fetchAndSaveWriteLogs()
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->baseUrl . '/write');

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $writeData) {
                        $this->saveWriteData($writeData, $data['client'] ?? 'unknown');
                    }
                    Log::info('Generator write logs fetched and saved successfully');
                }
            } else {
                Log::error('Failed to fetch generator write logs', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while fetching generator write logs', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Save log data to database
     */
    private function saveLogData($logData, $client)
    {
        // Check for duplicate entry
        $existingLog = GeneratorLog::where('generator_id', $logData['id'])
            ->where('log_timestamp', $this->parseTimestamp($logData))
            ->first();

        if (!$existingLog) {
            GeneratorLog::create([
                'generator_id' => $logData['id'],
                'client' => $client,
                'PS' => $logData['PS'] ?? false,
                'FL' => $logData['FL'] ?? 0,
                'GS' => $logData['GS'] ?? false,
                'yy' => $logData['yy'] ?? 0,
                'mm' => $logData['mm'] ?? 0,
                'dd' => $logData['dd'] ?? 0,
                'hm' => $logData['hm'] ?? 0,
                'BV' => $logData['BV'] ?? 0,
                'LV1' => $logData['LV1'] ?? 0,
                'LV2' => $logData['LV2'] ?? 0,
                'LV3' => $logData['LV3'] ?? 0,
                'LV12' => $logData['LV12'] ?? 0,
                'LV23' => $logData['LV23'] ?? 0,
                'LV31' => $logData['LV31'] ?? 0,
                'LI1' => $logData['LI1'] ?? 0,
                'LI2' => $logData['LI2'] ?? 0,
                'LI3' => $logData['LI3'] ?? 0,
                'Lf1' => $logData['Lf1'] ?? 0,
                'Lf2' => $logData['Lf2'] ?? 0,
                'Lf3' => $logData['Lf3'] ?? 0,
                'Lpf1' => $logData['Lpf1'] ?? 0,
                'Lpf2' => $logData['Lpf2'] ?? 0,
                'Lpf3' => $logData['Lpf3'] ?? 0,
                'Lkva1' => $logData['Lkva1'] ?? 0,
                'Lkva2' => $logData['Lkva2'] ?? 0,
                'Lkva3' => $logData['Lkva3'] ?? 0,
                'log_timestamp' => $this->parseTimestamp($logData),
            ]);
        }
    }

    /**
     * Save status data to database
     */
    private function saveStatusData($generatorId, $statusData)
    {
        GeneratorStatus::updateOrCreate(
            ['generator_id' => $generatorId],
            [
                'power' => $statusData['power'] ?? false,
                'last_updated' => now(),
            ]
        );
    }

    /**
     * Save write data to database
     */
    private function saveWriteData($writeData, $client)
    {
        // Check for duplicate entry
        $existingWrite = GeneratorWriteLog::where('generator_id', $writeData['id'])
            ->where('write_timestamp', now())
            ->first();

        if (!$existingWrite) {
            GeneratorWriteLog::create([
                'generator_id' => $writeData['id'],
                'client' => $client,
                'PS' => $writeData['PS'] ?? false,
                'FL' => $writeData['FL'] ?? 0,
                'BV' => $writeData['BV'] ?? 0,
                'LV1' => $writeData['LV1'] ?? 0,
                'LV2' => $writeData['LV2'] ?? 0,
                'LV3' => $writeData['LV3'] ?? 0,
                'LV12' => $writeData['LV12'] ?? 0,
                'LV23' => $writeData['LV23'] ?? 0,
                'LV31' => $writeData['LV31'] ?? 0,
                'LI1' => $writeData['LI1'] ?? 0,
                'LI2' => $writeData['LI2'] ?? 0,
                'LI3' => $writeData['LI3'] ?? 0,
                'Lf1' => $writeData['Lf1'] ?? 0,
                'Lf2' => $writeData['Lf2'] ?? 0,
                'Lf3' => $writeData['Lf3'] ?? 0,
                'Lpf1' => $writeData['Lpf1'] ?? 0,
                'Lpf2' => $writeData['Lpf2'] ?? 0,
                'Lpf3' => $writeData['Lpf3'] ?? 0,
                'Lkva1' => $writeData['Lkva1'] ?? 0,
                'Lkva2' => $writeData['Lkva2'] ?? 0,
                'Lkva3' => $writeData['Lkva3'] ?? 0,
                'write_timestamp' => now(),
            ]);
        }
    }

    /**
     * Parse timestamp from log data
     */
    private function parseTimestamp($logData)
    {
        if (isset($logData['yy'], $logData['mm'], $logData['dd'], $logData['hm'])) {
            $year = $logData['yy'];
            $month = $logData['mm'];
            $day = $logData['dd'];
            $time = $logData['hm'];

            $hour = intval($time / 100);
            $minute = $time % 100;

            return Carbon::create($year, $month, $day, $hour, $minute, 0);
        }

        return now();
    }
}
