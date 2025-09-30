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
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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
            $this->duration_seconds = $this->end_time->diffInSeconds($this->start_time);
            $this->save();
        }
        return $this->duration_seconds;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%02d:%02d', $minutes, $seconds);
        }
    }

    /**
     * Get current runtime for a generator
     */
    public static function getCurrentRuntime($generatorId)
    {
        return static::where('generator_id', $generatorId)
            ->where('status', 'running')
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
}
