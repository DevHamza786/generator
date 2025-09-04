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
        Schema::create('generator_logs', function (Blueprint $table) {
            $table->id();
            $table->string('generator_id');
            $table->string('client');
            $table->boolean('PS')->default(false);
            $table->integer('FL');
            $table->boolean('GS')->default(false);
            $table->integer('yy');
            $table->integer('mm');
            $table->integer('dd');
            $table->integer('hm');
            $table->decimal('BV', 8, 2);
            $table->decimal('LV1', 8, 2);
            $table->decimal('LV2', 8, 2);
            $table->decimal('LV3', 8, 2);
            $table->decimal('LV12', 8, 2);
            $table->decimal('LV23', 8, 2);
            $table->decimal('LV31', 8, 2);
            $table->decimal('LI1', 8, 2);
            $table->decimal('LI2', 8, 2);
            $table->decimal('LI3', 8, 2);
            $table->decimal('Lf1', 8, 2);
            $table->decimal('Lf2', 8, 2);
            $table->decimal('Lf3', 8, 2);
            $table->decimal('Lpf1', 8, 2);
            $table->decimal('Lpf2', 8, 2);
            $table->decimal('Lpf3', 8, 2);
            $table->decimal('Lkva1', 8, 2);
            $table->decimal('Lkva2', 8, 2);
            $table->decimal('Lkva3', 8, 2);
            $table->timestamp('log_timestamp');
            $table->timestamps();

            $table->index(['generator_id', 'log_timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generator_logs');
    }
};
