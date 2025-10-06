# cPanel Live Server Deployment Checklist

## 🚨 Common Issues on Live cPanel Server

### 1. **Database Migration Issues**
```bash
# Run migrations on live server
php artisan migrate --force
```

### 2. **File Permissions**
```bash
# Set correct permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 644 .env
```

### 3. **Environment Configuration**
- Check `.env` file has correct database credentials
- Verify `APP_URL` is set to your domain
- Ensure `APP_ENV=production`

### 4. **Runtime Tracking Service Issues**

#### Check if service is running:
```php
// Add this to a test file on live server
<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = new App\Services\RuntimeTrackingService();
$service->processLogs();
echo "Runtime tracking completed!";
?>
```

### 5. **Database Connection Issues**
```php
// Test database connection
<?php
try {
    $pdo = new PDO("sqlite:database/database.sqlite");
    echo "Database connected successfully!";
} catch(PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
```

### 6. **Cron Job Setup**
Add this to cPanel Cron Jobs:
```bash
*/5 * * * * cd /home/username/public_html && php artisan schedule:run
```

### 7. **File Upload Issues**
- Check if `storage/` directory is writable
- Verify `database/` directory permissions
- Ensure `vendor/` directory is uploaded

## 🔧 Quick Fixes for Live Server

### Fix 1: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Fix 2: Run Migrations
```bash
php artisan migrate --force
```

### Fix 3: Set Permissions
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Fix 4: Test Runtime Tracking
Create a test file `test-runtime.php`:
```php
<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Runtime Tracking...\n";

// Test database connection
try {
    $count = App\Models\GeneratorWriteLog::count();
    echo "Write logs count: $count\n";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

// Test runtime tracking
try {
    $service = new App\Services\RuntimeTrackingService();
    $service->processLogs();
    echo "Runtime tracking completed successfully!\n";
} catch (Exception $e) {
    echo "Runtime tracking error: " . $e->getMessage() . "\n";
}

echo "Test completed!\n";
?>
```

## 📋 Deployment Steps

1. **Upload Files**: Ensure all files are uploaded to cPanel
2. **Set Permissions**: Run permission commands
3. **Run Migrations**: Execute migration commands
4. **Clear Cache**: Clear all Laravel caches
5. **Test Database**: Verify database connection
6. **Test Runtime Tracking**: Run the test file
7. **Setup Cron Jobs**: Configure automatic runtime tracking

## 🐛 Debugging Commands

### Check Laravel Status:
```bash
php artisan --version
php artisan route:list
php artisan config:show
```

### Check Database:
```bash
php artisan tinker
>>> App\Models\GeneratorWriteLog::count()
>>> App\Models\GeneratorRuntime::count()
```

### Check Logs:
```bash
tail -f storage/logs/laravel.log
```

## ⚠️ Important Notes

- Always backup database before running migrations
- Test on staging environment first
- Check cPanel error logs for PHP errors
- Ensure PHP version compatibility (7.4+)
- Verify all dependencies are installed via Composer
