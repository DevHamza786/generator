<?php
/**
 * Delete Axact Generators Script for cPanel
 * Access this file through your browser: https://yourdomain.com/delete-axact-generators.php
 * 
 * SECURITY WARNING: Delete this file after running!
 */

// Security check - only allow with password
$password = 'delete-axact-123'; // Change this password

// Check if password is provided
if (!isset($_GET['password']) || $_GET['password'] !== $password) {
    die('Access denied. Please provide correct password: ?password=delete-axact-123');
}

echo "<h2>Delete Axact Generators</h2>";
echo "<p>Running from: " . __DIR__ . "</p>";

// Change to Laravel root directory
$laravel_root = dirname(__DIR__);
chdir($laravel_root);

echo "<p>Changed to Laravel root: " . $laravel_root . "</p>";

// Define the generator IDs to delete
$generatorIds = ['IDabc1234', 'IDabc567', 'IDabc890'];

echo "<h3>Generators to Delete:</h3>";
echo "<ul>";
foreach ($generatorIds as $id) {
    echo "<li>$id</li>";
}
echo "</ul>";

// Handle deletion
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo "<h3>Starting Deletion Process...</h3>";
    echo "<pre>";
    
    // Run the delete seeder
    $command = "php artisan db:seed --class=DeleteAxactGeneratorsSeeder 2>&1";
    $output = shell_exec($command);
    
    echo $output ?: "No output from command";
    echo "</pre>";
    
    echo "<p><strong>Deletion completed!</strong></p>";
    echo "<p><a href='?password=$password'>← Back to main page</a></p>";
    
} else {
    // Show confirmation
    echo "<h3>⚠️ WARNING ⚠️</h3>";
    echo "<p>This will delete the following Axact generators and ALL their associated data:</p>";
    echo "<ul>";
    foreach ($generatorIds as $id) {
        echo "<li><strong>$id</strong> (Axact #100, #101, #102)</li>";
    }
    echo "</ul>";
    
    echo "<p><strong>This action will also delete:</strong></p>";
    echo "<ul>";
    echo "<li>All GeneratorLogs for these generators</li>";
    echo "<li>All GeneratorWriteLogs for these generators</li>";
    echo "<li>All GeneratorRuntimes for these generators</li>";
    echo "<li>All Alerts for these generators</li>";
    echo "</ul>";
    
    echo "<p style='color: red; font-weight: bold;'>This action cannot be undone!</p>";
    
    echo "<p>";
    echo "<a href='?password=$password&confirm=yes' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>YES, DELETE THESE GENERATORS</a>";
    echo " ";
    echo "<a href='?password=$password' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a>";
    echo "</p>";
}

echo "<hr>";
echo "<h3>Manual Command:</h3>";
echo "<p>You can also run this command manually:</p>";
echo "<code>php artisan db:seed --class=DeleteAxactGeneratorsSeeder</code>";

echo "<hr>";
echo "<p><strong>Security Note:</strong> Delete this file after running!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
?>
