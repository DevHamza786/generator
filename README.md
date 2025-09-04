# Generator Power Monitor - Laravel 10 Portal

A Laravel 10 application for monitoring generator power consumption with real-time data from three external APIs.

## Features

- **Real-time Generator Status Monitoring**: Display current ON/OFF status with visual indicators
- **Live Data Tables**: Show latest log and write log data with auto-refresh
- **Automated API Integration**: Fetch data every 30 seconds from three external APIs
- **Duplicate Prevention**: Smart data handling to prevent duplicate entries
- **Modern UI**: Bootstrap-based responsive dashboard with Font Awesome icons
- **AJAX Auto-refresh**: Frontend automatically refreshes data every 30 seconds

## API Integration

The application integrates with three external APIs:

1. **Write API** (`https://xyz.net/write`) - Generator power consumption data
2. **Get API** (`https://xyz.net/get`) - Generator ON/OFF status
3. **Log API** (`https://xyz.net/log`) - Utilization logs

## Database Structure

- `generator_logs` - Stores log API data
- `generator_status` - Stores get API ON/OFF status
- `generator_write_logs` - Stores write API data

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd generator-monitor
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   Update your `.env` file with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=generator_monitor
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

## Usage

### Dashboard Access
- Visit `http://localhost:8000` or `http://localhost:8000/dashboard`
- The dashboard shows real-time generator status and latest data

### Manual API Testing
You can manually test the API fetching commands:

```bash
# Fetch generator logs
php artisan generator:fetch-logs

# Fetch generator status
php artisan generator:fetch-status

# Fetch generator write logs
php artisan generator:fetch-write-logs
```

### API Endpoints
The application provides JSON API endpoints for AJAX requests:

- `GET /api/generator/status` - Latest generator status
- `GET /api/generator/logs` - Latest log data (20 entries)
- `GET /api/generator/write-logs` - Latest write log data (20 entries)

## Scheduled Tasks

The application uses Laravel's scheduler to automatically fetch data:

- **Log API**: Every 30 seconds
- **Get API**: Every 30 seconds  
- **Write API**: Every 30 seconds (saved after 90 seconds to prevent duplicates)

To run the scheduler in production:

```bash
# Add to crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Configuration

### API URLs
Update the API URLs in `app/Services/GeneratorApiService.php`:

```php
private $baseUrl = 'https://xyz.net'; // Change to your actual API base URL
private $generatorIds = ['ID492ff2e5']; // Add your generator IDs
```

### Auto-refresh Interval
The frontend auto-refresh interval can be modified in `resources/views/dashboard.blade.php`:

```javascript
setInterval(function() {
    refreshData();
}, 30000); // 30 seconds
```

## File Structure

```
app/
├── Console/Commands/
│   ├── FetchGeneratorLogs.php
│   ├── FetchGeneratorStatus.php
│   └── FetchGeneratorWriteLogs.php
├── Http/Controllers/
│   ├── DashboardController.php
│   └── Api/GeneratorController.php
├── Models/
│   ├── GeneratorLog.php
│   ├── GeneratorStatus.php
│   └── GeneratorWriteLog.php
└── Services/
    └── GeneratorApiService.php

database/migrations/
├── create_generator_logs_table.php
├── create_generator_status_table.php
└── create_generator_write_logs_table.php

resources/views/
└── dashboard.blade.php

routes/
├── web.php
└── console.php
```

## Error Handling

The application includes comprehensive error handling:

- **API Timeout**: 30-second timeout for all API calls
- **Error Logging**: All errors are logged to Laravel's log system
- **Graceful Degradation**: Frontend continues to work even if APIs are unavailable
- **Duplicate Prevention**: Smart checking to prevent duplicate database entries

## Monitoring

Check the Laravel logs for API errors and system status:

```bash
tail -f storage/logs/laravel.log
```

## Security Considerations

- API URLs should be moved to environment variables
- Consider implementing API authentication if required
- Add rate limiting for API endpoints
- Implement proper CORS policies

## Troubleshooting

### Common Issues

1. **API Connection Errors**
   - Check network connectivity
   - Verify API URLs in GeneratorApiService
   - Check Laravel logs for detailed error messages

2. **Database Issues**
   - Ensure database credentials are correct
   - Run `php artisan migrate:status` to check migration status
   - Clear cache: `php artisan cache:clear`

3. **Scheduler Not Running**
   - Ensure cron is properly configured
   - Test manually: `php artisan schedule:run`
   - Check system logs for cron errors

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
