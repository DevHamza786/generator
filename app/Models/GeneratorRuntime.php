<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class GeneratorRuntime extends Model
{
    protected $fillable = [
        'generator_id',
        'client_id',
        'sitename',
        'start_time',
        'end_time',
        'duration_seconds',
        'start_voltage_l1',
        'start_voltage_l2',
        'start_voltage_l3',
        'end_voltage_l1',
        'end_voltage_l2',
        'end_voltage_l3',
        'status',
        'maintenance_status',
        'maintenance_started_at',
        'maintenance_completed_at',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'maintenance_started_at' => 'datetime',
        'maintenance_completed_at' => 'datetime',
        'start_voltage_l1' => 'decimal:2',
        'start_voltage_l2' => 'decimal:2',
        'start_voltage_l3' => 'decimal:2',
        'end_voltage_l1' => 'decimal:2',
        'end_voltage_l2' => 'decimal:2',
        'end_voltage_l3' => 'decimal:2',
    ];

    /**
     * Get the client that owns this runtime record
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the generator that owns this runtime record
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeStopped($query)
    {
        return $query->where('status', 'stopped');
    }

    public function scopeByGenerator($query, $generatorId)
    {
        return $query->where('generator_id', $generatorId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('start_time', '>=', now()->subDays($days));
    }

    /**
     * Calculate and update duration
     */
    public function calculateDuration()
    {
        if ($this->end_time && $this->start_time) {
            $this->duration_seconds = $this->start_time->diffInSeconds($this->end_time);
            $this->save();
        }
        return $this->duration_seconds;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        // For running generators, calculate real-time duration
        if ($this->status === 'running' && $this->start_time) {
            $durationSeconds = $this->start_time->diffInSeconds(now());
        } elseif ($this->duration_seconds) {
            $durationSeconds = $this->duration_seconds;
        } else {
            return 'N/A';
        }

        $days = floor($durationSeconds / 86400);
        $hours = floor(($durationSeconds % 86400) / 3600);
        $minutes = floor(($durationSeconds % 3600) / 60);

        $result = '';

        if ($days > 0) {
            $result .= $days . ' day' . ($days > 1 ? 's' : '') . ' ';
        }

        if ($hours > 0) {
            $result .= $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ';
        }

        if ($minutes > 0) {
            $result .= $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ';
        }

        // If all are zero, show at least minutes
        if (empty(trim($result))) {
            $result = 'Less than 1 minute';
        }

        return trim($result);
    }

    /**
     * Get current runtime for a generator
     */
    public static function getCurrentRuntime($generatorId)
    {
        return static::where('generator_id', $generatorId)
            ->where('status', 'running')
            ->whereNull('end_time') // Only get records that don't have an end time
            ->latest('start_time')
            ->first();
    }

    /**
     * Stop the runtime and calculate duration
     */
    public function stop($endVoltages = [])
    {
        $this->end_time = now();
        $this->status = 'stopped';

        if (isset($endVoltages['LV1'])) $this->end_voltage_l1 = $endVoltages['LV1'];
        if (isset($endVoltages['LV2'])) $this->end_voltage_l2 = $endVoltages['LV2'];
        if (isset($endVoltages['LV3'])) $this->end_voltage_l3 = $endVoltages['LV3'];

        $this->calculateDuration();
        $this->save();

        return $this;
    }

    /**
     * Get maintenance status badge class
     */
    public function getMaintenanceStatusBadgeClassAttribute()
    {
        return match($this->maintenance_status) {
            'none' => 'badge-secondary',
            'scheduled' => 'badge-info',
            'overdue' => 'badge-danger',
            'in_progress' => 'badge-warning',
            'completed' => 'badge-success',
            default => 'badge-secondary'
        };
    }

    /**
     * Get maintenance status text
     */
    public function getMaintenanceStatusTextAttribute()
    {
        return match($this->maintenance_status) {
            'none' => 'No maintenance required',
            'scheduled' => 'Maintenance scheduled',
            'overdue' => 'Maintenance overdue',
            'in_progress' => 'Maintenance in progress',
            'completed' => 'Maintenance completed',
            default => 'Unknown'
        };
    }

    /**
     * Get maintenance status icon
     */
    public function getMaintenanceStatusIconAttribute()
    {
        return match($this->maintenance_status) {
            'none' => 'fas fa-check-circle text-success',
            'scheduled' => 'fas fa-calendar-alt text-info',
            'overdue' => 'fas fa-exclamation-triangle text-danger',
            'in_progress' => 'fas fa-tools text-warning',
            'completed' => 'fas fa-check-double text-success',
            default => 'fas fa-question-circle text-secondary'
        };
    }

    /**
     * Scope for maintenance status
     */
    public function scopeMaintenanceStatus($query, $status)
    {
        return $query->where('maintenance_status', $status);
    }

    /**
     * Scope for overdue maintenance
     */
    public function scopeOverdueMaintenance($query)
    {
        return $query->where('maintenance_status', 'overdue');
    }

    /**
     * Scope for scheduled maintenance
     */
    public function scopeScheduledMaintenance($query)
    {
        return $query->where('maintenance_status', 'scheduled');
    }
}
