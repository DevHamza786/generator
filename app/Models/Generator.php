<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Generator extends Model
{
    protected $fillable = [
        'client_id',
        'generator_id',
        'name',
        'kva_power',
        'description',
        'location',
        'is_active'
    ];

    /**
     * Get the client that owns this generator
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get all logs for this generator
     */
    public function logs(): HasMany
    {
        return $this->hasMany(GeneratorLog::class);
    }

    /**
     * Get all write logs for this generator
     */
    public function writeLogs(): HasMany
    {
        return $this->hasMany(GeneratorWriteLog::class);
    }

    /**
     * Get the latest log for this generator
     */
    public function latestLog()
    {
        return $this->hasOne(GeneratorLog::class)->latest();
    }

    /**
     * Get the latest write log for this generator
     */
    public function latestWriteLog()
    {
        return $this->hasOne(GeneratorWriteLog::class)->latest();
    }
}
