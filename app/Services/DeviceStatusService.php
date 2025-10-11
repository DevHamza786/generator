<?php

namespace App\Services;

use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\GeneratorStatus;
use Carbon\Carbon;

class DeviceStatusService
{
    /**
     * Check if device is active based on recent log data (within 1 minute)
     */
    public function isDeviceActive($generatorId, $minutesThreshold = 1)
    {
        $cutoffTime = Carbon::now()->subMinutes($minutesThreshold);

        // Check for recent logs
        $recentLog = GeneratorLog::where('generator_id', $generatorId)
            ->where('log_timestamp', '>=', $cutoffTime)
            ->latest('log_timestamp')
            ->first();

        // Check for recent write logs
        $recentWriteLog = GeneratorWriteLog::where('generator_id', $generatorId)
            ->where('write_timestamp', '>=', $cutoffTime)
            ->latest('write_timestamp')
            ->first();

        return $recentLog || $recentWriteLog;
    }

    /**
     * Get device status with detailed information
     */
    public function getDeviceStatus($generatorId, $minutesThreshold = 1)
    {
        $cutoffTime = Carbon::now()->subMinutes($minutesThreshold);

        // Get latest log data
        $latestLog = GeneratorLog::where('generator_id', $generatorId)
            ->latest('log_timestamp')
            ->first();

        $latestWriteLog = GeneratorWriteLog::where('generator_id', $generatorId)
            ->latest('write_timestamp')
            ->first();

        // Check if device is active
        $isActive = $this->isDeviceActive($generatorId, $minutesThreshold);

        // Check for manual power override in GeneratorStatus table
        $manualStatus = GeneratorStatus::where('generator_id', $generatorId)
            ->where('last_updated', '>=', $cutoffTime)
            ->first();

        // Determine power status - prioritize manual override if recent
        $powerStatus = false;
        $lastDataTime = null;
        $isManualOverride = false;

        // Check if we have recent log data that should override manual settings
        $hasRecentLogData = ($latestLog && $latestLog->log_timestamp >= $cutoffTime) ||
                           ($latestWriteLog && $latestWriteLog->write_timestamp >= $cutoffTime);

        // Check if manual override is still valid (not too old)
        $manualOverrideValid = $manualStatus && $manualStatus->last_updated >= $cutoffTime;

        if ($manualOverrideValid) {
            // Manual override takes priority - respect user's manual control
            $powerStatus = $manualStatus->power;
            $lastDataTime = $manualStatus->last_updated;
            $isManualOverride = true;
        } elseif ($hasRecentLogData) {
            // Only use real-time data if there's no active manual override
            if ($latestWriteLog && $latestWriteLog->write_timestamp >= $cutoffTime) {
                // Determine power status based on voltages from write log
                $lv1 = $latestWriteLog->LV1 ?? 0;
                $lv2 = $latestWriteLog->LV2 ?? 0;
                $lv3 = $latestWriteLog->LV3 ?? 0;

                // Generator is ON if ANY voltage is greater than zero (as per user requirement)
                $powerStatus = ($lv1 > 0 || $lv2 > 0 || $lv3 > 0);
                $lastDataTime = $latestWriteLog->write_timestamp;
            } elseif ($latestLog && $latestLog->log_timestamp >= $cutoffTime) {
                // Fallback to GS field from general log if write log is not recent
                $powerStatus = $latestLog->GS ?? false;
                $lastDataTime = $latestLog->log_timestamp;
            }
        }

        return [
            'generator_id' => $generatorId,
            'is_active' => $isActive,
            'power_status' => $powerStatus,
            'last_data_time' => $lastDataTime,
            'minutes_since_last_data' => $lastDataTime ? Carbon::now()->diffInMinutes($lastDataTime) : null,
            'status_text' => $isActive ? 'ACTIVE' : 'INACTIVE',
            'power_text' => $powerStatus ? 'POWER ON' : 'POWER OFF',
            'is_manual_override' => $isManualOverride,
            'latest_write_log' => $latestWriteLog
        ];
    }

    /**
     * Update generator status based on recent log data and manage runtime
     */
    public function updateGeneratorStatus($generatorId, $minutesThreshold = 1)
    {
        // Only clear expired manual overrides (older than threshold), not active ones
        $this->clearExpiredManualOverrides($minutesThreshold);

        $status = $this->getDeviceStatus($generatorId, $minutesThreshold);
        $powerStatus = $status['power_status'];
        $latestWriteLog = $status['latest_write_log'];

        // Only update GeneratorStatus if it's not a manual override
        if (!$status['is_manual_override']) {
            // Update or create generator status record
            GeneratorStatus::updateOrCreate(
                ['generator_id' => $generatorId],
                [
                    'power' => $powerStatus,
                    'last_updated' => $status['last_data_time'] ?? now()
                ]
            );
        }

        // Runtime tracking is now handled by RuntimeTrackingService
        // $this->handleRuntimeTracking($generatorId, $powerStatus, $latestWriteLog);

        return $status;
    }

    /**
     * Handle runtime tracking based on voltage status
     */
    private function handleRuntimeTracking($generatorId, $powerStatus, $latestWriteLog)
    {
        $currentRuntime = \App\Models\GeneratorRuntime::getCurrentRuntime($generatorId);

        if ($powerStatus && !$currentRuntime) {
            // Generator just turned ON - start new runtime
            $this->startNewRuntime($generatorId, $latestWriteLog);
        } elseif (!$powerStatus && $currentRuntime) {
            // Generator just turned OFF - stop current runtime
            $this->stopCurrentRuntime($currentRuntime, $latestWriteLog);
        }
    }

    /**
     * Start a new runtime session
     */
    private function startNewRuntime($generatorId, $latestWriteLog)
    {
        try {
            // Get generator info
            $generator = \App\Models\Generator::where('generator_id', $generatorId)->first();

            \App\Models\GeneratorRuntime::create([
                'generator_id' => $generatorId,
                'client_id' => $generator->client_id ?? null,
                'sitename' => $generator->sitename ?? null,
                'start_time' => $latestWriteLog ? $latestWriteLog->write_timestamp : now(),
                'start_voltage_l1' => $latestWriteLog->LV1 ?? 0,
                'start_voltage_l2' => $latestWriteLog->LV2 ?? 0,
                'start_voltage_l3' => $latestWriteLog->LV3 ?? 0,
                'status' => 'running',
                'maintenance_status' => 'none', // Default maintenance status
                'notes' => 'Auto-started based on voltage detection'
            ]);

            \Log::info("Runtime started for generator {$generatorId} - All voltages > 0");
        } catch (\Exception $e) {
            \Log::error("Failed to start runtime for generator {$generatorId}: " . $e->getMessage());
        }
    }

    /**
     * Stop current runtime session
     */
    private function stopCurrentRuntime($currentRuntime, $latestWriteLog)
    {
        try {
            $endVoltages = [
                'LV1' => $latestWriteLog->LV1 ?? 0,
                'LV2' => $latestWriteLog->LV2 ?? 0,
                'LV3' => $latestWriteLog->LV3 ?? 0,
            ];

            $currentRuntime->stop($endVoltages);
            \Log::info("Runtime stopped for generator {$currentRuntime->generator_id} - One or more voltages = 0");
        } catch (\Exception $e) {
            \Log::error("Failed to stop runtime for generator {$currentRuntime->generator_id}: " . $e->getMessage());
        }
    }

    /**
     * Get all generators with their current status
     */
    public function getAllGeneratorsStatus($minutesThreshold = 1)
    {
        $generators = \App\Models\Generator::all();
        $statuses = [];

        foreach ($generators as $generator) {
            $statuses[] = $this->getDeviceStatus($generator->generator_id, $minutesThreshold);
        }

        return $statuses;
    }

    /**
     * Get active generators count
     */
    public function getActiveGeneratorsCount($minutesThreshold = 1)
    {
        $cutoffTime = Carbon::now()->subMinutes($minutesThreshold);

        $activeGenerators = GeneratorLog::where('log_timestamp', '>=', $cutoffTime)
            ->distinct('generator_id')
            ->pluck('generator_id')
            ->toArray();

        $activeWriteLogGenerators = GeneratorWriteLog::where('write_timestamp', '>=', $cutoffTime)
            ->distinct('generator_id')
            ->pluck('generator_id')
            ->toArray();

        $allActive = array_unique(array_merge($activeGenerators, $activeWriteLogGenerators));

        return count($allActive);
    }

    /**
     * Set manual power override for a generator
     */
    public function setManualPowerOverride($generatorId, $power)
    {
        return GeneratorStatus::updateOrCreate(
            ['generator_id' => $generatorId],
            [
                'power' => $power,
                'last_updated' => now()
            ]
        );
    }

    /**
     * Clear expired manual overrides (older than threshold)
     */
    public function clearExpiredManualOverrides($minutesThreshold = 30)
    {
        $cutoffTime = Carbon::now()->subMinutes($minutesThreshold);

        GeneratorStatus::where('last_updated', '<', $cutoffTime)->delete();
    }

    /**
     * Clear manual power override for a generator
     */
    public function clearManualPowerOverride($generatorId)
    {
        return GeneratorStatus::where('generator_id', $generatorId)->delete();
    }
}
