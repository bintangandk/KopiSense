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
        Schema::create('environment_thresholds', function (Blueprint $table) {
            $table->id();
            $table->decimal('temperature_min', 5, 2);
            $table->decimal('temperature_max', 5, 2);
            $table->decimal('humidity_min', 5, 2);
            $table->decimal('humidity_max', 5, 2);
            $table->decimal('soil_ph_min', 5, 2);
            $table->decimal('soil_ph_max', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environment_thresholds');
    }
};
