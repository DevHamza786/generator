<?php
/**
 * Seeder Runner for cPanel
 * Access this file through your browser: https://yourdomain.com/run-seeders.php
 * 
 * SECURITY WARNING: Delete this file after running seeders!
 */

// Security check - only allow from specific IP or with password
$allowed_ips = ['127.0.0.1', '::1']; // Add your IP here
$password = 'your-secret-password'; // Change this password

// Check if password is provided
if (!isset($_GET['password']) || $_GET['password'] !== $password) {
    die('Access denied. Please provide correct password: ?password=your-secret-password');
}

// Check IP if needed (uncomment the lines below)
/*
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Access denied from this IP: ' . $_SERVER['REMOTE_ADDR']);
}
*/

echo "<h2>Laravel Seeder Runner</h2>";
echo "<p>Running from: " . __DIR__ . "</p>";

// Change to Laravel root directory
$laravel_root = dirname(__DIR__);
chdir($laravel_root);

echo "<p>Changed to Laravel root: " . $laravel_root . "</p>";

// Available seeders
$available_seeders = [
    'AdminUserSeeder' => 'Create admin user',
    'UpdateGeneratorSitenameKvaSeeder' => 'Update generator sitename and KVA',
    'DeleteAxactGeneratorsSeeder' => 'Delete Axact generators (IDabc1234, IDabc567, IDabc890, ID1122334455)',
    'UpdateTimezoneSeeder' => 'Convert existing timestamps from UTC to Asia/Karachi',
    'FixTimezoneSeeder' => 'Fix timezone by adding 5 hours to all timestamps',
    'SqliteTimezoneFixSeeder' => 'SQLite-compatible timezone fix (recommended for cPanel)',
    'DatabaseSeeder' => 'Run all seeders'
];

// Handle seeder execution
if (isset($_GET['run'])) {
    $seeder = $_GET['run'];
    
    if (!array_key_exists($seeder, $available_seeders)) {
        die("Invalid seeder: $seeder");
    }
    
    echo "<h3>Running: $seeder</h3>";
    echo "<pre>";
    
    // Run the seeder
    $command = "php artisan db:seed --class=$seeder 2>&1";
    $output = shell_exec($command);
    
    echo $output ?: "No output from command";
    echo "</pre>";
    
    echo "<p><strong>Seeder completed!</strong></p>";
    echo "<p><a href='?password=$password'>← Back to seeder list</a></p>";
    
} else {
    // Show seeder list
    echo "<h3>Available Seeders:</h3>";
    echo "<ul>";
    
    foreach ($available_seeders as $seeder => $description) {
        echo "<li>";
        echo "<strong>$seeder</strong>: $description<br>";
        echo "<a href='?password=$password&run=$seeder' style='background: #007cba; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>Run $seeder</a>";
        echo "</li><br>";
    }
    
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>Manual Commands:</h3>";
    echo "<p>You can also run these commands manually:</p>";
    echo "<ul>";
    foreach ($available_seeders as $seeder => $description) {
        echo "<li><code>php artisan db:seed --class=$seeder</code></li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Security Note:</strong> Delete this file after running seeders!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
?>
