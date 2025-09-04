<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratorStatus extends Model
{
    protected $fillable = [
        'generator_id',
        'power',
        'last_updated',
    ];

    protected $casts = [
        'power' => 'boolean',
        'last_updated' => 'datetime',
    ];

    public function scopeByGeneratorId($query, $generatorId)
    {
        return $query->where('generator_id', $generatorId);
    }
}
