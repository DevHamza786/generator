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
        // Get the last processed log timestamp for this generator
        $lastProcessed = GeneratorRuntime::where('generator_id', $generatorId)
            ->latest('start_time')
            ->value('start_time');

        // Get unprocessed write logs (logs after the last processed runtime)
        $query = GeneratorWriteLog::where('generator_id', $generatorId)
            ->orderBy('write_timestamp');

        if ($lastProcessed) {
            $query->where('write_timestamp', '>', $lastProcessed);
        }

        $unprocessedLogs = $query->get(['generator_id', 'client_id', 'sitename', 'LV1', 'LV2', 'LV3', 'write_timestamp']);

        if ($unprocessedLogs->isEmpty()) {
            return;
        }

        // Process logs in chronological order
        foreach ($unprocessedLogs as $log) {
            $this->processGeneratorRuntime($log);
        }
    }

    /**
     * Process runtime for a specific generator
     */
    private function processGeneratorRuntime($log)
    {
        $generatorId = $log->generator_id;
        $hasVoltage = $this->hasVoltage($log);

        // Get current running session for this generator
        $currentRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);

        if ($hasVoltage && !$currentRuntime) {
            // Generator started - create new runtime record
            $this->startRuntime($log);
        } elseif (!$hasVoltage && $currentRuntime) {
            // Generator stopped - end current runtime
            $this->stopRuntime($currentRuntime, $log);
        } elseif ($hasVoltage && $currentRuntime) {
            // Generator still running - update if needed
            $this->updateRunningRuntime($currentRuntime, $log);
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
     * Start a new runtime session
     */
    private function startRuntime($log)
    {
        try {
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
