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
        Schema::create('anfis_rules', function (Blueprint $table) {
            $table->id();
            $table->string('temperature_category');
            $table->string('humidity_category');
            $table->string('soil_ph_category');
            $table->string('pump_action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anfis_rules');
    }
};
