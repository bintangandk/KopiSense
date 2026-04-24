<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\ControlActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    /**
     * Get all devices
     */
    public function getDevices()
    {
        $devices = Device::all();
        return response()->json($devices);
    }

    /**
     * Get device data (latest readings)
     */
    public function getDeviceData($deviceId)
    {
        $device = Device::find($deviceId);

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // Get latest temperature
        $temperature = $device->temperatures()->latest('created_at')->first();

        // Get latest humidity
        $humidity = $device->humidities()->latest('created_at')->first();

        // Get latest soil pH
        $soilPH = $device->soilPHs()->latest('created_at')->first();

        // Get the latest date from all sensor data
        $dates = collect([
            $temperature?->created_at,
            $humidity?->created_at,
            $soilPH?->created_at
        ])->filter()->sortDesc();

        $latestDate = $dates->first() ?? now();

        return response()->json([
            'device' => $device,
            'temperature' => $temperature?->value_temp ?? null,
            'humidity' => $humidity?->value_humidity ?? null,
            'soilPH' => $soilPH?->value_ph ?? null,
            'date' => $latestDate->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }

    /**
     * Manual pump control - emergency override
     */
    /**
     * Manual pump control - saves to control_actions with MANUAL method
     * Always uses device_id = 2 (ACTUATOR device)
     * 
     * Expected request:
     * {
     *     "pump_status": 1,  // 1 = ON, 0 = OFF
     *     "mist_duration": 5.0  // duration in minutes when pump is ON
     * }
     */
    public function manualPumpControl(Request $request, $deviceId)
    {
        Log::info('🔧 Manual pump control request received', [
            'deviceId' => $deviceId,
            'request_body' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // Validate request
            $validated = $request->validate([
                'pump_status' => 'required|in:0,1',
                'mist_duration' => 'required|numeric|min:0',
            ]);

            Log::info('✅ Validation passed', $validated);

            // Get or create ACTUATOR device (device_id = 2)
            $actuatorDevice = Device::firstOrCreate(
                ['name' => 'System Actuator - Pump Control', 'device_type' => 'ACTUATOR'],
                [
                    'latitude' => 0,
                    'longitude' => 0,
                    'device_type' => 'ACTUATOR',
                ]
            );

            Log::info('✅ ACTUATOR device found/created', [
                'device_id' => $actuatorDevice->id,
                'device_name' => $actuatorDevice->name
            ]);

            // Save to control_actions table with MANUAL method
            $controlAction = ControlActions::create([
                'device_id' => $actuatorDevice->id,
                'pump_status' => (int) $validated['pump_status'],
                'mist_duration' => (float) $validated['mist_duration'],
                'method' => 'MANUAL',
                'aggregation_type' => 'MANUAL_OVERRIDE',
            ]);

            Log::info('✅ Control action saved', [
                'control_action_id' => $controlAction->id,
                'pump_status' => $controlAction->pump_status,
                'mist_duration' => $controlAction->mist_duration
            ]);

            $statusText = $validated['pump_status'] === '1' || $validated['pump_status'] === 1 ? 'HIDUP' : 'MATI';
            $message = "Pompa berhasil dikontrol secara manual - Status: $statusText";

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'pump_status' => $controlAction->pump_status,
                    'mist_duration' => $controlAction->mist_duration,
                    'method' => $controlAction->method,
                    'timestamp' => $controlAction->created_at->format('Y-m-d H:i:s'),
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validation error', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('❌ Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengontrol pompa: ' . $e->getMessage(),
            ], 500);
        }
    }
}
