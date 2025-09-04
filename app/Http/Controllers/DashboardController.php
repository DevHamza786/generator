<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneratorStatus;
use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with generator monitoring data
     */
    public function index()
    {
        $generatorStatus = GeneratorStatus::latest()->first();
        $latestLogs = GeneratorLog::latest()->limit(20)->get();
        $latestWriteLogs = GeneratorWriteLog::latest()->limit(20)->get();

        return view('dashboard', compact('generatorStatus', 'latestLogs', 'latestWriteLogs'));
    }
}
