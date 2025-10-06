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
        Schema::table('generator_runtimes', function (Blueprint $table) {
            $table->timestamp('maintenance_started_at')->nullable()->after('maintenance_status');
            $table->timestamp('maintenance_completed_at')->nullable()->after('maintenance_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generator_runtimes', function (Blueprint $table) {
            $table->dropColumn(['maintenance_started_at', 'maintenance_completed_at']);
        });
    }
};
