<?php
/**
 * Simple Timezone Fix - No Laravel Required
 * Direct database connection and update
 * Access this file through your browser: https://yourdomain.com/simple-timezone-fix.php
 * 
 * SECURITY WARNING: Delete this file after running!
 */

// Security check
$password = 'simple-fix-123'; // Change this password

if (!isset($_GET['password']) || $_GET['password'] !== $password) {
    die('Access denied. Please provide correct password: ?password=simple-fix-123');
}

// Database configuration - UPDATE THESE VALUES
$host = 'localhost';        // Your database host
$dbname = 'your_database';  // Your database name
$username = 'your_username'; // Your database username
$password_db = 'your_password'; // Your database password

echo "<h2>Simple Timezone Fix (No Laravel)</h2>";
echo "<p>Direct database connection and timestamp update.</p>";

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    echo "<h3>Starting Simple Database Update...</h3>";
    echo "<pre>";
    
    try {
        // Connect to database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "Connected to database successfully.\n";
        
        // Update GeneratorLogs
        echo "\nUpdating generator_logs table...\n";
        $sql1 = "UPDATE generator_logs SET log_timestamp = DATE_ADD(log_timestamp, INTERVAL 5 HOUR)";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute();
        $affected1 = $stmt1->rowCount();
        echo "GeneratorLogs updated: {$affected1} records\n";
        
        // Update GeneratorWriteLogs
        echo "\nUpdating generator_write_logs table...\n";
        $sql2 = "UPDATE generator_write_logs SET write_timestamp = DATE_ADD(write_timestamp, INTERVAL 5 HOUR)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute();
        $affected2 = $stmt2->rowCount();
        echo "GeneratorWriteLogs updated: {$affected2} records\n";
        
        echo "\n=== UPDATE COMPLETED ===\n";
        echo "Total GeneratorLogs updated: {$affected1}\n";
        echo "Total GeneratorWriteLogs updated: {$affected2}\n";
        echo "Total records updated: " . ($affected1 + $affected2) . "\n";
        
        echo "\n✅ All timestamps updated from UTC to Asia/Karachi (UTC+5)\n";
        
    } catch (PDOException $e) {
        echo "❌ Database Error: " . $e->getMessage() . "\n";
        echo "Please check your database configuration.\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "</pre>";
    
    echo "<p><strong>Simple database update completed!</strong></p>";
    echo "<p><a href='?password=$password'>← Back to main page</a></p>";
    
} else {
    echo "<h3>Simple Timezone Fix</h3>";
    echo "<p>This script connects directly to your database and updates timestamps.</p>";
    
    echo "<h4>⚠️ IMPORTANT: Update Database Configuration</h4>";
    echo "<p>Before running, edit this file and update these values:</p>";
    echo "<pre>";
    echo "\$host = 'localhost';        // Your database host\n";
    echo "\$dbname = 'your_database';  // Your database name\n";
    echo "\$username = 'your_username'; // Your database username\n";
    echo "\$password_db = 'your_password'; // Your database password";
    echo "</pre>";
    
    echo "<h4>What will be updated:</h4>";
    echo "<ul>";
    echo "<li><strong>generator_logs</strong> - log_timestamp column</li>";
    echo "<li><strong>generator_write_logs</strong> - write_timestamp column</li>";
    echo "</ul>";
    
    echo "<h4>Example Conversion:</h4>";
    echo "<ul>";
    echo "<li><strong>Before:</strong> 2025-10-06 00:03:50 (UTC)</li>";
    echo "<li><strong>After:</strong> 2025-10-06 05:03:50 (PKT)</li>";
    echo "</ul>";
    
    echo "<p style='color: red; font-weight: bold;'>Update database configuration first, then proceed!</p>";
    
    echo "<p>";
    echo "<a href='?password=$password&confirm=yes' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>YES, UPDATE DATABASE</a>";
    echo " ";
    echo "<a href='?password=$password' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Cancel</a>";
    echo "</p>";
}

echo "<hr>";
echo "<p><strong>Security Note:</strong> Delete this file after running!</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
?>
