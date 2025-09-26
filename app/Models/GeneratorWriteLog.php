<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratorWriteLog extends Model
{
    protected $fillable = [
        'client_id',
        'generator_id',
        'sitename',
        'generator_id_old', // Keep old field for backward compatibility
        'client', // Keep old field for backward compatibility
        'PS',
        'FL',
        'BV',
        'LV1',
        'LV2',
        'LV3',
        'LV12',
        'LV23',
        'LV31',
        'LI1',
        'LI2',
        'LI3',
        'Lf1',
        'Lf2',
        'Lf3',
        'Lpf1',
        'Lpf2',
        'Lpf3',
        'Lkva1',
        'Lkva2',
        'Lkva3',
        'write_timestamp',
    ];

    protected $casts = [
        'PS' => 'boolean',
        'FL' => 'integer',
        'BV' => 'decimal:2',
        'LV1' => 'decimal:2',
        'LV2' => 'decimal:2',
        'LV3' => 'decimal:2',
        'LV12' => 'decimal:2',
        'LV23' => 'decimal:2',
        'LV31' => 'decimal:2',
        'LI1' => 'decimal:2',
        'LI2' => 'decimal:2',
        'LI3' => 'decimal:2',
        'Lf1' => 'decimal:2',
        'Lf2' => 'decimal:2',
        'Lf3' => 'decimal:2',
        'Lpf1' => 'decimal:2',
        'Lpf2' => 'decimal:2',
        'Lpf3' => 'decimal:2',
        'Lkva1' => 'decimal:2',
        'Lkva2' => 'decimal:2',
        'Lkva3' => 'decimal:2',
        'write_timestamp' => 'datetime',
    ];

    /**
     * Get the client that owns this write log
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the generator that owns this write log
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    public function scopeLatest($query, $limit = 20)
    {
        return $query->orderBy('write_timestamp', 'desc')->limit($limit);
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByGenerator($query, $generatorId)
    {
        return $query->where('generator_id', $generatorId);
    }
}
