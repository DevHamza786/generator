-- Timezone Fix SQL Script
-- Run this directly in phpMyAdmin or MySQL command line
-- This adds 5 hours to all timestamps to convert from UTC to Asia/Karachi

-- Backup reminder: Always backup your database before running these commands!

-- Update GeneratorLogs timestamps (add 5 hours)
UPDATE generator_logs 
SET log_timestamp = DATE_ADD(log_timestamp, INTERVAL 5 HOUR);

-- Update GeneratorWriteLogs timestamps (add 5 hours)  
UPDATE generator_write_logs 
SET write_timestamp = DATE_ADD(write_timestamp, INTERVAL 5 HOUR);

-- Check results (optional - run these to verify)
-- SELECT COUNT(*) as total_logs FROM generator_logs;
-- SELECT COUNT(*) as total_write_logs FROM generator_write_logs;
-- SELECT log_timestamp FROM generator_logs ORDER BY log_timestamp DESC LIMIT 5;
-- SELECT write_timestamp FROM generator_write_logs ORDER BY write_timestamp DESC LIMIT 5;

-- Example of what happens:
-- Before: 2025-10-06 00:03:50 (UTC)
-- After:  2025-10-06 05:03:50 (PKT - Pakistan Standard Time)
