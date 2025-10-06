# 🚀 cPanel Live Server Deployment Guide

## 📋 Pre-Deployment Checklist

### 1. **File Upload**
- [ ] Upload all project files to cPanel File Manager
- [ ] Ensure `vendor/` directory is uploaded (or run `composer install`)
- [ ] Verify `storage/` and `bootstrap/cache/` directories exist
- [ ] Check that `.env` file is uploaded and configured

### 2. **Database Setup**
- [ ] Create SQLite database file in `database/` directory
- [ ] Set correct permissions on database file
- [ ] Verify database connection in `.env` file

### 3. **File Permissions**
```bash
# Set these permissions via cPanel File Manager or SSH
chmod 755 storage/
chmod 755 bootstrap/cache/
chmod 644 .env
chmod 644 database/database.sqlite
```

## 🔧 Step-by-Step Deployment

### Step 1: Upload Files
1. Zip your local project
2. Upload to cPanel File Manager
3. Extract in public_html directory
4. Verify all files are uploaded correctly

### Step 2: Configure Environment
1. Edit `.env` file in cPanel File Manager
2. Set these values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/your/database/database.sqlite
```

### Step 3: Run Quick Fix Script
1. Upload `cpanel-quick-fix.php` to your server
2. Run it via browser: `https://yourdomain.com/cpanel-quick-fix.php`
3. This will:
   - Run database migrations
   - Clear Laravel cache
   - Process runtime tracking
   - Fix any corrupted records

### Step 4: Test System
1. Upload `cpanel-debug.php` to your server
2. Run it via browser: `https://yourdomain.com/cpanel-debug.php`
3. Check for any ❌ errors and fix them

### Step 5: Setup Cron Jobs
1. Go to cPanel → Cron Jobs
2. Add this cron job:
```bash
*/5 * * * * cd /home/username/public_html && php artisan schedule:run
```
3. This will automatically run runtime tracking every 5 minutes

## 🐛 Common Issues & Solutions

### Issue 1: "Class not found" errors
**Solution:**
```bash
composer install --no-dev --optimize-autoloader
```

### Issue 2: Database connection failed
**Solution:**
- Check database file path in `.env`
- Ensure database file has correct permissions
- Verify SQLite is enabled on server

### Issue 3: Runtime tracking not working
**Solution:**
- Run the quick fix script
- Check cron jobs are set up
- Verify file permissions

### Issue 4: Maintenance timestamps not showing
**Solution:**
- Run migrations: `php artisan migrate --force`
- Check if columns exist in database

### Issue 5: Page not loading
**Solution:**
- Check `.htaccess` file exists
- Verify mod_rewrite is enabled
- Check PHP version (7.4+ required)

## 📊 Testing Checklist

After deployment, test these features:

- [ ] Dashboard loads correctly
- [ ] Generator data displays
- [ ] Runtime tracking works
- [ ] Maintenance status updates
- [ ] View Details modal works
- [ ] Page refreshes after maintenance update
- [ ] Maintenance timestamps display correctly

## 🔄 Maintenance Commands

### Clear Cache (if needed):
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Run Runtime Tracking Manually:
```bash
php artisan tinker
>>> $service = new App\Services\RuntimeTrackingService();
>>> $service->processLogs();
```

### Check System Status:
```bash
php artisan route:list
php artisan config:show
```

## 📞 Support

If you encounter issues:

1. **Run Debug Script**: Upload and run `cpanel-debug.php`
2. **Check Error Logs**: Look in cPanel Error Logs
3. **Verify Permissions**: Ensure all directories are writable
4. **Test Database**: Verify database connection and data

## ⚠️ Important Notes

- Always backup your database before making changes
- Test on a staging environment first if possible
- Keep your `.env` file secure and never commit it to version control
- Monitor your server resources and performance
- Set up regular backups of your database

## 🎯 Success Indicators

Your deployment is successful when:
- ✅ Dashboard loads without errors
- ✅ Generator data displays correctly
- ✅ Runtime tracking shows accurate data
- ✅ Maintenance features work properly
- ✅ No PHP errors in logs
- ✅ Cron jobs are running automatically
