<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Temperature;
use App\Models\Humidity;
use App\Models\SoilPH;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SensorDataController extends Controller
{
    /**
     * Store sensor data from IoT device
     * 
     * Expected JSON payload:
     * {
     *     "device_id": "device_001",
     *     "name": "Device 1",
     *     "latitude": -6.9271,
     *     "longitude": 107.6412,
     *     "temperature": 28.5,
     *     "humidity": 65.3,
     *     "soil_ph": 6.8
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric',
            'soil_ph' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // Find or create device
            $device = Device::firstOrCreate(
                [
                    'name' => $request->name,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]
            );

            // Update device data if needed
            $device->update([
                'name' => $request->name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Store temperature data
            Temperature::create([
                'device_id' => $device->id,
                'value_temp' => $request->temperature,
            ]);

            // Store humidity data
            Humidity::create([
                'device_id' => $device->id,
                'value_humidity' => $request->humidity,
            ]);

            // Store soil pH data
            SoilPH::create([
                'device_id' => $device->id,
                'value_ph' => $request->soil_ph,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data sensor berhasil disimpan',
                'device_id' => $device->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data sensor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get latest sensor data for a device
     */
    public function show($deviceId)
    {
        try {
            $device = Device::with([
                'temperatures' => function ($query) {
                    $query->latest()->limit(10);
                },
                'humidities' => function ($query) {
                    $query->latest()->limit(10);
                },
                'soilPHs' => function ($query) {
                    $query->latest()->limit(10);
                }
            ])->findOrFail($deviceId);

            return response()->json([
                'success' => true,
                'data' => $device,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Get all devices
     */
    public function index()
    {
        try {
            $devices = Device::with([
                'temperatures' => function ($query) {
                    $query->latest()->first();
                },
                'humidities' => function ($query) {
                    $query->latest()->first();
                },
                'soilPHs' => function ($query) {
                    $query->latest()->first();
                }
            ])->get();

            return response()->json([
                'success' => true,
                'data' => $devices,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data devices',
            ], 500);
        }
    }
}
