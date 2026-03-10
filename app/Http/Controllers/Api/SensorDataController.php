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
        $validated = $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric',
            'soil_ph' => 'nullable|numeric',
        ]);

        // Ensure optional soil_ph is always available as null when omitted.
        $validated['soil_ph'] = $validated['soil_ph'] ?? null;

        DB::beginTransaction();
        try {
            // Find or create device
            $device = Device::firstOrCreate(
                [
                    'name' => $validated['name'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]
            );

            // Update device data if needed
            $device->update([
                'name' => $validated['name'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            // Store temperature data
            Temperature::create([
                'device_id' => $device->id,
                'value_temp' => $validated['temperature'],
            ]);

            // Store humidity data
            Humidity::create([
                'device_id' => $device->id,
                'value_humidity' => $validated['humidity'],
            ]);

            // Only store soil pH for devices that send this sensor.
            if (!is_null($validated['soil_ph'])) {
                SoilPH::create([
                    'device_id' => $device->id,
                    'value_ph' => $validated['soil_ph'],
                ]);
            }

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

            $groupedByDate = $humidities->groupBy(function ($hum) {
                return $hum->created_at->format('Y-m-d');
            });

            $humidityValues = [];
            $dates = [];

            foreach ($groupedByDate as $date => $items) {
                $average = $items->avg('value_humidity');
                $humidityValues[] = round($average, 2);
                $dates[] = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d-m-Y');
            }

            return response()->json([
                'success' => true,
                'humidityValues' => $humidityValues,
                'dates' => $dates,
                'average' => count($humidityValues) > 0 ? round(collect($humidityValues)->avg(), 2) : 0,
                'total_records' => count($humidities),
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
     * Returns average soil pH from all devices
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

            // Group by date and calculate average for each date
            $groupedByDate = $soilPHs->groupBy(function ($ph) {
                return $ph->created_at->format('Y-m-d');
            });

            $soilPHValues = [];
            $dates = [];

            foreach ($groupedByDate as $date => $items) {
                $average = $items->avg('value_ph');
                $soilPHValues[] = round($average, 2);
                $dates[] = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d-m-Y');
            }

            return response()->json([
                'success' => true,
                'soilPHValues' => $soilPHValues,
                'dates' => $dates,
                'average' => count($soilPHValues) > 0 ? round(collect($soilPHValues)->avg(), 2) : 0,
                'total_records' => count($soilPHs),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data soil pH: ' . $e->getMessage(),
            ], 500);
        }
    }
}
