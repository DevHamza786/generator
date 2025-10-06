<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneratorStatus;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\Client;
use App\Models\Generator;
use App\Services\DeviceStatusService;

class GeneratorController extends Controller
{
    protected $deviceStatusService;

    public function __construct(DeviceStatusService $deviceStatusService)
    {
        $this->deviceStatusService = $deviceStatusService;
    }

    /**
     * Get latest generator status based on recent log data
     */
    public function status()
    {
        try {
            // Get all generators with their current status
            $generatorStatuses = $this->deviceStatusService->getAllGeneratorsStatus(1); // 1 minute threshold
            
            // Calculate overall status
            $activeCount = $this->deviceStatusService->getActiveGeneratorsCount(1);
            $totalGenerators = Generator::count();
            
            // Determine overall power status (if any generator is active and powered on)
            $overallPower = false;
            $lastUpdated = null;
            
            foreach ($generatorStatuses as $status) {
                if ($status['is_active'] && $status['power_status']) {
                    $overallPower = true;
                }
                if ($status['last_data_time'] && (!$lastUpdated || $status['last_data_time'] > $lastUpdated)) {
                    $lastUpdated = $status['last_data_time'];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'power' => $overallPower,
                    'active_generators' => $activeCount,
                    'total_generators' => $totalGenerators,
                    'last_updated' => $lastUpdated ? $lastUpdated->toISOString() : null,
                    'generator_statuses' => $generatorStatuses
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting generator status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest generator logs
     */
    public function logs()
    {
        $logs = GeneratorLog::latest()->limit(20)->get();

        return response()->json([
            'status_code' => 200
        ], 200);
    }

    /**
     * Get latest generator write logs
     */
    public function writeLogs()
    {
        $writeLogs = GeneratorWriteLog::latest()->limit(20)->get();

        return response()->json([
            'status_code' => 200
        ], 200);
    }

    /**
     * Get quick stats data for dashboard
     */
    public function quickStats()
    {
        try {
            // Get latest logs from the last 5 minutes to ensure we have recent data
            $cutoffTime = now()->subMinutes(5);
            
            $latestLogs = GeneratorLog::where('log_timestamp', '>=', $cutoffTime)
                ->latest('log_timestamp')
                ->get();
                
            $latestWriteLogs = GeneratorWriteLog::where('write_timestamp', '>=', $cutoffTime)
                ->latest('write_timestamp')
                ->get();
            
            // Combine both log types for comprehensive stats
            $allLogs = $latestLogs->concat($latestWriteLogs);
            
            // Calculate stats
            $runningCount = $allLogs->where('GS', true)->count();
            $stoppedCount = $allLogs->where('GS', false)->count();
            $avgCurrent = $allLogs->avg('LI1') ?? 0;
            $avgFrequency = $allLogs->avg('Lf1') ?? 0;
            
            // Get active generators count (devices with recent data)
            $activeGenerators = $this->deviceStatusService->getActiveGeneratorsCount(1);
            $totalGenerators = Generator::count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'running' => $runningCount,
                    'stopped' => $stoppedCount,
                    'avg_current' => round($avgCurrent, 1),
                    'avg_frequency' => round($avgFrequency, 3),
                    'active_generators' => $activeGenerators,
                    'total_generators' => $totalGenerators,
                    'last_updated' => now()->toISOString()
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting quick stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save sample log data via POST request
     */
    public function saveLogData(Request $request)
    {
        try {
            $data = $request->all();

            if (!isset($data['client']) || !isset($data['data']) || !is_array($data['data'])) {
                return response()->json([
                    'status_code' => 400
                ], 400);
            }

            $savedCount = 0;

            // Get or create client
            $client = $this->getOrCreateClient($data['client']);

            foreach ($data['data'] as $logData) {
                // Get or create generator
                $generator = $this->getOrCreateGenerator($client->id, $logData['id']);

                GeneratorLog::create([
                    'client_id' => $client->id,
                    'generator_id' => $logData['id'], // Keep as varchar for now
                    'generator_id_old' => $logData['id'], // Keep old field for backward compatibility
                    'client' => $data['client'], // Keep old field for backward compatibility
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
                $savedCount++;
            }

            return response()->json([
                'status_code' => 200,
                // 'message' => "Saved {$savedCount} log entries"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error saving log data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save write log data via POST request (saves every 30 seconds)
     */
    public function saveWriteLogData(Request $request)
    {
        try {
            $data = $request->all();

            if (!isset($data['client']) || !isset($data['data']) || !is_array($data['data'])) {
                return response()->json([
                    'status_code' => 400
                ], 400);
            }

            $savedCount = 0;
            $currentTime = now()->setTimezone('Asia/Karachi');

            // Get or create client
            $client = $this->getOrCreateClient($data['client']);

            foreach ($data['data'] as $writeData) {
                // Get or create generator
                $generator = $this->getOrCreateGenerator($client->id, $writeData['id']);

                // Get the last saved data for this generator
                $lastSaved = GeneratorWriteLog::where('generator_id', $writeData['id'])
                    ->latest('write_timestamp')
                    ->first();

                $shouldSave = false;

                if (!$lastSaved) {
                    // First time saving this generator
                    $shouldSave = true;
                } else {
                    // Check if data has changed
                    $dataChanged = $this->hasDataChanged($writeData, $lastSaved);

                    if ($dataChanged) {
                        // Data changed, save immediately
                        $shouldSave = true;
                    } else {
                        // Data is same, check if 30 seconds have passed
                        $timeDiff = $currentTime->diffInSeconds($lastSaved->write_timestamp);
                        $shouldSave = $timeDiff >= 30;
                    }
                }

                if ($shouldSave) {
                    GeneratorWriteLog::create([
                        'client_id' => $client->id,
                        'generator_id' => $writeData['id'], // Keep as varchar for now
                        'generator_id_old' => $writeData['id'], // Keep old field for backward compatibility
                        'client' => $data['client'], // Keep old field for backward compatibility
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
                        'write_timestamp' => $currentTime,
                    ]);
                    $savedCount++;
                }
            }

            return response()->json([
                'status_code' => 200,
                // 'message' => "Saved {$savedCount} write log entries"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error saving write log data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if the new data has changed compared to the last saved data
     */
    private function hasDataChanged($newData, $lastSaved)
    {
        $fieldsToCompare = [
            'PS', 'FL', 'BV', 'LV1', 'LV2', 'LV3', 'LV12', 'LV23', 'LV31',
            'LI1', 'LI2', 'LI3', 'Lf1', 'Lf2', 'Lf3', 'Lpf1', 'Lpf2', 'Lpf3',
            'Lkva1', 'Lkva2', 'Lkva3'
        ];

        foreach ($fieldsToCompare as $field) {
            $newValue = $newData[$field] ?? 0;
            $oldValue = $lastSaved->$field ?? 0;

            // Convert boolean to integer for comparison
            if (is_bool($newValue)) $newValue = $newValue ? 1 : 0;
            if (is_bool($oldValue)) $oldValue = $oldValue ? 1 : 0;

            if ($newValue != $oldValue) {
                return true; // Data has changed
            }
        }

        return false; // No changes detected
    }

    /**
     * Get or create client based on client_id
     */
    private function getOrCreateClient(string $clientId)
    {
        $client = Client::where('client_id', $clientId)->first();

        if (!$client) {
            $clientName = Client::extractClientName($clientId);
            $clientNumber = Client::extractClientNumber($clientId);

            $client = Client::create([
                'name' => $clientName,
                'client_id' => $clientId,
                'display_name' => ucfirst($clientName) . ' #' . $clientNumber,
                'description' => "Client {$clientName} with ID {$clientNumber}",
                'is_active' => true
            ]);
        }

        return $client;
    }

    /**
     * Get or create generator based on client_id and generator_id
     */
    private function getOrCreateGenerator(int $clientId, string $generatorId)
    {
        // First, try to find existing generator by generator_id only (since it's unique)
        $generator = Generator::where('generator_id', $generatorId)->first();

        if (!$generator) {
            // Generator doesn't exist, create new one
            $generator = Generator::create([
                'client_id' => $clientId,
                'generator_id' => $generatorId,
                'name' => "Generator {$generatorId}",
                'description' => "Generator with ID {$generatorId}",
                'location' => 'Unknown',
                'is_active' => true
            ]);
        } else {
            // Generator exists, but check if it belongs to the same client
            if ($generator->client_id !== $clientId) {
                // Update the client_id if it's different (generator moved to different client)
                $generator->update(['client_id' => $clientId]);
            }
        }

        return $generator;
    }

    /**
     * Get power status for multiple generators by their IDs
     */
    public function getPowerStatus(Request $request)
    {
        try {
            $data = $request->all();

            if (!isset($data['ids']) || !is_array($data['ids'])) {
                return response()->json([
                    'status_code' => 400,
                    'message' => 'Invalid request. Expected "ids" array.'
                ], 400);
            }

            $generatorIds = $data['ids'];
            $powerStatus = [];

            foreach ($generatorIds as $generatorId) {
                // Get the latest status for this generator
                $status = GeneratorStatus::where('generator_id', $generatorId)
                    ->latest('last_updated')
                    ->first();

                $powerStatus[$generatorId] = [
                    'power' => $status ? $status->power : false
                ];
            }

            return response()->json([
                'status_code' => 200,
                'data' => $powerStatus
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error getting power status: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get runtime data for a specific generator
     */
    public function getGeneratorRuntime(Request $request)
    {
        try {
            $generatorId = $request->input('generator_id');
            $period = $request->input('period', 'today'); // today, week, month
            
            if (!$generatorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generator ID is required'
                ], 400);
            }

            // Get generator info
            $generator = Generator::where('generator_id', $generatorId)->first();
            if (!$generator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generator not found'
                ], 404);
            }

            // Calculate time ranges based on period
            $now = now();
            switch ($period) {
                case 'today':
                    $startTime = $now->copy()->startOfDay();
                    break;
                case 'week':
                    $startTime = $now->copy()->startOfWeek();
                    break;
                case 'month':
                    $startTime = $now->copy()->startOfMonth();
                    break;
                default:
                    $startTime = $now->copy()->startOfDay();
            }

            // Get runtime data from logs
            $logs = GeneratorLog::where('generator_id', $generatorId)
                ->where('log_timestamp', '>=', $startTime)
                ->where('GS', true) // Only running logs
                ->orderBy('log_timestamp')
                ->get();

            // Calculate runtime statistics
            $totalRuntime = 0;
            $currentRuntime = 0;
            $isCurrentlyRunning = false;
            $lastLogTime = null;

            if ($logs->count() > 0) {
                $lastLogTime = $logs->last()->log_timestamp;
                $isCurrentlyRunning = $this->deviceStatusService->isDeviceActive($generatorId, 1);
                
                // Calculate total runtime (simplified - assuming continuous running)
                $firstLog = $logs->first();
                $lastLog = $logs->last();
                $totalRuntime = $lastLog->log_timestamp->diffInMinutes($firstLog->log_timestamp);
                
                // If currently running, add time since last log
                if ($isCurrentlyRunning) {
                    $currentRuntime = $now->diffInMinutes($lastLogTime);
                }
            }

            // Get additional statistics
            $todayRuntime = $this->calculateRuntimeForPeriod($generatorId, 'today');
            $weekRuntime = $this->calculateRuntimeForPeriod($generatorId, 'week');
            $monthRuntime = $this->calculateRuntimeForPeriod($generatorId, 'month');

            return response()->json([
                'success' => true,
                'data' => [
                    'generator' => [
                        'id' => $generator->generator_id,
                        'sitename' => $generator->sitename,
                        'kva_power' => $generator->kva_power,
                        'is_active' => $isCurrentlyRunning
                    ],
                    'runtime' => [
                        'current' => $this->formatDuration($currentRuntime),
                        'today' => $this->formatDuration($todayRuntime),
                        'week' => $this->formatDuration($weekRuntime),
                        'month' => $this->formatDuration($monthRuntime),
                        'total_minutes' => [
                            'current' => $currentRuntime,
                            'today' => $todayRuntime,
                            'week' => $weekRuntime,
                            'month' => $monthRuntime
                        ]
                    ],
                    'last_updated' => $lastLogTime ? $lastLogTime->toISOString() : null,
                    'period' => $period
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting generator runtime: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate runtime for a specific period
     */
    private function calculateRuntimeForPeriod($generatorId, $period)
    {
        $now = now();
        switch ($period) {
            case 'today':
                $startTime = $now->copy()->startOfDay();
                break;
            case 'week':
                $startTime = $now->copy()->startOfWeek();
                break;
            case 'month':
                $startTime = $now->copy()->startOfMonth();
                break;
            default:
                $startTime = $now->copy()->startOfDay();
        }

        $logs = GeneratorLog::where('generator_id', $generatorId)
            ->where('log_timestamp', '>=', $startTime)
            ->where('GS', true)
            ->orderBy('log_timestamp')
            ->get();

        if ($logs->count() < 2) {
            return 0;
        }

        // Simplified calculation - total time span when generator was running
        $firstLog = $logs->first();
        $lastLog = $logs->last();
        
        return $lastLog->log_timestamp->diffInMinutes($firstLog->log_timestamp);
    }

    /**
     * Format duration in minutes to human readable format
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return $minutes . 'm';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($hours < 24) {
            return $remainingMinutes > 0 ? $hours . 'h ' . $remainingMinutes . 'm' : $hours . 'h';
        }
        
        $days = floor($hours / 24);
        $remainingHours = $hours % 24;
        
        return $remainingHours > 0 ? $days . 'd ' . $remainingHours . 'h' : $days . 'd';
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

            return \Carbon\Carbon::create($year, $month, $day, $hour, $minute, 0, 'Asia/Karachi');
        }

        return now()->setTimezone('Asia/Karachi');
    }
}
