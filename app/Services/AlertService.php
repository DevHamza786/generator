<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Check for alerts based on generator data
     */
    public function checkAlerts()
    {
        $this->checkFuelLevelAlerts();
        $this->checkBatteryVoltageAlerts();
        $this->checkLineCurrentAlerts();
    }

    /**
     * Check for fuel level alerts (below 20%)
     */
    private function checkFuelLevelAlerts()
    {
        // Get latest logs for all generators
        $latestLogs = GeneratorLog::select('generator_id', 'client_id', 'sitename', 'FL', 'log_timestamp')
            ->whereIn('id', function($query) {
                $query->selectRaw('MAX(id)')
                    ->from('generator_logs')
                    ->groupBy('generator_id');
            })
            ->get();

        foreach ($latestLogs as $log) {
            if ($log->FL < 20) {
                $this->createAlertIfNotExists(
                    $log->generator_id,
                    $log->client_id,
                    $log->sitename,
                    'fuel_low',
                    'Low Fuel Level Alert',
                    "Generator {$log->generator_id} fuel level is at {$log->FL}% (below 20% threshold)",
                    [
                        'fuel_level' => $log->FL,
                        'threshold' => 20,
                        'log_timestamp' => $log->log_timestamp
                    ],
                    'high'
                );
            } else {
                // Resolve any existing fuel level alerts for this generator
                $this->resolveAlerts($log->generator_id, 'fuel_low');
            }
        }
    }

    /**
     * Check for battery voltage alerts (11V constant for 30 minutes)
     */
    private function checkBatteryVoltageAlerts()
    {
        // Get logs from the last 30 minutes for all generators
        $thirtyMinutesAgo = Carbon::now()->subMinutes(30);

        $recentLogs = GeneratorLog::select('generator_id', 'client_id', 'sitename', 'BV', 'log_timestamp')
            ->where('log_timestamp', '>=', $thirtyMinutesAgo)
            ->orderBy('generator_id')
            ->orderBy('log_timestamp')
            ->get();

        // Group by generator_id
        $logsByGenerator = $recentLogs->groupBy('generator_id');

        foreach ($logsByGenerator as $generatorId => $logs) {
            $latestLog = $logs->last();

            // Check if all recent logs have BV = 11V
            $allElevenVolts = $logs->every(function($log) {
                return $log->BV == 11.0;
            });

            if ($allElevenVolts && $logs->count() >= 2) { // At least 2 readings in 30 minutes
                $this->createAlertIfNotExists(
                    $generatorId,
                    $latestLog->client_id,
                    $latestLog->sitename,
                    'battery_voltage',
                    'Battery Voltage Alert',
                    "Generator {$generatorId} battery voltage has been constant at 11V for 30+ minutes",
                    [
                        'battery_voltage' => 11.0,
                        'duration_minutes' => 30,
                        'log_count' => $logs->count(),
                        'first_log' => $logs->first()->log_timestamp,
                        'last_log' => $latestLog->log_timestamp
                    ],
                    'medium'
                );
            } else {
                // Resolve any existing battery voltage alerts for this generator
                $this->resolveAlerts($generatorId, 'battery_voltage');
            }
        }
    }

    /**
     * Check for line current alerts (greater than 1.20A)
     */
    private function checkLineCurrentAlerts()
    {
        // Get latest logs for all generators
        $latestLogs = GeneratorLog::select('generator_id', 'client_id', 'sitename', 'LI1', 'log_timestamp')
            ->whereIn('id', function($query) {
                $query->selectRaw('MAX(id)')
                    ->from('generator_logs')
                    ->groupBy('generator_id');
            })
            ->get();

        foreach ($latestLogs as $log) {
            if ($log->LI1 > 1.20) {
                $this->createAlertIfNotExists(
                    $log->generator_id,
                    $log->client_id,
                    $log->sitename,
                    'line_current',
                    'High Line Current Alert',
                    "Generator {$log->generator_id} line current is {$log->LI1}A (above 1.20A threshold). Running time will be managed.",
                    [
                        'line_current' => $log->LI1,
                        'threshold' => 1.20,
                        'log_timestamp' => $log->log_timestamp
                    ],
                    'medium'
                );
            } else {
                // Resolve any existing line current alerts for this generator
                $this->resolveAlerts($log->generator_id, 'line_current');
            }
        }
    }

    /**
     * Create alert if it doesn't already exist for the same generator and type
     */
    private function createAlertIfNotExists($generatorId, $clientId, $sitename, $type, $title, $message, $data, $severity)
    {
        $existingAlert = Alert::where('generator_id', $generatorId)
            ->where('type', $type)
            ->where('status', 'active')
            ->first();

        if (!$existingAlert) {
            Alert::create([
                'generator_id' => $generatorId,
                'client_id' => $clientId,
                'sitename' => $sitename,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'severity' => $severity,
                'status' => 'active',
                'triggered_at' => now(),
            ]);

            Log::info("Alert created: {$type} for generator {$generatorId}");
        }
    }

    /**
     * Resolve alerts for a specific generator and type
     */
    private function resolveAlerts($generatorId, $type)
    {
        $alerts = Alert::where('generator_id', $generatorId)
            ->where('type', $type)
            ->where('status', 'active')
            ->get();

        foreach ($alerts as $alert) {
            $alert->resolve();
            Log::info("Alert resolved: {$type} for generator {$generatorId}");
        }
    }

    /**
     * Get active alerts count
     */
    public function getActiveAlertsCount()
    {
        return Alert::active()->count();
    }

    /**
     * Get active alerts grouped by type
     */
    public function getActiveAlertsByType()
    {
        return Alert::active()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    /**
     * Get recent alerts
     */
    public function getRecentAlerts($limit = 10)
    {
        return Alert::with(['generator', 'client'])
            ->orderBy('triggered_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert($alertId, $userId = null)
    {
        $alert = Alert::find($alertId);
        if ($alert) {
            $alert->acknowledge($userId);
            return true;
        }
        return false;
    }

    /**
     * Resolve alert
     */
    public function resolveAlert($alertId)
    {
        $alert = Alert::find($alertId);
        if ($alert) {
            $alert->resolve();
            return true;
        }
        return false;
    }
}
