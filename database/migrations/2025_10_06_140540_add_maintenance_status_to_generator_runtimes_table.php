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
            $table->enum('maintenance_status', ['none', 'scheduled', 'overdue', 'in_progress', 'completed'])
                  ->default('none')
                  ->after('status')
                  ->comment('Maintenance status for this runtime session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generator_runtimes', function (Blueprint $table) {
            $table->dropColumn('maintenance_status');
        });
    }
};
