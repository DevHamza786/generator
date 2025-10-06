<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneratorStatus;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\GeneratorRuntime;
use App\Models\Client;
use App\Models\Generator;
use App\Services\RuntimeTrackingService;
use App\Services\DeviceStatusService;

class DashboardController extends Controller
{
    protected $deviceStatusService;

    public function __construct(DeviceStatusService $deviceStatusService)
    {
        $this->deviceStatusService = $deviceStatusService;
    }

    /**
     * Display the dashboard with generator monitoring data
     */
    public function index()
    {
        // Get clients and generators for filtering
        $clients = Client::with('generators')->get();
        $generators = Generator::with('client')->select('id', 'generator_id', 'name', 'sitename', 'kva_power', 'client_id')->get();

        // Get latest logs with client and generator info
        $latestLogs = GeneratorLog::with(['client', 'generator'])
            ->latest('log_timestamp')
            ->limit(20)
            ->get();

        // Get latest write logs with client and generator info
        $latestWriteLogs = GeneratorWriteLog::with(['client', 'generator'])
            ->latest('write_timestamp')
            ->limit(20)
            ->get();

        // Get statistics
        $totalClients = Client::count();
        $totalGenerators = Generator::count();
        $totalLogs = GeneratorLog::count();
        $totalWriteLogs = GeneratorWriteLog::count();

        // Get active generators count based on recent data (1 minute threshold)
        $activeGenerators = $this->deviceStatusService->getActiveGeneratorsCount(1);

        // Get running generators count (generators with GS=true in recent logs)
        $runningGenerators = GeneratorLog::where('GS', true)
            ->where('log_timestamp', '>=', now()->subMinutes(5))
            ->distinct('generator_id')
            ->count();

        // Get all generator statuses
        $generatorStatuses = $this->deviceStatusService->getAllGeneratorsStatus(1);

        // Create a mock generator status for backward compatibility
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

        $generatorStatus = (object) [
            'power' => $overallPower,
            'last_updated' => $lastUpdated ?: now()
        ];

        return view('dashboard', compact(
            'clients',
            'generators',
            'latestLogs',
            'latestWriteLogs',
            'totalClients',
            'totalGenerators',
            'totalLogs',
            'totalWriteLogs',
            'activeGenerators',
            'runningGenerators',
            'generatorStatus',
            'generatorStatuses'
        ));
    }

    /**
     * Display all generator logs with filtering and pagination
     */
    public function logs(Request $request)
    {
        $query = GeneratorLog::with(['client', 'generator']);

        // Apply filters
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('generator_id')) {
            $query->where('generator_id', $request->generator_id);
        }

        if ($request->filled('sitename')) {
            $query->where('sitename', $request->sitename);
        }

        if ($request->filled('date')) {
            $query->whereDate('log_timestamp', $request->date);
        }

        if ($request->filled('status')) {
            if ($request->status === 'running') {
                $query->where('GS', true);
            } elseif ($request->status === 'stopped') {
                $query->where('GS', false);
            }
        }

        $perPage = $request->get('per_page', 50); // Default to 50, allow 20, 50, 100
        $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 50;

        // Handle sorting
        $sortBy = $request->get('sort_by', 'log_timestamp');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $logs = $query->orderBy($sortBy, $sortDirection)->paginate($perPage);

        // Get filter options
        $clients = Client::all();
        $generators = Generator::select('id', 'generator_id', 'name', 'sitename', 'kva_power')->get();
        $generatorIds = $generators->pluck('generator_id')->sort();

        return view('logs', compact('logs', 'clients', 'generatorIds', 'generators'));
    }

    /**
     * Display all generator write logs with filtering and pagination
     */
    public function writeLogs(Request $request)
    {
        $query = GeneratorWriteLog::with(['client', 'generator']);

        // Apply filters
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('generator_id')) {
            $query->where('generator_id', $request->generator_id);
        }

        if ($request->filled('sitename')) {
            $query->where('sitename', $request->sitename);
        }

        if ($request->filled('date')) {
            $query->whereDate('write_timestamp', $request->date);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('PS', true);
            } elseif ($request->status === 'inactive') {
                $query->where('PS', false);
            }
        }

        $perPage = $request->get('per_page', 50); // Default to 50, allow 20, 50, 100
        $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 50;

        // Handle sorting
        $sortBy = $request->get('sort_by', 'write_timestamp');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $writeLogs = $query->orderBy($sortBy, $sortDirection)->paginate($perPage);

        // Get filter options
        $clients = Client::all();
        $generators = Generator::select('id', 'generator_id', 'name', 'sitename', 'kva_power')->get();
        $writeLogGeneratorIds = $generators->pluck('generator_id')->sort();

        return view('write-logs', compact('writeLogs', 'clients', 'writeLogGeneratorIds', 'generators'));
    }

    /**
     * Toggle generator power status
     */
    public function togglePower(Request $request)
    {
        $request->validate([
            'generator_id' => 'required|string',
            'power' => 'required|in:true,false,1,0,"true","false"'
        ]);

        try {
            $generatorId = $request->generator_id;
            $power = filter_var($request->power, FILTER_VALIDATE_BOOLEAN);

            // Check if generator exists
            $generator = Generator::where('generator_id', $generatorId)->first();
            if (!$generator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generator not found.'
                ], 404);
            }

            if ($power) {
                // Turn ON: Set manual power override
                $this->deviceStatusService->setManualPowerOverride($generatorId, $power);
                $message = "Generator {$generatorId} powered ON (Manual Override - will stay ON until manually turned OFF)";
            } else {
                // Turn OFF: Clear manual override and let real-time data take over
                $this->deviceStatusService->clearManualPowerOverride($generatorId);
                $message = "Generator {$generatorId} powered OFF (Real-time data will now control the status)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'generator_id' => $generatorId,
                    'power' => $power
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error setting power status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get power status for generators
     */
    public function getPowerStatus(Request $request)
    {
        try {
            $generatorIds = $request->input('ids', []);

            if (empty($generatorIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No generator IDs provided.'
                ], 400);
            }

            $powerStatus = [];
            foreach ($generatorIds as $generatorId) {
                $status = $this->deviceStatusService->getDeviceStatus($generatorId, 1);
                $powerStatus[$generatorId] = [
                    'power' => $status['power_status']
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $powerStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting power status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the preventive maintenance page
     */
    public function preventiveMaintenance()
    {
        // Get all generators with their runtime data
        $generators = Generator::with(['client', 'latestLog'])->get();

        // Get runtime statistics for each generator
        $runtimeService = app(RuntimeTrackingService::class);
        $maintenanceData = [];

        // Get maintenance status for each generator
        $maintenanceStatuses = [];
        foreach ($generators as $generator) {
            $currentRuntime = GeneratorRuntime::getCurrentRuntime($generator->generator_id);
            $maintenanceStatuses[$generator->generator_id] = $currentRuntime ? $currentRuntime->maintenance_status : 'none';
        }

        foreach ($generators as $generator) {
            $runtimeStats = $runtimeService->getRuntimeStats($generator->generator_id, 30); // Last 30 days
            $currentRuntime = GeneratorRuntime::getCurrentRuntime($generator->generator_id);

            // Calculate maintenance recommendations
            $maintenanceRecommendations = $this->calculateMaintenanceRecommendations($generator, $runtimeStats, $currentRuntime);

            $maintenanceData[] = [
                'generator' => $generator,
                'runtime_stats' => $runtimeStats,
                'current_runtime' => $currentRuntime,
                'recommendations' => $maintenanceRecommendations,
                'last_maintenance' => $this->getLastMaintenanceDate($generator->generator_id),
                'next_maintenance' => $this->getNextMaintenanceDate($generator->generator_id, $runtimeStats),
            ];
        }

        // Get maintenance summary
        $maintenanceSummary = $this->getMaintenanceSummary($maintenanceData);

        return view('preventive-maintenance', compact('maintenanceData', 'maintenanceSummary', 'maintenanceStatuses'));
    }

    /**
     * Calculate maintenance recommendations for a generator
     */
    private function calculateMaintenanceRecommendations($generator, $runtimeStats, $currentRuntime)
    {
        $recommendations = [];

        // Check runtime hours
        $totalHours = $runtimeStats['total_duration_seconds'] / 3600;
        if ($totalHours > 500) {
            $recommendations[] = [
                'type' => 'runtime',
                'priority' => 'high',
                'message' => 'Generator has exceeded 500 runtime hours. Schedule major maintenance.',
                'action' => 'Schedule oil change, filter replacement, and full inspection'
            ];
        } elseif ($totalHours > 250) {
            $recommendations[] = [
                'type' => 'runtime',
                'priority' => 'medium',
                'message' => 'Generator approaching 500 runtime hours. Plan maintenance soon.',
                'action' => 'Schedule preventive maintenance within 2 weeks'
            ];
        }

        // Check current runtime
        if ($currentRuntime) {
            $currentHours = $currentRuntime->start_time->diffInHours(now());
            if ($currentHours > 24) {
                $recommendations[] = [
                    'type' => 'continuous_runtime',
                    'priority' => 'critical',
                    'message' => 'Generator has been running continuously for ' . $currentHours . ' hours.',
                    'action' => 'Immediate shutdown recommended for cooling and inspection'
                ];
            } elseif ($currentHours > 12) {
                $recommendations[] = [
                    'type' => 'continuous_runtime',
                    'priority' => 'high',
                    'message' => 'Generator has been running for ' . $currentHours . ' hours.',
                    'action' => 'Monitor closely and plan shutdown for maintenance'
                ];
            }
        }

        // Check frequency of use
        if ($runtimeStats['total_sessions'] > 50) {
            $recommendations[] = [
                'type' => 'frequency',
                'priority' => 'medium',
                'message' => 'High frequency of starts/stops (' . $runtimeStats['total_sessions'] . ' sessions).',
                'action' => 'Check starter motor and battery condition'
            ];
        }

        return $recommendations;
    }

    /**
     * Get last maintenance date for a generator
     */
    private function getLastMaintenanceDate($generatorId)
    {
        // Get the most recent runtime record with maintenance completed timestamp
        $lastMaintenance = GeneratorRuntime::where('generator_id', $generatorId)
            ->whereNotNull('maintenance_completed_at')
            ->orderBy('maintenance_completed_at', 'desc')
            ->first();

        return $lastMaintenance ? $lastMaintenance->maintenance_completed_at : null;
    }

    /**
     * Get next maintenance date for a generator
     */
    private function getNextMaintenanceDate($generatorId, $runtimeStats)
    {
        $lastMaintenance = $this->getLastMaintenanceDate($generatorId);

        if ($lastMaintenance) {
            // Calculate next maintenance based on last maintenance date + 30 days
            return $lastMaintenance->addDays(30);
        } else {
            // If no previous maintenance, calculate based on runtime hours
            $totalHours = $runtimeStats['total_duration_seconds'] / 3600;
            $hoursUntilMaintenance = 500 - ($totalHours % 500);
            $daysUntilMaintenance = $hoursUntilMaintenance / 24; // Assuming 24 hours per day

            return now()->addDays($daysUntilMaintenance);
        }
    }

    /**
     * Get maintenance summary for all generators
     */
    private function getMaintenanceSummary($maintenanceData)
    {
        $summary = [
            'total_generators' => count($maintenanceData),
            'overdue_maintenance' => 0,
            'due_soon' => 0,
            'critical_alerts' => 0,
            'high_priority' => 0,
            'medium_priority' => 0,
        ];

        foreach ($maintenanceData as $data) {
            foreach ($data['recommendations'] as $recommendation) {
                switch ($recommendation['priority']) {
                    case 'critical':
                        $summary['critical_alerts']++;
                        break;
                    case 'high':
                        $summary['high_priority']++;
                        break;
                    case 'medium':
                        $summary['medium_priority']++;
                        break;
                }
            }

            if ($data['next_maintenance'] && $data['next_maintenance']->isPast()) {
                $summary['overdue_maintenance']++;
            } elseif ($data['next_maintenance'] && $data['next_maintenance']->diffInDays(now()) <= 7) {
                $summary['due_soon']++;
            }
        }

        return $summary;
    }

    /**
     * Update maintenance status for a generator runtime
     */
    public function updateMaintenanceStatus(Request $request)
    {
        $request->validate([
            'generator_id' => 'required|string',
            'maintenance_status' => 'required|in:none,scheduled,overdue,in_progress,completed'
        ]);

        try {
            $generatorId = $request->generator_id;
            $maintenanceStatus = $request->maintenance_status;

            // Check if generator exists
            $generator = Generator::where('generator_id', $generatorId)->first();
            if (!$generator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generator not found.'
                ], 404);
            }

            // Get current runtime
            $currentRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);

            if ($currentRuntime) {
                // Stop the current runtime and start a new one with updated maintenance status
                $this->handleMaintenanceRuntimeTransition($currentRuntime, $maintenanceStatus, $generator);
            } else {
                // If no current runtime, create a new one with the maintenance status
                $this->createMaintenanceRuntime($generatorId, $maintenanceStatus, $generator);
            }

            // Get the updated runtime for response
            $updatedRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);

            return response()->json([
                'success' => true,
                'message' => "Maintenance status updated to " . ucfirst(str_replace('_', ' ', $maintenanceStatus)) . ". Runtime transitioned.",
                'data' => [
                    'generator_id' => $generatorId,
                    'maintenance_status' => $maintenanceStatus,
                    'maintenance_status_text' => $updatedRuntime ? $updatedRuntime->maintenance_status_text : 'No maintenance required',
                    'maintenance_status_icon' => $updatedRuntime ? $updatedRuntime->maintenance_status_icon : 'fas fa-check-circle text-success',
                    'maintenance_status_badge_class' => $updatedRuntime ? $updatedRuntime->maintenance_status_badge_class : 'badge-secondary'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating maintenance status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle runtime transition when maintenance status is updated
     */
    private function handleMaintenanceRuntimeTransition($currentRuntime, $newMaintenanceStatus, $generator)
    {
        // Stop the current runtime
        $currentRuntime->stop([
            'LV1' => 0, // Set to 0 to indicate maintenance transition
            'LV2' => 0,
            'LV3' => 0
        ]);

        // Update the stopped runtime's maintenance status and maintenance time
        $currentRuntime->maintenance_status = $newMaintenanceStatus;
        $currentRuntime->notes = 'Stopped for maintenance status update: ' . ucfirst(str_replace('_', ' ', $newMaintenanceStatus));

        // Update maintenance time based on status
        if ($newMaintenanceStatus === 'completed') {
            $currentRuntime->maintenance_completed_at = now();
        } elseif ($newMaintenanceStatus === 'in_progress') {
            $currentRuntime->maintenance_started_at = now();
        }

        $currentRuntime->save();

        // Start a new runtime with the new maintenance status
        GeneratorRuntime::create([
            'generator_id' => $generator->generator_id,
            'client_id' => $generator->client_id,
            'sitename' => $generator->sitename,
            'start_time' => now(),
            'start_voltage_l1' => 0, // Will be updated when real voltage data comes in
            'start_voltage_l2' => 0,
            'start_voltage_l3' => 0,
            'status' => 'running',
            'maintenance_status' => $newMaintenanceStatus,
            'notes' => 'Started after maintenance status update: ' . ucfirst(str_replace('_', ' ', $newMaintenanceStatus))
        ]);

        \Log::info("Runtime transitioned for generator {$generator->generator_id} due to maintenance status change to {$newMaintenanceStatus}");
    }

    /**
     * Create a new runtime with maintenance status
     */
    private function createMaintenanceRuntime($generatorId, $maintenanceStatus, $generator)
    {
        GeneratorRuntime::create([
            'generator_id' => $generatorId,
            'client_id' => $generator->client_id,
            'sitename' => $generator->sitename,
            'start_time' => now(),
            'start_voltage_l1' => 0,
            'start_voltage_l2' => 0,
            'start_voltage_l3' => 0,
            'status' => 'running',
            'maintenance_status' => $maintenanceStatus,
            'notes' => 'Created with maintenance status: ' . ucfirst(str_replace('_', ' ', $maintenanceStatus))
        ]);

        \Log::info("New runtime created for generator {$generatorId} with maintenance status: {$maintenanceStatus}");
    }

    /**
     * Get maintenance status for a generator
     */
    public function getMaintenanceStatus(Request $request)
    {
        try {
            $generatorId = $request->input('generator_id');

            if (!$generatorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generator ID is required.'
                ], 400);
            }

            $currentRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);
            $maintenanceStatus = $currentRuntime ? $currentRuntime->maintenance_status : 'none';

            return response()->json([
                'success' => true,
                'data' => [
                    'generator_id' => $generatorId,
                    'maintenance_status' => $maintenanceStatus,
                    'maintenance_status_text' => $currentRuntime ? $currentRuntime->maintenance_status_text : 'No maintenance required',
                    'maintenance_status_icon' => $currentRuntime ? $currentRuntime->maintenance_status_icon : 'fas fa-check-circle text-success',
                    'maintenance_status_badge_class' => $currentRuntime ? $currentRuntime->maintenance_status_badge_class : 'badge-secondary'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting maintenance status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get runtime details for a specific generator
     */
    public function getRuntimeDetails($generatorId)
    {
        try {
            // Check if generator exists
            $generator = Generator::where('generator_id', $generatorId)->first();
            if (!$generator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Generator not found'
                ], 404);
            }

            // Get current runtime
            $currentRuntime = GeneratorRuntime::getCurrentRuntime($generatorId);

            // Get recent runtime history (last 30 days)
            $runtimeHistory = GeneratorRuntime::where('generator_id', $generatorId)
                ->where('start_time', '>=', now()->subDays(30))
                ->orderBy('start_time', 'desc')
                ->limit(50)
                ->get();

            // Get runtime statistics
            $runtimeService = app(RuntimeTrackingService::class);
            $stats = $runtimeService->getRuntimeStats($generatorId, 30);

            // Get recent write logs for voltage data
            $recentLogs = GeneratorWriteLog::where('generator_id', $generatorId)
                ->latest('write_timestamp')
                ->limit(20)
                ->get(['write_timestamp', 'LV1', 'LV2', 'LV3', 'PS', 'FL']);

            return response()->json([
                'success' => true,
                'data' => [
                    'generator' => [
                        'id' => $generator->generator_id,
                        'name' => $generator->name,
                        'sitename' => $generator->sitename,
                        'kva_power' => $generator->kva_power,
                        'client_name' => $generator->client->name ?? 'Unknown'
                    ],
                    'current_runtime' => $currentRuntime ? [
                        'start_time' => $currentRuntime->start_time,
                        'duration' => $currentRuntime->formatted_duration,
                        'duration_seconds' => $currentRuntime->duration_seconds,
                        'status' => $currentRuntime->status,
                        'maintenance_status' => $currentRuntime->maintenance_status,
                        'maintenance_started_at' => $currentRuntime->maintenance_started_at,
                        'maintenance_completed_at' => $currentRuntime->maintenance_completed_at,
                        'start_voltages' => [
                            'LV1' => $currentRuntime->start_voltage_l1,
                            'LV2' => $currentRuntime->start_voltage_l2,
                            'LV3' => $currentRuntime->start_voltage_l3
                        ]
                    ] : null,
                    'runtime_history' => $runtimeHistory->map(function ($runtime) {
                        return [
                            'start_time' => $runtime->start_time,
                            'end_time' => $runtime->end_time,
                            'duration' => $runtime->formatted_duration,
                            'duration_seconds' => $runtime->duration_seconds,
                            'status' => $runtime->status,
                            'maintenance_status' => $runtime->maintenance_status,
                            'maintenance_started_at' => $runtime->maintenance_started_at,
                            'maintenance_completed_at' => $runtime->maintenance_completed_at,
                            'start_voltages' => [
                                'LV1' => $runtime->start_voltage_l1,
                                'LV2' => $runtime->start_voltage_l2,
                                'LV3' => $runtime->start_voltage_l3
                            ],
                            'end_voltages' => [
                                'LV1' => $runtime->end_voltage_l1,
                                'LV2' => $runtime->end_voltage_l2,
                                'LV3' => $runtime->end_voltage_l3
                            ],
                            'notes' => $runtime->notes
                        ];
                    }),
                    'statistics' => $stats,
                    'recent_logs' => $recentLogs->map(function ($log) {
                        return [
                            'timestamp' => $log->write_timestamp,
                            'voltages' => [
                                'LV1' => $log->LV1,
                                'LV2' => $log->LV2,
                                'LV3' => $log->LV3
                            ],
                            'power_status' => $log->PS,
                            'frequency' => $log->FL
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting runtime details: ' . $e->getMessage()
            ], 500);
        }
    }
}
