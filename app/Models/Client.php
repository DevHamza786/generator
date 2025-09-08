<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'client_id',
        'display_name',
        'description',
        'is_active'
    ];

    /**
     * Get all generators for this client
     */
    public function generators(): HasMany
    {
        return $this->hasMany(Generator::class);
    }

    /**
     * Get all logs for this client
     */
    public function logs(): HasMany
    {
        return $this->hasMany(GeneratorLog::class);
    }

    /**
     * Get all write logs for this client
     */
    public function writeLogs(): HasMany
    {
        return $this->hasMany(GeneratorWriteLog::class);
    }

    /**
     * Extract client name from client_id (e.g., "axact#100" -> "axact")
     */
    public static function extractClientName(string $clientId): string
    {
        return explode('#', $clientId)[0];
    }

    /**
     * Extract client number from client_id (e.g., "axact#100" -> "100")
     */
    public static function extractClientNumber(string $clientId): string
    {
        $parts = explode('#', $clientId);
        return $parts[1] ?? '';
    }
}
