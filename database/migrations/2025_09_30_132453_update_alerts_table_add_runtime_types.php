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
        // For SQLite, we need to recreate the table to modify the enum
        if (DB::getDriverName() === 'sqlite') {
            // Create new table with updated enum
            Schema::create('alerts_new', function (Blueprint $table) {
                $table->id();
                $table->string('generator_id');
                $table->string('client_id')->nullable();
                $table->string('sitename')->nullable();
                $table->enum('type', ['fuel_low', 'battery_voltage', 'line_current', 'long_runtime', 'critical_runtime']);
                $table->string('title');
                $table->text('message');
                $table->json('data')->nullable();
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');
                $table->timestamp('triggered_at');
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->unsignedBigInteger('acknowledged_by')->nullable();
                $table->timestamps();

                $table->index(['generator_id', 'type', 'status']);
                $table->index(['status', 'triggered_at']);
            });

            // Copy data from old table
            DB::statement('INSERT INTO alerts_new SELECT * FROM alerts');

            // Drop old table and rename new one
            Schema::drop('alerts');
            Schema::rename('alerts_new', 'alerts');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For SQLite, we need to recreate the table to modify the enum
        if (DB::getDriverName() === 'sqlite') {
            // Create old table
            Schema::create('alerts_old', function (Blueprint $table) {
                $table->id();
                $table->string('generator_id');
                $table->string('client_id')->nullable();
                $table->string('sitename')->nullable();
                $table->enum('type', ['fuel_low', 'battery_voltage', 'line_current']);
                $table->string('title');
                $table->text('message');
                $table->json('data')->nullable();
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');
                $table->timestamp('triggered_at');
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->unsignedBigInteger('acknowledged_by')->nullable();
                $table->timestamps();

                $table->index(['generator_id', 'type', 'status']);
                $table->index(['status', 'triggered_at']);
            });

            // Copy data back (excluding new types)
            DB::statement('INSERT INTO alerts_old SELECT * FROM alerts WHERE type IN ("fuel_low", "battery_voltage", "line_current")');

            // Drop current table and rename old one
            Schema::drop('alerts');
            Schema::rename('alerts_old', 'alerts');
        }
    }
};
