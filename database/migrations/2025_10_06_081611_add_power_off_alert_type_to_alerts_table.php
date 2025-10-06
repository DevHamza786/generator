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
        Schema::table('alerts', function (Blueprint $table) {
            // Update the enum to include the new power_off alert type
            $table->enum('type', ['fuel_low', 'battery_voltage', 'line_current', 'long_runtime', 'critical_runtime', 'power_off'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            // Remove the power_off alert type
            $table->enum('type', ['fuel_low', 'battery_voltage', 'line_current', 'long_runtime', 'critical_runtime'])->change();
        });
    }
};