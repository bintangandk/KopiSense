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

    /**
     * Get temperature data with date range filter
     * Returns average temperature from all devices
     */
    public function getTemperatureData(Request $request)
    {
        try {
            $query = Temperature::query();

            // Filter by date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->start_date . ' 00:00:00';
                $endDate = $request->end_date . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Filter by device if provided
            if ($request->has('device_id')) {
                $query->where('device_id', $request->device_id);
            }

            // Get all temperatures ordered by created_at
            $temperatures = $query->orderBy('created_at')->get();

            // Group by date and calculate average for each date
            $groupedByDate = $temperatures->groupBy(function ($temp) {
                return $temp->created_at->format('Y-m-d');
            });

            $temperatureValues = [];
            $dates = [];

            foreach ($groupedByDate as $date => $items) {
                $average = $items->avg('value_temp');
                $temperatureValues[] = round($average, 2);
                $dates[] = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d-m-Y');
            }

            return response()->json([
                'success' => true,
                'temperatureValues' => $temperatureValues,
                'dates' => $dates,
                'average' => count($temperatureValues) > 0 ? round(collect($temperatureValues)->avg(), 2) : 0,
                'total_records' => count($temperatures),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data temperature: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get humidity data with date range filter
     */
    public function getHumidityData(Request $request)
    {
        try {
            $query = Humidity::query();

            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->start_date . ' 00:00:00';
                $endDate = $request->end_date . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($request->has('device_id')) {
                $query->where('device_id', $request->device_id);
            }

            $humidities = $query->orderBy('created_at')->get();

            $humidityValues = $humidities->map(function ($hum) {
                return $hum->value_humidity;
            })->toArray();

            $dates = $humidities->map(function ($hum) {
                return $hum->created_at->format('d-m-Y');
            })->toArray();

            return response()->json([
                'success' => true,
                'humidityValues' => $humidityValues,
                'dates' => $dates,
                'data' => $humidities,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data humidity: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get soil pH data with date range filter
     */
    public function getSoilPHData(Request $request)
    {
        try {
            $query = SoilPH::query();

            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->start_date . ' 00:00:00';
                $endDate = $request->end_date . ' 23:59:59';
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($request->has('device_id')) {
                $query->where('device_id', $request->device_id);
            }

            $soilPHs = $query->orderBy('created_at')->get();

            $soilPHValues = $soilPHs->map(function ($ph) {
                return $ph->value_ph;
            })->toArray();

            $dates = $soilPHs->map(function ($ph) {
                return $ph->created_at->format('d-m-Y');
            })->toArray();

            return response()->json([
                'success' => true,
                'soilPHValues' => $soilPHValues,
                'dates' => $dates,
                'data' => $soilPHs,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data soil pH: ' . $e->getMessage(),
            ], 500);
        }
    }
}
