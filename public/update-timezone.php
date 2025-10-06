<?php
/**
 * Update Timezone Script for cPanel
 * Converts existing timestamps from UTC to Asia/Karachi
 * Access this file through your browser: https://yourdomain.com/update-timezone.php
 * 
 * SECURITY WARNING: Delete this file after running!
 */

// Security check - only allow with password
$password = 'update-timezone-123'; // Change this password

// Check if password is provided
if (!isset($_GET['password']) || $_GET['password'] !== $password) {
    die('Access denied. Please provide correct password: ?password=update-timezone-123');
}

echo "<h2>Update Timezone to Pakistan Karachi</h2>";
echo "<p>Running from: " . __DIR__ . "</p>";

// Change to Laravel root directory
$laravel_root = dirname(__DIR__);
chdir($laravel_root);

echo "<p>Changed to Laravel root: " . $laravel_root . "</p>";

// Handle timezone update
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo "<h3>Starting Timezone Conversion...</h3>";
    echo "<pre>";
    
    // Run the timezone update seeder
    $command = "php artisan db:seed --class=UpdateTimezoneSeeder 2>&1";
    $output = shell_exec($command);
    
    echo $output ?: "No output from command";
    echo "</pre>";
    
    echo "<p><strong>Timezone conversion completed!</strong></p>";
    echo "<p><a href='?password=$password'>← Back to main page</a></p>";
    
} else {
    // Show information and confirmation
    echo "<h3>Timezone Conversion Information</h3>";
    echo "<p>This will convert all existing timestamps from <strong>UTC</strong> to <strong>Asia/Karachi</strong> timezone.</p>";
    
    echo "<h4>What will be updated:</h4>";
    echo "<ul>";
    echo "<li><strong>GeneratorLogs</strong> - All log_timestamp fields (289 records)</li>";
    echo "<li><strong>GeneratorWriteLogs</strong> - All write_timestamp fields (17,105 records)</li>";
    echo "</ul>";
    
    echo "<h4>Total Records to Convert:</h4>";
    echo "<p><strong>17,394 total records</strong> will be updated from UTC to Asia/Karachi timezone</p>";
    
    echo "<h4>Timezone Details:</h4>";
    echo "<ul>";
    echo "<li><strong>From:</strong> UTC (Coordinated Universal Time)</li>";
    echo "<li><strong>To:</strong> Asia/Karachi (Pakistan Standard Time)</li>";
    echo "<li><strong>Offset:</strong> +5 hours</li>";
    echo "<li><strong>Example:</strong> 2025-01-15 10:30:00 UTC → 2025-01-15 15:30:00 PKT</li>";
    echo "</ul>";
    
    echo "<h4>⚠️ Important Notes:</h4>";
    echo "<ul>";
    echo "<li>This operation will modify existing data</li>";
    echo "<li>Make sure you have a database backup before proceeding</li>";
    echo "<li>The conversion adds 5 hours to all timestamps</li>";
    echo "<li>This operation cannot be easily undone</li>";
    echo "</ul>";
    
    echo "<p style='color: red; font-weight: bold;'>Make sure you have a database backup before proceeding!</p>";
    
    echo "<p>";
    echo "<a href='?password=$password&confirm=yes' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>YES, UPDATE TIMEZONE</a>";
    echo " ";
    echo "<a href='?password=$password' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a>";
    echo "</p>";
}

echo "<hr>";
echo "<h3>Manual Command:</h3>";
echo "<p>You can also run this command manually:</p>";
echo "<code>php artisan db:seed --class=UpdateTimezoneSeeder</code>";

echo "<hr>";
echo "<h3>Check Current Timezone:</h3>";
echo "<p>Current server time: " . date('Y-m-d H:i:s T') . "</p>";
echo "<p>Current timezone: " . date_default_timezone_get() . "</p>";

echo "<hr>";
echo "<p><strong>Security Note:</strong> Delete this file after running!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
?>
