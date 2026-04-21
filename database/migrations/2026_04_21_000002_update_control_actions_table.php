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
        Schema::table('control_actions', function (Blueprint $table) {
            // Make device_id nullable to allow for system-level control records
            $table->foreignId('device_id')->nullable()->change();

            // Add metadata for sensor aggregation tracking
            $table->json('sensor_device_ids')->nullable()->after('device_id')->comment('JSON array of sensor device IDs used in aggregation');
            $table->string('aggregation_type')->default('AVERAGE')->after('sensor_device_ids')->comment('Type of aggregation used (AVERAGE, MIN, MAX, etc)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_actions', function (Blueprint $table) {
            $table->dropColumn(['sensor_device_ids', 'aggregation_type']);
            $table->foreignId('device_id')->change();
        });
    }
};
