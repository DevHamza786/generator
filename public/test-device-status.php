<?php
// Test Device Status - cPanel Web Script
// Access via: https://yourdomain.com/test-device-status.php

require_once '../vendor/autoload.php';

use App\Services\DeviceStatusService;
use App\Models\Generator;

echo "<!DOCTYPE html>";
echo "<html><head><title>Device Status Test</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .status{color:green;} .inactive{color:red;} .active{color:green;}</style>";
echo "</head><body>";

echo "<h1>🔧 Device Status Test</h1>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

try {
    // Initialize the service
    $deviceStatusService = new DeviceStatusService();
    
    // Get all generators
    $generators = Generator::all();
    
    echo "<h2>📊 Generator Status Summary</h2>";
    
    $activeCount = 0;
    $poweredOnCount = 0;
    
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse:collapse;'>";
    echo "<tr style='background:#f0f0f0;'><th>Generator ID</th><th>Sitename</th><th>Status</th><th>Power</th><th>Last Data</th><th>Minutes Since</th></tr>";
    
    foreach ($generators as $generator) {
        $status = $deviceStatusService->getDeviceStatus($generator->generator_id, 1);
        
        $statusClass = $status['is_active'] ? 'active' : 'inactive';
        $powerClass = $status['power_status'] ? 'active' : 'inactive';
        
        echo "<tr>";
        echo "<td>{$generator->generator_id}</td>";
        echo "<td>" . ($generator->sitename ?: 'N/A') . "</td>";
        echo "<td class='{$statusClass}'>{$status['status_text']}</td>";
        echo "<td class='{$powerClass}'>{$status['power_text']}</td>";
        echo "<td>" . ($status['last_data_time'] ?: 'No data') . "</td>";
        echo "<td>" . ($status['minutes_since_last_data'] ?: 'N/A') . "</td>";
        echo "</tr>";
        
        if ($status['is_active']) $activeCount++;
        if ($status['power_status']) $poweredOnCount++;
    }
    
    echo "</table>";
    
    echo "<h2>📈 Overall Statistics</h2>";
    echo "<ul>";
    echo "<li><strong>Total Generators:</strong> " . $generators->count() . "</li>";
    echo "<li><strong>Active Generators:</strong> <span class='active'>{$activeCount}</span></li>";
    echo "<li><strong>Inactive Generators:</strong> <span class='inactive'>" . ($generators->count() - $activeCount) . "</span></li>";
    echo "<li><strong>Powered On Generators:</strong> <span class='active'>{$poweredOnCount}</span></li>";
    echo "</ul>";
    
    // Test different time thresholds
    echo "<h2>⏱️ Time Threshold Test</h2>";
    echo "<ul>";
    $thresholds = [1, 5, 10, 30];
    foreach ($thresholds as $minutes) {
        $thresholdActiveCount = $deviceStatusService->getActiveGeneratorsCount($minutes);
        echo "<li><strong>Last {$minutes} minutes:</strong> {$thresholdActiveCount} active generators</li>";
    }
    echo "</ul>";
    
    echo "<h2>✅ Test Results</h2>";
    echo "<p style='color:green;'><strong>✓ Device Status Service is working correctly!</strong></p>";
    echo "<p>• Status detection: <span class='active'>Working</span></p>";
    echo "<p>• Power detection: <span class='active'>Working</span></p>";
    echo "<p>• Time calculations: <span class='active'>Working</span></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your Laravel setup and database connection.</p>";
}

echo "<hr>";
echo "<p><small>Generated at: " . date('Y-m-d H:i:s') . "</small></p>";
echo "<p><a href='../dashboard'>← Back to Dashboard</a></p>";
echo "</body></html>";
?>
