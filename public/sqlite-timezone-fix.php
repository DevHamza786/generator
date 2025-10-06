<?php
/**
 * SQLite Timezone Fix for cPanel
 * Specifically designed for SQLite databases
 * Access this file through your browser: https://yourdomain.com/sqlite-timezone-fix.php
 * 
 * SECURITY WARNING: Delete this file after running!
 */

// Security check - only allow with password
$password = 'sqlite-fix-123'; // Change this password

// Check if password is provided
if (!isset($_GET['password']) || $_GET['password'] !== $password) {
    die('Access denied. Please provide correct password: ?password=sqlite-fix-123');
}

echo "<h2>SQLite Timezone Fix for cPanel</h2>";
echo "<p>This script is specifically designed for SQLite databases on cPanel.</p>";

// Change to Laravel root directory
$laravel_root = dirname(__DIR__);
chdir($laravel_root);

echo "<p>Changed to Laravel root: " . $laravel_root . "</p>";

// Handle SQLite timezone fix
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo "<h3>Starting SQLite Timezone Fix...</h3>";
    echo "<pre>";
    
    try {
        // Load Laravel environment
        require_once $laravel_root . '/vendor/autoload.php';
        $app = require_once $laravel_root . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        // Check database driver
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        echo "Database driver detected: {$driver}\n";
        
        if ($driver !== 'sqlite') {
            echo "⚠️ Warning: This script is designed for SQLite, but you're using {$driver}\n";
            echo "The script will still work, but it's optimized for SQLite.\n";
        }
        
        echo "\nRunning SQLite Timezone Fix Seeder...\n";
        echo "=====================================\n";
        
        // Run the SQLite timezone fix seeder
        $command = "php artisan db:seed --class=SqliteTimezoneFixSeeder 2>&1";
        $output = shell_exec($command);
        
        echo $output ?: "No output from command";
        
        echo "\n=====================================\n";
        echo "✅ SQLite timezone fix completed!\n";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "Please check your Laravel installation and database connection.\n";
    }
    
    echo "</pre>";
    
    echo "<p><strong>SQLite timezone fix completed!</strong></p>";
    echo "<p><a href='?password=$password'>← Back to main page</a></p>";
    
} else {
    // Show information and confirmation
    echo "<h3>SQLite Timezone Fix Information</h3>";
    echo "<p>This script is specifically designed for <strong>SQLite databases</strong> and will add 5 hours to all timestamps.</p>";
    
    echo "<h4>What makes this different:</h4>";
    echo "<ul>";
    echo "<li><strong>SQLite Compatible:</strong> Uses Carbon PHP library instead of SQL DATE_ADD</li>";
    echo "<li><strong>Batch Processing:</strong> Processes records in batches to avoid memory issues</li>";
    echo "<li><strong>Error Handling:</strong> Continues processing even if some records fail</li>";
    echo "<li><strong>Progress Tracking:</strong> Shows progress for large datasets</li>";
    echo "</ul>";
    
    echo "<h4>What will be updated:</h4>";
    echo "<ul>";
    echo "<li><strong>generator_logs</strong> table - log_timestamp column</li>";
    echo "<li><strong>generator_write_logs</strong> table - write_timestamp column</li>";
    echo "</ul>";
    
    echo "<h4>SQLite Method:</h4>";
    echo "<p>Instead of SQL DATE_ADD (which doesn't work in SQLite), this script:</p>";
    echo "<ol>";
    echo "<li>Reads each timestamp using Carbon PHP library</li>";
    echo "<li>Adds 5 hours using Carbon's addHours() method</li>";
    echo "<li>Updates the record with the new timestamp</li>";
    echo "<li>Processes in batches of 100 records</li>";
    echo "</ol>";
    
    echo "<h4>Example Conversion:</h4>";
    echo "<ul>";
    echo "<li><strong>Before:</strong> 2025-10-06 00:03:50 (UTC)</li>";
    echo "<li><strong>After:</strong> 2025-10-06 05:03:50 (PKT)</li>";
    echo "</ul>";
    
    echo "<h4>⚠️ Important Notes:</h4>";
    echo "<ul>";
    echo "<li>This operation will modify existing data</li>";
    echo "<li>Make sure you have a database backup before proceeding</li>";
    echo "<li>This adds exactly 5 hours to all timestamps</li>";
    echo "<li>This operation cannot be easily undone</li>";
    echo "<li>Optimized for SQLite but works with other databases too</li>";
    echo "</ul>";
    
    echo "<p style='color: red; font-weight: bold;'>Make sure you have a database backup before proceeding!</p>";
    
    echo "<p>";
    echo "<a href='?password=$password&confirm=yes' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>YES, FIX SQLITE TIMEZONE</a>";
    echo " ";
    echo "<a href='?password=$password' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a>";
    echo "</p>";
}

echo "<hr>";
echo "<h3>Manual Command:</h3>";
echo "<p>You can also run this command manually:</p>";
echo "<code>php artisan db:seed --class=SqliteTimezoneFixSeeder</code>";

echo "<hr>";
echo "<h3>Database Information:</h3>";
echo "<p>Current server time: " . date('Y-m-d H:i:s T') . "</p>";
echo "<p>Current timezone: " . date_default_timezone_get() . "</p>";

echo "<hr>";
echo "<p><strong>Security Note:</strong> Delete this file after running!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
?>
