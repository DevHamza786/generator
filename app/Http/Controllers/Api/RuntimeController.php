<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneratorRuntime;
use App\Services\RuntimeTrackingService;
use Illuminate\Http\Request;

class RuntimeController extends Controller
{
    protected $runtimeService;

    public function __construct(RuntimeTrackingService $runtimeService)
    {
        $this->runtimeService = $runtimeService;
    }

    /**
     * Get runtime summary for dashboard
     */
    public function summary()
    {
        try {
            $summary = $this->runtimeService->getDashboardSummary();

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch runtime summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currently running generators
     */
    public function running()
    {
        try {
            $runningGenerators = $this->runtimeService->getRunningGenerators();

            return response()->json([
                'success' => true,
                'data' => $runningGenerators
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch running generators: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get runtime statistics for a specific generator
     */
    public function generatorStats(Request $request, $generatorId)
    {
        try {
            $days = $request->get('days', 7);
            $stats = $this->runtimeService->getRuntimeStats($generatorId, $days);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch generator runtime stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all runtime records with pagination
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $generatorId = $request->get('generator_id');
            $status = $request->get('status');
            $days = $request->get('days', 7);

            $query = GeneratorRuntime::with(['generator', 'client'])
                ->where('start_time', '>=', now()->subDays($days));

            if ($generatorId) {
                $query->where('generator_id', $generatorId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $runtimes = $query->orderBy('start_time', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $runtimes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch runtime records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get runtime record by ID
     */
    public function show($id)
    {
        try {
            $runtime = GeneratorRuntime::with(['generator', 'client'])->find($id);

            if (!$runtime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Runtime record not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $runtime
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch runtime record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually stop a running generator
     */
    public function stop(Request $request, $id)
    {
        try {
            $runtime = GeneratorRuntime::find($id);

            if (!$runtime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Runtime record not found'
                ], 404);
            }

            if ($runtime->status !== 'running') {
                return response()->json([
                    'success' => false,
                    'message' => 'Generator is not currently running'
                ], 400);
            }

            $runtime->stop();

            return response()->json([
                'success' => true,
                'message' => 'Generator runtime stopped successfully',
                'data' => $runtime
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop generator runtime: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process logs and update runtime tracking
     */
    public function process()
    {
        try {
            $this->runtimeService->processLogs();

            return response()->json([
                'success' => true,
                'message' => 'Runtime tracking processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process runtime tracking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get runtime analytics
     */
    public function analytics(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $generatorId = $request->get('generator_id');

            $query = GeneratorRuntime::where('start_time', '>=', now()->subDays($days))
                ->where('status', 'stopped');

            if ($generatorId) {
                $query->where('generator_id', $generatorId);
            }

            $runtimes = $query->get();

            $analytics = [
                'total_sessions' => $runtimes->count(),
                'total_duration_seconds' => $runtimes->sum('duration_seconds'),
                'average_duration_seconds' => $runtimes->avg('duration_seconds'),
                'longest_session_seconds' => $runtimes->max('duration_seconds'),
                'shortest_session_seconds' => $runtimes->min('duration_seconds'),
                'daily_breakdown' => $this->getDailyBreakdown($runtimes),
                'generator_breakdown' => $this->getGeneratorBreakdown($runtimes),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch runtime analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily breakdown of runtime
     */
    private function getDailyBreakdown($runtimes)
    {
        return $runtimes->groupBy(function($runtime) {
            return $runtime->start_time->format('Y-m-d');
        })->map(function($dayRuntimes) {
            return [
                'sessions' => $dayRuntimes->count(),
                'total_duration_seconds' => $dayRuntimes->sum('duration_seconds'),
                'average_duration_seconds' => $dayRuntimes->avg('duration_seconds'),
            ];
        });
    }

    /**
     * Get generator breakdown of runtime
     */
    private function getGeneratorBreakdown($runtimes)
    {
        return $runtimes->groupBy('generator_id')->map(function($generatorRuntimes) {
            return [
                'sessions' => $generatorRuntimes->count(),
                'total_duration_seconds' => $generatorRuntimes->sum('duration_seconds'),
                'average_duration_seconds' => $generatorRuntimes->avg('duration_seconds'),
            ];
        });
    }
}
