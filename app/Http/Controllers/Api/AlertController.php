<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    protected $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Get all active alerts
     */
    public function index()
    {
        try {
            $alerts = Alert::with(['generator', 'client'])
                ->active()
                ->orderBy('triggered_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alerts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get alert statistics
     */
    public function stats()
    {
        try {
            $stats = [
                'total' => Alert::active()->count(),
                'by_severity' => Alert::active()
                    ->selectRaw('severity, COUNT(*) as count')
                    ->groupBy('severity')
                    ->pluck('count', 'severity')
                    ->toArray(),
                'by_type' => Alert::active()
                    ->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alert statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Acknowledge a specific alert
     */
    public function acknowledge(Request $request, $id)
    {
        try {
            $alert = Alert::find($id);

            if (!$alert) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alert not found'
                ], 404);
            }

            $alert->acknowledge(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Alert acknowledged successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to acknowledge alert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Acknowledge all active alerts
     */
    public function acknowledgeAll(Request $request)
    {
        try {
            $alerts = Alert::active()->get();
            $acknowledgedCount = 0;

            foreach ($alerts as $alert) {
                $alert->acknowledge(Auth::id());
                $acknowledgedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully acknowledged {$acknowledgedCount} alerts"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to acknowledge alerts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve a specific alert
     */
    public function resolve(Request $request, $id)
    {
        try {
            $alert = Alert::find($id);

            if (!$alert) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alert not found'
                ], 404);
            }

            $alert->resolve();

            return response()->json([
                'success' => true,
                'message' => 'Alert resolved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve alert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent alerts (last 24 hours)
     */
    public function recent()
    {
        try {
            $alerts = Alert::with(['generator', 'client'])
                ->where('triggered_at', '>=', now()->subDay())
                ->orderBy('triggered_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent alerts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for new alerts (trigger alert checking)
     */
    public function check()
    {
        try {
            $this->alertService->checkAlerts();

            $activeCount = Alert::active()->count();

            return response()->json([
                'success' => true,
                'message' => 'Alert check completed',
                'active_alerts' => $activeCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check alerts: ' . $e->getMessage()
            ], 500);
        }
    }
}
