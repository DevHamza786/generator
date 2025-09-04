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
        Schema::create('generator_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('generator_id')->unique();
            $table->boolean('power')->default(false);
            $table->timestamp('last_updated');
            $table->timestamps();

            $table->index(['generator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generator_statuses');
    }
};
