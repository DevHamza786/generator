<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clean up any existing duplicate running records
        // Keep only the latest running record for each generator (SQLite compatible)
        DB::statement("
            DELETE FROM generator_runtimes 
            WHERE id NOT IN (
                SELECT MAX(id) 
                FROM generator_runtimes 
                WHERE status = 'running' 
                GROUP BY generator_id
            ) 
            AND status = 'running'
        ");

        // Add a more specific index to help with queries
        Schema::table('generator_runtimes', function (Blueprint $table) {
            $table->index(['generator_id', 'status', 'start_time'], 'idx_generator_status_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generator_runtimes', function (Blueprint $table) {
            $table->dropIndex('idx_generator_status_start');
        });
    }
};