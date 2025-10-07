<?php

namespace App\Services;

use App\Models\GeneratorRuntime;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\Generator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RuntimeTrackingService
{
    /**
     * Process generator logs and track runtime based on voltage from writelogs
     */
    public function processLogs()
    {
        // Get all generators that have write logs (prioritize writelogs for voltage tracking)
        $generators = GeneratorWriteLog::select('generator_id')
            ->distinct()
            ->get();

        foreach ($generators as $gen) {
            $this->processGeneratorWriteLogsChronologically($gen->generator_id);
        }
    }

    /**
     * Process write logs for a specific generator chronologically
     */
    private function processGeneratorWriteLogsChronologically($generatorId)
    {
        // Get the latest write log for this generator
        $latestLog = GeneratorWriteLog::where('generator_id', $generatorId)
            ->latest('write_timestamp')
            ->first(['generator_id', 'client_id', 'sitename', 'LV1', 'LV2', 'LV3', 'write_timestamp']);

        if (!$latestLog) {
            return;
        }

        // Process latest log with proper timestamp handling
        $this->processGeneratorRuntimeWithTimestamp($latestLog);
    }

    /**
     * Process runtime with proper timestamp handling - COMPLETE SINGLE SESSION LOGIC
     */
    private function processGeneratorRuntimeWithTimestamp($log)
    {
        $generatorId = $log->generator_id;
        $hasVoltage = $this->hasVoltage($log);
        $logTimestamp = $log->write_timestamp; // Use actual write log timestamp

        Log::info("Generator {$generatorId} - Processing log with voltage status: " . ($hasVoltage ? 'ON' : 'OFF') . " at actual timestamp: " . $logTimestamp);

        // Get current running session for this generator
        $currentRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);

        if ($hasVoltage && !$currentRuntime) {
            // Generator started - create new runtime record with actual write log timestamp
            $this->startRuntimeWithTimestamp($log, $logTimestamp);
            Log::info("Generator {$generatorId} STARTED - New session created at actual timestamp: " . $logTimestamp);
        } elseif (!$hasVoltage && $currentRuntime) {
            // Generator stopped - end current runtime with actual write log timestamp
            $this->stopRuntimeWithTimestamp($currentRuntime, $logTimestamp);
            Log::info("Generator {$generatorId} STOPPED - Session ended at actual timestamp: " . $logTimestamp);
        } elseif ($hasVoltage && $currentRuntime) {
            // Generator still running - DO NOT create new session, just log continuation
            Log::info("Generator {$generatorId} CONTINUING - Session running since " . $currentRuntime->start_time . " - Voltage: {$log->LV1}V, {$log->LV2}V, {$log->LV3}V");
        } elseif ($hasVoltage && !$currentRuntime) {
            // CRITICAL: Generator has voltage but no running session - CREATE NEW RUNNING SESSION
            $this->startRuntimeWithTimestamp($log, $logTimestamp);
            Log::info("Generator {$generatorId} RESTARTED - New running session created at actual timestamp: " . $logTimestamp);
        } else {
            // Generator is off and no session exists (do nothing)
            Log::info("Generator {$generatorId} OFF - No action needed");
        }
    }

    /**
     * Process runtime for a specific generator (old method - keeping for compatibility)
     */
    private function processGeneratorRuntime($log)
    {
        $generatorId = $log->generator_id;
        $hasVoltage = $this->hasVoltage($log);

        // Get current running session for this generator
        $currentRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);

        Log::info("Generator {$generatorId} - Processing log with voltage status: " . ($hasVoltage ? 'ON' : 'OFF') . " at " . $log->write_timestamp);

        if ($hasVoltage && !$currentRuntime) {
            // Generator started - create new runtime record ONLY if no running session exists
            $this->startRuntime($log);
            Log::info("Generator {$generatorId} STARTED - New session created at " . $log->write_timestamp);
        } elseif (!$hasVoltage && $currentRuntime) {
            // Generator stopped - end current runtime ONLY if voltage is actually 0
            $this->stopRuntime($currentRuntime, $log);
            Log::info("Generator {$generatorId} STOPPED - Session ended at " . $log->write_timestamp);
        } elseif ($hasVoltage && $currentRuntime) {
            // Generator still running - DO NOT create new session, just log continuation
            Log::info("Generator {$generatorId} CONTINUING - Session running since " . $currentRuntime->start_time);
        } elseif ($hasVoltage && !$currentRuntime) {
            // Special case: Generator has voltage but no running session (restart after stop)
            $this->startRuntime($log);
            Log::info("Generator {$generatorId} RESTARTED - New session created after voltage detected at " . $log->write_timestamp);
        } else {
            // Generator is off and no session exists (do nothing)
            Log::info("Generator {$generatorId} OFF - No action needed");
        }
    }

    /**
     * Check if generator has voltage on ALL lines (new requirement)
     */
    private function hasVoltage($log)
    {
        return ($log->LV1 > 0) && ($log->LV2 > 0) && ($log->LV3 > 0);
    }

    /**
     * Start a new runtime session with proper timestamp - SINGLE SESSION LOGIC
     */
    private function startRuntimeWithTimestamp($log, $timestamp)
    {
        try {
            // CRITICAL: Check if ANY running session exists for this generator
            $existingRunningRecord = GeneratorRuntime::where('generator_id', $log->generator_id)
                ->where('status', 'running')
                ->first();

            if ($existingRunningRecord) {
                Log::info("Runtime record already exists and running for generator {$log->generator_id}, skipping duplicate creation");
                return;
            }

            // SIMPLIFIED: Only check for running sessions, allow restart after stop

            GeneratorRuntime::create([
                'generator_id' => $log->generator_id,
                'client_id' => $log->client_id,
                'sitename' => $log->sitename,
                'start_time' => $timestamp, // Use actual write log timestamp
                'start_voltage_l1' => $log->LV1,
                'start_voltage_l2' => $log->LV2,
                'start_voltage_l3' => $log->LV3,
                'status' => 'running',
                'maintenance_status' => 'none',
                'notes' => 'Auto-started based on voltage detection (all voltages > 0)'
            ]);

            Log::info("Runtime started for generator {$log->generator_id} at actual timestamp: " . $timestamp);
        } catch (\Exception $e) {
            Log::error("Failed to start runtime for generator {$log->generator_id}: " . $e->getMessage());
        }
    }

    /**
     * Stop a running session with proper timestamp
     */
    private function stopRuntimeWithTimestamp($runtime, $timestamp)
    {
        try {
            // Set the end time to the actual write log timestamp
            $runtime->end_time = $timestamp;
            $runtime->status = 'stopped';
            $runtime->calculateDuration();
            $runtime->save();

            Log::info("Runtime stopped for generator {$runtime->generator_id} at actual timestamp: " . $timestamp . ". Duration: {$runtime->formatted_duration}");
        } catch (\Exception $e) {
            Log::error("Failed to stop runtime for generator {$runtime->generator_id}: " . $e->getMessage());
        }
    }

    /**
     * Start a new runtime session (old method - keeping for compatibility)
     */
    private function startRuntime($log)
    {
        try {
            // Check if ANY runtime record exists for this generator (running or stopped)
            $existingRecord = GeneratorRuntime::where('generator_id', $log->generator_id)
                ->latest('created_at')
                ->first();

            if ($existingRecord) {
                // If existing record is running, don't create duplicate
                if ($existingRecord->status === 'running') {
                    Log::info("Runtime record already exists and running for generator {$log->generator_id}, skipping duplicate");
                    return;
                }

                // If existing record is stopped, allow new session creation (restart scenario)
                Log::info("Existing stopped record found for generator {$log->generator_id}, creating new running session");
            }

            GeneratorRuntime::create([
                'generator_id' => $log->generator_id,
                'client_id' => $log->client_id,
                'sitename' => $log->sitename,
                'start_time' => $log->write_timestamp ?? $log->log_timestamp,
                'start_voltage_l1' => $log->LV1,
                'start_voltage_l2' => $log->LV2,
                'start_voltage_l3' => $log->LV3,
                'status' => 'running',
                'maintenance_status' => 'none', // Default maintenance status
                'notes' => 'Auto-started based on voltage detection (all voltages > 0)'
            ]);

            Log::info("Runtime started for generator {$log->generator_id} at " . ($log->write_timestamp ?? $log->log_timestamp));
        } catch (\Exception $e) {
            Log::error("Failed to start runtime for generator {$log->generator_id}: " . $e->getMessage());
        }
    }

    /**
     * Stop a running session
     */
    private function stopRuntime($runtime, $log)
    {
        try {
            // Set the end time to the log timestamp instead of now()
            $runtime->end_time = $log->write_timestamp ?? $log->log_timestamp;
            $runtime->status = 'stopped';

            if (isset($log->LV1)) $runtime->end_voltage_l1 = $log->LV1;
            if (isset($log->LV2)) $runtime->end_voltage_l2 = $log->LV2;
            if (isset($log->LV3)) $runtime->end_voltage_l3 = $log->LV3;

            $runtime->calculateDuration();
            $runtime->save();

            Log::info("Runtime stopped for generator {$log->generator_id} at " . ($log->write_timestamp ?? $log->log_timestamp) . ". Duration: {$runtime->formatted_duration}");
        } catch (\Exception $e) {
            Log::error("Failed to stop runtime for generator {$log->generator_id}: " . $e->getMessage());
        }
    }

    /**
     * Update running runtime (for future enhancements)
     */
    private function updateRunningRuntime($runtime, $log)
    {
        // For now, just log that it's still running
        // Could add features like updating max voltage, etc.
    }

    /**
     * Get current runtime for a generator
     */
    public function getCurrentRuntime($generatorId)
    {
        return GeneratorRuntime::getCurrentRuntime($generatorId);
    }

    /**
     * Get real-time duration for a running generator
     */
    public function getRealTimeDuration($generatorId)
    {
        $currentRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);

        if (!$currentRuntime || $currentRuntime->status !== 'running') {
            return null;
        }

        $durationSeconds = $currentRuntime->start_time->diffInSeconds(now());
        return $this->formatDuration($durationSeconds);
    }

    /**
     * Get runtime statistics for a generator
     */
    public function getRuntimeStats($generatorId, $days = 7)
    {
        $runtimes = GeneratorRuntime::byGenerator($generatorId)
            ->stopped()
            ->where('start_time', '>=', now()->subDays($days))
            ->get();

        $totalDuration = $runtimes->sum('duration_seconds');
        $sessionCount = $runtimes->count();
        $averageDuration = $sessionCount > 0 ? $totalDuration / $sessionCount : 0;

        return [
            'total_sessions' => $sessionCount,
            'total_duration_seconds' => $totalDuration,
            'total_duration_formatted' => $this->formatDuration($totalDuration),
            'average_duration_seconds' => $averageDuration,
            'average_duration_formatted' => $this->formatDuration($averageDuration),
            'runtimes' => $runtimes
        ];
    }

    /**
     * Get all currently running generators
     */
    public function getRunningGenerators()
    {
        return GeneratorRuntime::running()
            ->with(['generator', 'client'])
            ->get();
    }

    /**
     * Get runtime summary for dashboard
     */
    public function getDashboardSummary()
    {
        $runningCount = GeneratorRuntime::running()->count();
        $totalToday = GeneratorRuntime::whereDate('start_time', today())
            ->stopped()
            ->sum('duration_seconds');

        $totalThisWeek = GeneratorRuntime::where('start_time', '>=', now()->startOfWeek())
            ->stopped()
            ->sum('duration_seconds');

        return [
            'currently_running' => $runningCount,
            'total_today_seconds' => $totalToday,
            'total_today_formatted' => $this->formatDuration($totalToday),
            'total_week_seconds' => $totalThisWeek,
            'total_week_formatted' => $this->formatDuration($totalThisWeek),
        ];
    }

    /**
     * Format duration in seconds to readable format
     */
    public function formatDuration($seconds)
    {
        if (!$seconds) {
            return '0 hours';
        }

        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $result = '';

        if ($days > 0) {
            $result .= $days . ' day' . ($days > 1 ? 's' : '') . ' ';
        }

        if ($hours > 0) {
            $result .= $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ';
        }

        if ($minutes > 0) {
            $result .= $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ';
        }

        // If all are zero, show at least minutes
        if (empty(trim($result))) {
            $result = 'Less than 1 minute';
        }

        return trim($result);
    }

    /**
     * Clean up old runtime records (optional maintenance)
     */
    public function cleanupOldRecords($daysToKeep = 90)
    {
        $cutoffDate = now()->subDays($daysToKeep);

        $deletedCount = GeneratorRuntime::where('start_time', '<', $cutoffDate)
            ->where('status', 'stopped')
            ->delete();

        Log::info("Cleaned up {$deletedCount} old runtime records");
        return $deletedCount;
    }

    /**
     * Fix corrupted runtime records
     */
    public function fixCorruptedRecords()
    {
        // Find records that have both end_time and status = 'running'
        $corruptedRecords = GeneratorRuntime::where('status', 'running')
            ->whereNotNull('end_time')
            ->get();

        $fixedCount = 0;
        foreach ($corruptedRecords as $record) {
            $record->status = 'stopped';
            $record->save();
            $fixedCount++;

            Log::info("Fixed corrupted runtime record ID {$record->id} for generator {$record->generator_id}");
        }

        Log::info("Fixed {$fixedCount} corrupted runtime records");
        return $fixedCount;
    }
}
