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
        Schema::create('generator_runtimes', function (Blueprint $table) {
            $table->id();
            $table->string('generator_id');
            $table->string('client_id')->nullable();
            $table->string('sitename')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->integer('duration_seconds')->nullable(); // Calculated duration in seconds
            $table->decimal('start_voltage_l1', 8, 2)->nullable();
            $table->decimal('start_voltage_l2', 8, 2)->nullable();
            $table->decimal('start_voltage_l3', 8, 2)->nullable();
            $table->decimal('end_voltage_l1', 8, 2)->nullable();
            $table->decimal('end_voltage_l2', 8, 2)->nullable();
            $table->decimal('end_voltage_l3', 8, 2)->nullable();
            $table->enum('status', ['running', 'stopped'])->default('running');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['generator_id', 'status']);
            $table->index(['start_time', 'end_time']);
            $table->index(['status', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generator_runtimes');
    }
};
