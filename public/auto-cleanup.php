<?php
/**
 * Auto Cleanup Script for cPanel
 * Keeps only latest 10 days of data and deletes the rest
 * Access this file through your browser: https://yourdomain.com/auto-cleanup.php
 * 
 * SECURITY WARNING: Delete this file after running!
 */

// Security check - only allow with password
$password = 'auto-cleanup-123'; // Change this password

// Check if password is provided
if (!isset($_GET['password']) || $_GET['password'] !== $password) {
    die('Access denied. Please provide correct password: ?password=auto-cleanup-123');
}

echo "<h2>Auto Cleanup - Keep Only Latest 10 Days</h2>";
echo "<p>This script will delete all data older than 10 days and keep only the latest data.</p>";

// Change to Laravel root directory
$laravel_root = dirname(__DIR__);
chdir($laravel_root);

echo "<p>Changed to Laravel root: " . $laravel_root . "</p>";

// Handle auto cleanup
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo "<h3>Starting Auto Cleanup...</h3>";
    echo "<pre>";
    
    try {
        // Load Laravel environment
        require_once $laravel_root . '/vendor/autoload.php';
        $app = require_once $laravel_root . '/bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        echo "Laravel environment loaded successfully.\n";
        
        // Run the auto cleanup seeder
        $command = "php artisan db:seed --class=AutoCleanupSeeder 2>&1";
        $output = shell_exec($command);
        
        echo $output ?: "No output from command";
        
        echo "\n=====================================\n";
        echo "✅ Auto cleanup completed!\n";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "Please check your Laravel installation and database connection.\n";
    }
    
    echo "</pre>";
    
    echo "<p><strong>Auto cleanup completed!</strong></p>";
    echo "<p><a href='?password=$password'>← Back to main page</a></p>";
    
} else {
    // Show information and confirmation
    echo "<h3>Auto Cleanup Information</h3>";
    echo "<p>This will <strong>DELETE</strong> all data older than 10 days and keep only the latest data.</p>";
    
    echo "<h4>What will be cleaned up:</h4>";
    echo "<ul>";
    echo "<li><strong>generator_logs</strong> table - All records older than 10 days</li>";
    echo "<li><strong>generator_write_logs</strong> table - All records older than 10 days</li>";
    echo "</ul>";
    
    echo "<h4>What will be kept:</h4>";
    echo "<ul>";
    echo "<li><strong>Latest 10 days</strong> of GeneratorLogs</li>";
    echo "<li><strong>Latest 10 days</strong> of GeneratorWriteLogs</li>";
    echo "<li><strong>All other tables</strong> remain unchanged</li>";
    echo "</ul>";
    
    echo "<h4>Example:</h4>";
    echo "<p>If today is <strong>2025-10-06</strong>, then:</p>";
    echo "<ul>";
    echo "<li><strong>Keep:</strong> Data from 2025-09-27 to 2025-10-06 (10 days)</li>";
    echo "<li><strong>Delete:</strong> All data before 2025-09-27</li>";
    echo "</ul>";
    
    echo "<h4>⚠️ Important Notes:</h4>";
    echo "<ul>";
    echo "<li>This operation will <strong>PERMANENTLY DELETE</strong> old data</li>";
    echo "<li>Make sure you have a database backup before proceeding</li>";
    echo "<li>This operation cannot be undone</li>";
    echo "<li>Only log tables are affected - other data remains safe</li>";
    echo "</ul>";
    
    echo "<p style='color: red; font-weight: bold;'>⚠️ WARNING: This will permanently delete old data! Make sure you have a backup!</p>";
    
    echo "<p>";
    echo "<a href='?password=$password&confirm=yes' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>YES, DELETE OLD DATA</a>";
    echo " ";
    echo "<a href='?password=$password' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a>";
    echo "</p>";
}

echo "<hr>";
echo "<h3>Manual Commands:</h3>";
echo "<p>You can also run these commands manually:</p>";
echo "<code>php artisan db:seed --class=AutoCleanupSeeder</code><br>";
echo "<code>php artisan cleanup:logs --days=10</code>";

echo "<hr>";
echo "<h3>Current Database Status:</h3>";
echo "<p>Current time: " . date('Y-m-d H:i:s T') . "</p>";
echo "<p>Cutoff date (10 days ago): " . date('Y-m-d H:i:s', strtotime('-10 days')) . "</p>";

echo "<hr>";
echo "<p><strong>Security Note:</strong> Delete this file after running!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
?>
