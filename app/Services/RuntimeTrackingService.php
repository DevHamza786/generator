<?php

namespace App\Services;

use App\Models\GeneratorRuntime;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\Generator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RuntimeTrackingService
{
    /**
     * Process generator logs and track runtime based on voltage from writelogs
     */
    public function processLogs()
    {
        try {
            // Get all generators that have write logs (prioritize writelogs for voltage tracking)
            $generators = GeneratorWriteLog::select('generator_id')
                ->distinct()
                ->get();

            foreach ($generators as $gen) {
                $this->processGeneratorWriteLogsChronologically($gen->generator_id);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'database is locked')) {
                Log::warning("Database temporarily locked, retrying in 1 second...");
                sleep(1); // Wait 1 second and retry
                $this->processLogs(); // Retry once
            } else {
                Log::error("Database error in processLogs: " . $e->getMessage());
                throw $e;
            }
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
     * Process runtime with proper timestamp handling - SINGLE SESSION LOGIC
     * Based on Excel scenarios: Only create ONE record per session, update until stopped
     * FIXED: Added debouncing to prevent multiple entries from voltage fluctuations
     */
    private function processGeneratorRuntimeWithTimestamp($log)
    {
        $generatorId = $log->generator_id;
        $hasVoltage = $this->hasVoltage($log);
        $logTimestamp = $log->write_timestamp; // Use actual write log timestamp

        // Get current running session for this generator
        $currentRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);

        // CRITICAL FIX: Check if we already processed this exact log entry recently (within 5 minutes)
        // BUT only skip if the existing record is in the correct status
        $existingRecord = GeneratorRuntime::where('generator_id', $generatorId)
            ->where('start_time', $logTimestamp)
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();
        
        if ($existingRecord) {
            // Only skip if the existing record status matches current voltage status
            $shouldBeRunning = $hasVoltage;
            $isCurrentlyRunning = ($existingRecord->status === 'running');
            
            if ($shouldBeRunning === $isCurrentlyRunning) {
                Log::info("Generator {$generatorId} - Skipping duplicate log entry - Status already correct at: " . $logTimestamp);
                return;
            } else {
                Log::info("Generator {$generatorId} - Status mismatch detected - Will update existing record");
            }
        }

        // DEBOUNCING: Only process if enough time has passed since last update OR if status changed
        if ($currentRuntime && $currentRuntime->updated_at && $currentRuntime->updated_at->diffInSeconds(now()) < 60) {
            // Check if voltage+current status actually changed
            $currentHasVoltage = ($currentRuntime->end_voltage_l1 > 100) || ($currentRuntime->end_voltage_l2 > 100) || ($currentRuntime->end_voltage_l3 > 100);
            // Note: We can't check current from runtime record, so we'll process if voltage status changed
            if ($currentHasVoltage === ($log->LV1 > 100 || $log->LV2 > 100 || $log->LV3 > 100)) {
                Log::info("Generator {$generatorId} - Skipping update (debouncing) - No voltage change - Last update: " . $currentRuntime->updated_at);
                return;
            }
        }

        Log::info("Generator {$generatorId} - Processing log with voltage status: " . ($hasVoltage ? 'ON' : 'OFF') . " at actual timestamp: " . $logTimestamp);

        if ($hasVoltage && !$currentRuntime) {
            // Scenario 1: Generator started OR Scenario 3: Generator restarted after being stopped
            $this->startRuntimeWithTimestamp($log, $logTimestamp);
            Log::info("Generator {$generatorId} STARTED/RESTARTED - New session created at actual timestamp: " . $logTimestamp);
        } elseif (!$hasVoltage && $currentRuntime) {
            // Scenario 2: Generator stopped - end current runtime (single session)
            $this->stopRuntimeWithTimestamp($currentRuntime, $logTimestamp);
            Log::info("Generator {$generatorId} STOPPED - Session ended at actual timestamp: " . $logTimestamp);
        } elseif ($hasVoltage && $currentRuntime) {
            // Generator still running - UPDATE the existing record instead of creating new
            $this->updateRunningRuntime($currentRuntime, $log, $logTimestamp);
            Log::info("Generator {$generatorId} CONTINUING - Session updated - Voltage: {$log->LV1}V, {$log->LV2}V, {$log->LV3}V");
        } elseif (!$hasVoltage && !$currentRuntime) {
            // Generator is off and no session exists - NO ACTION
            Log::info("Generator {$generatorId} OFF - No action needed");
        }
        
        // This ensures we only create ONE record per session until it's stopped
        // Scenario 3 (restart) is handled by the same logic as Scenario 1 (start)
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
     * Check if generator has voltage (generator is active/running)
     * Fixed: Generator is "running" if ANY voltage > 0, only "stopped" when voltage = 0
     */
    private function hasVoltage($log)
    {
        // Generator is considered "active/running" if it has ANY voltage above 0
        // Only "stopped" when voltage is actually 0 (as per user requirement)
        $hasVoltage = ($log->LV1 > 0) || ($log->LV2 > 0) || ($log->LV3 > 0);
        
        // Log detailed info for debugging
        if ($hasVoltage) {
            $hasCurrent = ($log->LI1 > 0.1) || ($log->LI2 > 0.1) || ($log->LI3 > 0.1);
            $mode = $hasCurrent ? 'RUNNING (with load)' : 'STANDBY (voltage but no load)';
            Log::info("Generator {$log->generator_id} - Generator ACTIVE: {$mode} - V: {$log->LV1}V, {$log->LV2}V, {$log->LV3}V - I: {$log->LI1}A, {$log->LI2}A, {$log->LI3}A");
        } else {
            Log::info("Generator {$log->generator_id} - Generator STOPPED (no voltage) - V: {$log->LV1}V, {$log->LV2}V, {$log->LV3}V");
        }
        
        return $hasVoltage;
    }

    /**
     * Start a new runtime session with proper timestamp - SINGLE SESSION LOGIC
     * Based on Excel scenarios: Only create ONE record per session
     */
    private function startRuntimeWithTimestamp($log, $timestamp)
    {
        try {
            // Use database transaction to prevent race conditions
            \DB::transaction(function () use ($log, $timestamp) {
                // CRITICAL: Double-check if ANY running session exists for this generator
                $existingRunningRecord = GeneratorRuntime::where('generator_id', $log->generator_id)
                    ->where('status', 'running')
                    ->lockForUpdate() // Lock the row to prevent concurrent access
                    ->first();

                if ($existingRunningRecord) {
                    Log::warning("CRITICAL: Runtime record already exists and running for generator {$log->generator_id}, skipping duplicate creation");
                    return;
                }

                // Create new runtime record - this should only happen when no running session exists
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
            });
        } catch (\Exception $e) {
            Log::error("Failed to start runtime for generator {$log->generator_id}: " . $e->getMessage());
        }
    }

    /**
     * Update a running runtime session instead of creating new ones
     * This prevents multiple entries for the same running session
     */
    private function updateRunningRuntime($runtime, $log, $timestamp)
    {
        try {
            // Update the existing running record instead of creating new one
            $runtime->end_time = $timestamp; // Update end time to current timestamp
            $runtime->end_voltage_l1 = $log->LV1;
            $runtime->end_voltage_l2 = $log->LV2;
            $runtime->end_voltage_l3 = $log->LV3;
            $runtime->calculateDuration();
            $runtime->save();

            Log::info("Runtime session {$runtime->id} UPDATED - Still running, duration: " . $runtime->duration_seconds . " seconds");
        } catch (\Exception $e) {
            Log::error("Failed to update runtime for generator {$runtime->generator_id}: " . $e->getMessage());
        }
    }

    /**
     * Stop a running session with proper timestamp
     * Based on Excel Scenario 2: End the current session completely
     */
    private function stopRuntimeWithTimestamp($runtime, $timestamp)
    {
        try {
            // Set the end time to the actual write log timestamp
            $runtime->end_time = $timestamp;
            $runtime->status = 'stopped';
            $runtime->end_voltage_l1 = 0; // Set end voltages to 0 as per Excel scenario
            $runtime->end_voltage_l2 = 0;
            $runtime->end_voltage_l3 = 0;
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
     * Get runtime summary for dashboard (optimized with caching)
     */
    public function getDashboardSummary()
    {
        return Cache::remember('dashboard_summary', 30, function () {
            // Optimized queries with single database hits
            $runningCount = GeneratorRuntime::where('status', 'running')->count();
            
            // Combined query for today and week totals
            $today = now()->startOfDay();
            $weekStart = now()->startOfWeek();
            
            $totals = GeneratorRuntime::selectRaw('
                SUM(CASE WHEN start_time >= ? AND status = "stopped" THEN duration_seconds ELSE 0 END) as today_total,
                SUM(CASE WHEN start_time >= ? AND status = "stopped" THEN duration_seconds ELSE 0 END) as week_total
            ', [$today, $weekStart])->first();

            return [
                'currently_running' => $runningCount,
                'total_today_seconds' => $totals->today_total ?? 0,
                'total_today_formatted' => $this->formatDuration($totals->today_total ?? 0),
                'total_week_seconds' => $totals->week_total ?? 0,
                'total_week_formatted' => $this->formatDuration($totals->week_total ?? 0),
            ];
        });
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

    /**
     * Get current status of all generators for debugging
     */
    public function getCurrentGeneratorStatus()
    {
        $runningGenerators = GeneratorRuntime::running()->get();
        $allGenerators = GeneratorWriteLog::select('generator_id')
            ->distinct()
            ->get();

        $status = [];
        foreach ($allGenerators as $gen) {
            $currentRuntime = GeneratorRuntime::getCurrentRuntime($gen->generator_id);
            $latestLog = GeneratorWriteLog::where('generator_id', $gen->generator_id)
                ->latest('write_timestamp')
                ->first();

            $status[] = [
                'generator_id' => $gen->generator_id,
                'has_running_session' => $currentRuntime ? true : false,
                'session_status' => $currentRuntime ? $currentRuntime->status : 'none',
                'latest_voltage' => $latestLog ? [
                    'LV1' => $latestLog->LV1,
                    'LV2' => $latestLog->LV2,
                    'LV3' => $latestLog->LV3,
                    'timestamp' => $latestLog->write_timestamp
                ] : null,
                'has_voltage' => $latestLog ? $this->hasVoltage($latestLog) : false
            ];
        }

        return $status;
    }

    /**
     * Clean up duplicate running records for generators
     * This ensures only ONE running record exists per generator
     */
    public function cleanupDuplicateRunningRecords()
    {
        $duplicateCount = 0;
        
        // Get all generators that have multiple running records
        $generatorsWithDuplicates = GeneratorRuntime::select('generator_id')
            ->where('status', 'running')
            ->groupBy('generator_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($generatorsWithDuplicates as $gen) {
            // Get all running records for this generator
            $runningRecords = GeneratorRuntime::where('generator_id', $gen->generator_id)
                ->where('status', 'running')
                ->orderBy('start_time', 'desc')
                ->get();

            // Keep only the latest one, delete the rest
            $keepRecord = $runningRecords->first();
            $toDelete = $runningRecords->skip(1);

            foreach ($toDelete as $record) {
                Log::warning("Deleting duplicate running record ID {$record->id} for generator {$gen->generator_id}");
                $record->delete();
                $duplicateCount++;
            }
        }

        Log::info("Cleaned up {$duplicateCount} duplicate running records");
        return $duplicateCount;
    }
}
