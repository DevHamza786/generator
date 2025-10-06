<?php
/**
 * cPanel Live Server Debug Script
 * Upload this file to your live server and run it to diagnose issues
 */

echo "<h2>🔍 cPanel Live Server Debug Report</h2>";
echo "<hr>";

// Test 1: Basic PHP and Laravel
echo "<h3>1. Basic Environment Check</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . getcwd() . "<br>";
echo "File Exists (bootstrap/app.php): " . (file_exists('bootstrap/app.php') ? '✅ Yes' : '❌ No') . "<br>";
echo "File Exists (vendor/autoload.php): " . (file_exists('vendor/autoload.php') ? '✅ Yes' : '❌ No') . "<br>";
echo "<br>";

// Test 2: Laravel Bootstrap
echo "<h3>2. Laravel Bootstrap Test</h3>";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel bootstrapped successfully<br>";
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "<br>";
    exit;
}
echo "<br>";

// Test 3: Database Connection
echo "<h3>3. Database Connection Test</h3>";
try {
    $writeLogsCount = App\Models\GeneratorWriteLog::count();
    $runtimeCount = App\Models\GeneratorRuntime::count();
    echo "✅ Database connected successfully<br>";
    echo "Write Logs Count: " . $writeLogsCount . "<br>";
    echo "Runtime Records Count: " . $runtimeCount . "<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 4: Generator Data
echo "<h3>4. Generator Data Test</h3>";
try {
    $generator = App\Models\Generator::where('generator_id', 'ID50da533a')->first();
    if ($generator) {
        echo "✅ Generator ID50da533a found<br>";
        echo "Name: " . $generator->name . "<br>";
        echo "Site: " . $generator->sitename . "<br>";
    } else {
        echo "❌ Generator ID50da533a not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Generator query failed: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 5: Recent Write Logs
echo "<h3>5. Recent Write Logs Test</h3>";
try {
    $recentLogs = App\Models\GeneratorWriteLog::where('generator_id', 'ID50da533a')
        ->latest('write_timestamp')
        ->limit(5)
        ->get(['write_timestamp', 'LV1', 'LV2', 'LV3']);

    if ($recentLogs->count() > 0) {
        echo "✅ Recent logs found: " . $recentLogs->count() . "<br>";
        foreach ($recentLogs as $log) {
            $hasVoltage = ($log->LV1 > 0) && ($log->LV2 > 0) && ($log->LV3 > 0);
            echo "- " . $log->write_timestamp . " | LV1: " . $log->LV1 . " | LV2: " . $log->LV2 . " | LV3: " . $log->LV3 . " | Has Voltage: " . ($hasVoltage ? 'YES' : 'NO') . "<br>";
        }
    } else {
        echo "❌ No recent logs found<br>";
    }
} catch (Exception $e) {
    echo "❌ Recent logs query failed: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 6: Runtime Tracking Service
echo "<h3>6. Runtime Tracking Service Test</h3>";
try {
    $service = new App\Services\RuntimeTrackingService();
    $service->processLogs();
    echo "✅ Runtime tracking service executed successfully<br>";
} catch (Exception $e) {
    echo "❌ Runtime tracking service failed: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 7: Current Runtime Status
echo "<h3>7. Current Runtime Status Test</h3>";
try {
    $currentRuntime = App\Models\GeneratorRuntime::getCurrentRuntime('ID50da533a');
    if ($currentRuntime) {
        echo "✅ Generator is currently RUNNING<br>";
        echo "Start Time: " . $currentRuntime->start_time . "<br>";
        echo "Duration: " . $currentRuntime->formatted_duration . "<br>";
    } else {
        echo "✅ Generator is currently STOPPED<br>";
    }
} catch (Exception $e) {
    echo "❌ Runtime status check failed: " . $e->getMessage() . "<br>";
}
echo "<br>";

// Test 8: File Permissions
echo "<h3>8. File Permissions Test</h3>";
$directories = ['storage', 'bootstrap/cache', 'database'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir);
        echo "Directory '$dir': " . ($writable ? '✅ Writable' : '❌ Not Writable') . "<br>";
    } else {
        echo "Directory '$dir': ❌ Not Found<br>";
    }
}
echo "<br>";

// Test 9: Environment Configuration
echo "<h3>9. Environment Configuration Test</h3>";
echo "APP_ENV: " . env('APP_ENV', 'Not Set') . "<br>";
echo "APP_DEBUG: " . env('APP_DEBUG', 'Not Set') . "<br>";
echo "DB_CONNECTION: " . env('DB_CONNECTION', 'Not Set') . "<br>";
echo "APP_URL: " . env('APP_URL', 'Not Set') . "<br>";
echo "<br>";

// Test 10: Maintenance Timestamps
echo "<h3>10. Maintenance Timestamps Test</h3>";
try {
    $runtimeWithMaintenance = App\Models\GeneratorRuntime::where('generator_id', 'ID50da533a')
        ->where(function($query) {
            $query->whereNotNull('maintenance_started_at')
                  ->orWhereNotNull('maintenance_completed_at');
        })
        ->count();

    echo "Runtime records with maintenance timestamps: " . $runtimeWithMaintenance . "<br>";

    // Test if columns exist
    $columns = DB::select("PRAGMA table_info(generator_runtimes)");
    $hasMaintenanceStarted = false;
    $hasMaintenanceCompleted = false;

    foreach ($columns as $column) {
        if ($column->name === 'maintenance_started_at') $hasMaintenanceStarted = true;
        if ($column->name === 'maintenance_completed_at') $hasMaintenanceCompleted = true;
    }

    echo "maintenance_started_at column: " . ($hasMaintenanceStarted ? '✅ Exists' : '❌ Missing') . "<br>";
    echo "maintenance_completed_at column: " . ($hasMaintenanceCompleted ? '✅ Exists' : '❌ Missing') . "<br>";

} catch (Exception $e) {
    echo "❌ Maintenance timestamps test failed: " . $e->getMessage() . "<br>";
}
echo "<br>";

echo "<hr>";
echo "<h3>🎯 Summary</h3>";
echo "If you see any ❌ errors above, those are the issues that need to be fixed on your live server.<br>";
echo "Upload this file to your cPanel server and run it to get a complete diagnostic report.<br>";
echo "<br>";
echo "<strong>Next Steps:</strong><br>";
echo "1. Fix any ❌ errors shown above<br>";
echo "2. Run migrations if database columns are missing<br>";
echo "3. Set correct file permissions<br>";
echo "4. Configure cron jobs for automatic runtime tracking<br>";
echo "5. Test the system again<br>";
?>
