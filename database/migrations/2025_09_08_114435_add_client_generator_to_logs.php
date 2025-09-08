<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only add missing columns to generator_write_logs table
        Schema::table('generator_write_logs', function (Blueprint $table) {
            // Check if generator_id_old column doesn't exist before adding it
            if (!Schema::hasColumn('generator_write_logs', 'generator_id_old')) {
                $table->string('generator_id_old')->nullable(); // Keep old generator_id as varchar
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generator_write_logs', function (Blueprint $table) {
            if (Schema::hasColumn('generator_write_logs', 'generator_id_old')) {
                $table->dropColumn(['generator_id_old']);
            }
        });
    }
};
