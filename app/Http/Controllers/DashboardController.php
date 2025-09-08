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

        $logs = $query->orderBy('log_timestamp', 'desc')->get(); // Get all logs without pagination, ordered by latest first

        // Get filter options
        $clients = Client::all();
        $generatorIds = Generator::distinct()->pluck('generator_id')->sort();

        return view('logs', compact('logs', 'clients', 'generatorIds'));
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

        $writeLogs = $query->orderBy('write_timestamp', 'desc')->get(); // Get all write logs without pagination, ordered by latest first

        // Get filter options
        $clients = Client::all();
        $writeLogGeneratorIds = Generator::distinct()->pluck('generator_id')->sort();

        return view('write-logs', compact('writeLogs', 'clients', 'writeLogGeneratorIds'));
    }
}
