<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\ControlActions;
use Illuminate\Http\Request;

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
    public function manualPumpControl(Request $request, $deviceId)
    {
        $device = Device::find($deviceId);

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // Validate request
        $validated = $request->validate([
            'pump_status' => 'required|in:0,1',
        ]);

        try {
            // Save to control_actions table
            $controlAction = ControlActions::create([
                'device_id' => $deviceId,
                'pump_status' => $validated['pump_status'],
                'method' => 'MANUAL',
                'mist_duration' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pompa berhasil dikontrol secara manual',
                'data' => $controlAction,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengontrol pompa: ' . $e->getMessage(),
            ], 500);
        }
    }
}
