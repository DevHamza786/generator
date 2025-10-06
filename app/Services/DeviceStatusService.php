<?php

namespace App\Services;

use App\Models\GeneratorLog;
use App\Models\GeneratorWriteLog;
use App\Models\GeneratorStatus;
use Carbon\Carbon;

class DeviceStatusService
{
    /**
     * Check if device is active based on recent log data (within 1 minute)
     */
    public function isDeviceActive($generatorId, $minutesThreshold = 1)
    {
        $cutoffTime = Carbon::now()->subMinutes($minutesThreshold);
        
        // Check for recent logs
        $recentLog = GeneratorLog::where('generator_id', $generatorId)
            ->where('log_timestamp', '>=', $cutoffTime)
            ->latest('log_timestamp')
            ->first();
            
        // Check for recent write logs
        $recentWriteLog = GeneratorWriteLog::where('generator_id', $generatorId)
            ->where('write_timestamp', '>=', $cutoffTime)
            ->latest('write_timestamp')
            ->first();
            
        return $recentLog || $recentWriteLog;
    }
    
    /**
     * Get device status with detailed information
     */
    public function getDeviceStatus($generatorId, $minutesThreshold = 1)
    {
        $cutoffTime = Carbon::now()->subMinutes($minutesThreshold);
        
        // Get latest log data
        $latestLog = GeneratorLog::where('generator_id', $generatorId)
            ->latest('log_timestamp')
            ->first();
            
        $latestWriteLog = GeneratorWriteLog::where('generator_id', $generatorId)
            ->latest('write_timestamp')
            ->first();
            
        // Check if device is active
        $isActive = $this->isDeviceActive($generatorId, $minutesThreshold);
        
        // Determine power status from log data
        $powerStatus = false;
        $lastDataTime = null;
        
        if ($latestLog && $latestLog->log_timestamp >= $cutoffTime) {
            $powerStatus = $latestLog->GS ?? false; // GS field indicates generator status
            $lastDataTime = $latestLog->log_timestamp;
        } elseif ($latestWriteLog && $latestWriteLog->write_timestamp >= $cutoffTime) {
            $powerStatus = $latestWriteLog->GS ?? false;
            $lastDataTime = $latestWriteLog->write_timestamp;
        }
        
        return [
            'generator_id' => $generatorId,
            'is_active' => $isActive,
            'power_status' => $powerStatus,
            'last_data_time' => $lastDataTime,
            'minutes_since_last_data' => $lastDataTime ? Carbon::now()->diffInMinutes($lastDataTime) : null,
            'status_text' => $isActive ? 'ACTIVE' : 'INACTIVE',
            'power_text' => $powerStatus ? 'POWER ON' : 'POWER OFF'
        ];
    }
    
    /**
     * Update generator status based on recent log data
     */
    public function updateGeneratorStatus($generatorId, $minutesThreshold = 1)
    {
        $status = $this->getDeviceStatus($generatorId, $minutesThreshold);
        
        // Update or create generator status record
        GeneratorStatus::updateOrCreate(
            ['generator_id' => $generatorId],
            [
                'power' => $status['power_status'],
                'last_updated' => now()
            ]
        );
        
        return $status;
    }
    
    /**
     * Get all generators with their current status
     */
    public function getAllGeneratorsStatus($minutesThreshold = 1)
    {
        $generators = \App\Models\Generator::all();
        $statuses = [];
        
        foreach ($generators as $generator) {
            $statuses[] = $this->getDeviceStatus($generator->generator_id, $minutesThreshold);
        }
        
        return $statuses;
    }
    
    /**
     * Get active generators count
     */
    public function getActiveGeneratorsCount($minutesThreshold = 1)
    {
        $cutoffTime = Carbon::now()->subMinutes($minutesThreshold);
        
        $activeGenerators = GeneratorLog::where('log_timestamp', '>=', $cutoffTime)
            ->distinct('generator_id')
            ->pluck('generator_id')
            ->toArray();
            
        $activeWriteLogGenerators = GeneratorWriteLog::where('write_timestamp', '>=', $cutoffTime)
            ->distinct('generator_id')
            ->pluck('generator_id')
            ->toArray();
            
        $allActive = array_unique(array_merge($activeGenerators, $activeWriteLogGenerators));
        
        return count($allActive);
    }
}
