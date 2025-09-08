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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('client_id')->unique()->after('name');
            $table->string('display_name')->nullable()->after('client_id');
            $table->text('description')->nullable()->after('display_name');
            $table->boolean('is_active')->default(true)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['name', 'client_id', 'display_name', 'description', 'is_active']);
        });
    }
};
