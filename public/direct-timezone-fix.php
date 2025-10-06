<?php
/**
 * Direct Database Timezone Fix for cPanel
 * Updates timestamps directly in database without using Laravel seeders
 * Access this file through your browser: https://yourdomain.com/direct-timezone-fix.php
 * 
 * SECURITY WARNING: Delete this file after running!
 */

// Security check - only allow with password
$password = 'direct-fix-123'; // Change this password

// Check if password is provided
if (!isset($_GET['password']) || $_GET['password'] !== $password) {
    die('Access denied. Please provide correct password: ?password=direct-fix-123');
}

echo "<h2>Direct Database Timezone Fix</h2>";
echo "<p>This script will directly update database timestamps without using Laravel seeders.</p>";

// Change to Laravel root directory
$laravel_root = dirname(__DIR__);
chdir($laravel_root);

echo "<p>Changed to Laravel root: " . $laravel_root . "</p>";

// Handle direct database update
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo "<h3>Starting Direct Database Update...</h3>";
    echo "<pre>";
    
    try {
        // Load Laravel environment
        require_once $laravel_root . '/vendor/autoload.php';
        $app = require_once $laravel_root . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        // Get database connection
        $pdo = DB::connection()->getPdo();
        
        echo "Connected to database successfully.\n";
        
        // Update GeneratorLogs
        echo "\nUpdating GeneratorLogs...\n";
        $sql1 = "UPDATE generator_logs SET log_timestamp = DATE_ADD(log_timestamp, INTERVAL 5 HOUR)";
        $stmt1 = $pdo->prepare($sql1);
        $result1 = $stmt1->execute();
        $affected1 = $stmt1->rowCount();
        echo "GeneratorLogs updated: {$affected1} records\n";
        
        // Update GeneratorWriteLogs
        echo "\nUpdating GeneratorWriteLogs...\n";
        $sql2 = "UPDATE generator_write_logs SET write_timestamp = DATE_ADD(write_timestamp, INTERVAL 5 HOUR)";
        $stmt2 = $pdo->prepare($sql2);
        $result2 = $stmt2->execute();
        $affected2 = $stmt2->rowCount();
        echo "GeneratorWriteLogs updated: {$affected2} records\n";
        
        echo "\n=== UPDATE COMPLETED ===\n";
        echo "Total GeneratorLogs updated: {$affected1}\n";
        echo "Total GeneratorWriteLogs updated: {$affected2}\n";
        echo "Total records updated: " . ($affected1 + $affected2) . "\n";
        
        echo "\n✅ All timestamps have been updated from UTC to Asia/Karachi (UTC+5)\n";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "Please check your database connection and try again.\n";
    }
    
    echo "</pre>";
    
    echo "<p><strong>Direct database update completed!</strong></p>";
    echo "<p><a href='?password=$password'>← Back to main page</a></p>";
    
} else {
    // Show information and confirmation
    echo "<h3>Direct Database Timezone Fix</h3>";
    echo "<p>This will directly update database timestamps by adding 5 hours to convert from UTC to Asia/Karachi timezone.</p>";
    
    echo "<h4>What will be updated:</h4>";
    echo "<ul>";
    echo "<li><strong>generator_logs</strong> table - log_timestamp column</li>";
    echo "<li><strong>generator_write_logs</strong> table - write_timestamp column</li>";
    echo "</ul>";
    
    echo "<h4>SQL Commands that will be executed:</h4>";
    echo "<pre>";
    echo "UPDATE generator_logs SET log_timestamp = DATE_ADD(log_timestamp, INTERVAL 5 HOUR);\n";
    echo "UPDATE generator_write_logs SET write_timestamp = DATE_ADD(write_timestamp, INTERVAL 5 HOUR);";
    echo "</pre>";
    
    echo "<h4>Example Conversion:</h4>";
    echo "<ul>";
    echo "<li><strong>Before:</strong> 2025-10-06 00:03:50 (UTC)</li>";
    echo "<li><strong>After:</strong> 2025-10-06 05:03:50 (PKT)</li>";
    echo "</ul>";
    
    echo "<h4>⚠️ Important Notes:</h4>";
    echo "<ul>";
    echo "<li>This directly modifies the database</li>";
    echo "<li>Make sure you have a database backup</li>";
    echo "<li>This adds exactly 5 hours to all timestamps</li>";
    echo "<li>This operation cannot be easily undone</li>";
    echo "<li>No Laravel seeders are used - direct SQL execution</li>";
    echo "</ul>";
    
    echo "<p style='color: red; font-weight: bold;'>Make sure you have a database backup before proceeding!</p>";
    
    echo "<p>";
    echo "<a href='?password=$password&confirm=yes' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>YES, UPDATE DATABASE DIRECTLY</a>";
    echo " ";
    echo "<a href='?password=$password' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a>";
    echo "</p>";
}

echo "<hr>";
echo "<h3>Manual SQL Commands:</h3>";
echo "<p>You can also run these SQL commands directly in phpMyAdmin:</p>";
echo "<pre>";
echo "UPDATE generator_logs SET log_timestamp = DATE_ADD(log_timestamp, INTERVAL 5 HOUR);\n";
echo "UPDATE generator_write_logs SET write_timestamp = DATE_ADD(write_timestamp, INTERVAL 5 HOUR);";
echo "</pre>";

echo "<hr>";
echo "<h3>Database Information:</h3>";
echo "<p>Current server time: " . date('Y-m-d H:i:s T') . "</p>";
echo "<p>Current timezone: " . date_default_timezone_get() . "</p>";

echo "<hr>";
echo "<p><strong>Security Note:</strong> Delete this file after running!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
?>
