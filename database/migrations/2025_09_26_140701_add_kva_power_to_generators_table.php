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
        Schema::table('generators', function (Blueprint $table) {
            $table->string('kva_power')->nullable()->after('name'); // e.g., "200kva", "27kva"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->dropColumn('kva_power');
        });
    }
};
