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

class DashboardController extends Controller
{
    /**
     * Display the dashboard with generator monitoring data
     */
    public function index()
    {
        // Get clients and generators for filtering
        $clients = Client::with('generators')->get();
        $generators = Generator::with('client')->select('generator_id', 'name', 'sitename', 'client_id')->get();

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

        // Get running generators count
        $runningGenerators = GeneratorLog::where('GS', true)
            ->distinct('generator_id')
            ->count();

        // Get latest generator status
        $generatorStatus = GeneratorStatus::latest()->first();

        return view('dashboard', compact(
            'clients',
            'generators',
            'latestLogs',
            'latestWriteLogs',
            'totalClients',
            'totalGenerators',
            'totalLogs',
            'totalWriteLogs',
            'runningGenerators',
            'generatorStatus'
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
            $query->whereHas('generator', function($q) use ($request) {
                $q->where('generator_id', $request->generator_id);
            });
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
        $logs = $query->orderBy('log_timestamp', 'desc')->paginate($perPage);

        // Get filter options
        $clients = Client::all();
        $generators = Generator::select('generator_id', 'name', 'sitename')->get();
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
            $query->whereHas('generator', function($q) use ($request) {
                $q->where('generator_id', $request->generator_id);
            });
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
        $writeLogs = $query->orderBy('write_timestamp', 'desc')->paginate($perPage);

        // Get filter options
        $clients = Client::all();
        $generators = Generator::select('generator_id', 'name', 'sitename')->get();
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

            // Update or create generator status
            GeneratorStatus::updateOrCreate(
                ['generator_id' => $generatorId],
                [
                    'power' => $power,
                    'last_updated' => now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Generator {$generatorId} power set to " . ($power ? 'ON' : 'OFF'),
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
                $status = GeneratorStatus::where('generator_id', $generatorId)
                    ->latest('last_updated')
                    ->first();

                $powerStatus[$generatorId] = [
                    'power' => $status ? $status->power : false
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

        return view('preventive-maintenance', compact('maintenanceData', 'maintenanceSummary'));
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
        // This would typically come from a maintenance records table
        // For now, we'll simulate based on runtime data
        $lastRuntime = GeneratorRuntime::where('generator_id', $generatorId)
            ->where('status', 'stopped')
            ->orderBy('end_time', 'desc')
            ->first();

        return $lastRuntime ? $lastRuntime->end_time->subDays(rand(7, 30)) : null;
    }

    /**
     * Get next maintenance date for a generator
     */
    private function getNextMaintenanceDate($generatorId, $runtimeStats)
    {
        $lastMaintenance = $this->getLastMaintenanceDate($generatorId);
        $totalHours = $runtimeStats['total_duration_seconds'] / 3600;

        // Calculate next maintenance based on hours
        $hoursUntilMaintenance = 500 - ($totalHours % 500);
        $daysUntilMaintenance = $hoursUntilMaintenance / 24; // Assuming 24 hours per day

        return now()->addDays($daysUntilMaintenance);
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
}
