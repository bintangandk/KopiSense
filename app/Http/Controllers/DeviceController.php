<?php

namespace App\Http\Controllers;

use App\Models\Device;
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
}
