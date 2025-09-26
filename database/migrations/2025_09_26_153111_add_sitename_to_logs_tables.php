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
        Schema::table('generator_logs', function (Blueprint $table) {
            $table->string('sitename')->nullable()->after('generator_id');
        });

        Schema::table('generator_write_logs', function (Blueprint $table) {
            $table->string('sitename')->nullable()->after('generator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generator_logs', function (Blueprint $table) {
            $table->dropColumn('sitename');
        });

        Schema::table('generator_write_logs', function (Blueprint $table) {
            $table->dropColumn('sitename');
        });
    }
};
