<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneratorStatus;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;

class GeneratorController extends Controller
{
    /**
     * Get latest generator status
     */
    public function status()
    {
        $status = GeneratorStatus::latest()->first();

        return response()->json([
            'status_code' => 200
        ], 200);
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

            foreach ($data['data'] as $logData) {
                GeneratorLog::create([
                    'generator_id' => $logData['id'],
                    'client' => $data['client'],
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

            if ($savedCount === 0) {
                return response()->json([
                    'status_code' => 200
                ], 200);
            }

            return response()->json([
                'status_code' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500
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
            $currentTime = now();

            foreach ($data['data'] as $writeData) {
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
                        'generator_id' => $writeData['id'],
                        'client' => $data['client'],
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
                'status_code' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500
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

            return \Carbon\Carbon::create($year, $month, $day, $hour, $minute, 0);
        }

        return now();
    }
}
