<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneratorStatus;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\Client;
use App\Models\Generator;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with generator monitoring data
     */
    public function index()
    {
        // Get clients and generators for filtering
        $clients = Client::with('generators')->get();
        $generators = Generator::with('client')->get();

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

        $logs = $query->orderBy('log_timestamp', 'desc')->paginate(50); // Paginate logs with 50 per page

        // Get filter options
        $clients = Client::all();
        $generators = Generator::select('generator_id', 'name')->get();
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

        $writeLogs = $query->orderBy('write_timestamp', 'desc')->paginate(50); // Paginate write logs with 50 per page

        // Get filter options
        $clients = Client::all();
        $generators = Generator::select('generator_id', 'name')->get();
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
}
