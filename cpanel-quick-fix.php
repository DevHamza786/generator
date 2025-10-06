<?php
/**
 * cPanel Quick Fix Script
 * Run this on your live server to fix common issues
 */

echo "<h2>🔧 cPanel Quick Fix Script</h2>";
echo "<hr>";

// Bootstrap Laravel
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "✅ Laravel bootstrapped successfully<br>";
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>1. Running Database Migrations</h3>";
try {
    // Run migrations
    Artisan::call('migrate', ['--force' => true]);
    echo "✅ Migrations completed successfully<br>";
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Clearing Laravel Cache</h3>";
try {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    echo "✅ Cache cleared successfully<br>";
} catch (Exception $e) {
    echo "❌ Cache clear failed: " . $e->getMessage() . "<br>";
}

echo "<h3>3. Processing Runtime Tracking</h3>";
try {
    $service = new App\Services\RuntimeTrackingService();
    $service->processLogs();
    echo "✅ Runtime tracking processed successfully<br>";
} catch (Exception $e) {
    echo "❌ Runtime tracking failed: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Fixing Corrupted Runtime Records</h3>";
try {
    $service = new App\Services\RuntimeTrackingService();
    $fixedCount = $service->fixCorruptedRecords();
    echo "✅ Fixed $fixedCount corrupted runtime records<br>";
} catch (Exception $e) {
    echo "❌ Fix corrupted records failed: " . $e->getMessage() . "<br>";
}

echo "<h3>5. Testing System Status</h3>";
try {
    // Test database connection
    $writeLogsCount = App\Models\GeneratorWriteLog::count();
    $runtimeCount = App\Models\GeneratorRuntime::count();
    echo "✅ Database working - Write Logs: $writeLogsCount, Runtime Records: $runtimeCount<br>";

    // Test generator
    $generator = App\Models\Generator::where('generator_id', 'ID50da533a')->first();
    if ($generator) {
        echo "✅ Generator ID50da533a found<br>";
    } else {
        echo "❌ Generator ID50da533a not found<br>";
    }

    // Test current runtime
    $currentRuntime = App\Models\GeneratorRuntime::getCurrentRuntime('ID50da533a');
    if ($currentRuntime) {
        echo "✅ Generator is currently RUNNING<br>";
    } else {
        echo "✅ Generator is currently STOPPED<br>";
    }

} catch (Exception $e) {
    echo "❌ System test failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>🎯 Quick Fix Completed!</h3>";
echo "The system should now be working correctly on your live server.<br>";
echo "If you still see issues, run the cpanel-debug.php file for detailed diagnostics.<br>";
?>
