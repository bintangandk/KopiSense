<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Temperature;
use App\Models\Humidity;
use App\Models\SoilPH;
use App\Models\Device;
use App\Models\ControlActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

            $ph_for_anfis = !is_null($validated['soil_ph']) ? $validated['soil_ph'] : 7.0; // Default to neutral if not provided

            // Only store soil pH for devices that send this sensor.
            if (!is_null($validated['soil_ph'])) {
                SoilPH::create([
                    'device_id' => $device->id,
                    'value_ph' => $validated['soil_ph'],
                ]);
            }

            $pump_status = false;
            $mist_duration = 0.0;
            $method = 'ANFIS';

            try {
                $response = Http::post('http://localhost:5001/predict', [
                    'temperature' => $validated['temperature'],
                    'humidity' => $validated['humidity'],
                    'soil_ph' => $ph_for_anfis,
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $score = $result['skor_anfis'] ?? 0.0;
                    $pump_status = $result['pump_status'] ?? false;
                    $mist_duration = $result['mist_duration'] ?? 0.0;
                    $method = 'ANFIS';
                } else {
                    $method = 'ERROR_FALLBACK';
                }
            } catch (\Exception $e) {
                $method = 'CONNECTION_FAILED';
                if ($validated['temperature'] > 30 || $validated['humidity'] < 60) {
                    $pump_status = true;
                    $mist_duration = 5.0;
                }
            }

            ControlActions::create([
                'device_id' => $device->id,
                'pump_status' => $pump_status,
                'mist_duration' => $mist_duration,
                'method' => $method,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data sensor & tindakan kontrol berhasil disimpan',
                'device_id' => $device->id,
                'anfis_decision' => [
                    'pump_status' => $pump_status,
                    'mist_duration' => $mist_duration,
                    'method' => $method,
                    'skor_anfis' => $score,
                ],
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
            // Validate date inputs to prevent unexpected values
            $validated = $request->validate([
                'start_date' => ['sometimes', 'date_format:Y-m-d'],
                'end_date'   => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                'device_id'  => ['sometimes', 'string', 'max:100'],
            ]);

            $query = Temperature::query();

            // Filter by date range if provided
            if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
                $query->whereBetween('created_at', [
                    $validated['start_date'] . ' 00:00:00',
                    $validated['end_date']   . ' 23:59:59',
                ]);
            }

            // Filter by device if provided
            if (!empty($validated['device_id'])) {
                $query->where('device_id', $validated['device_id']);
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

    /**
     * Get latest ANFIS prediction for dashboard
     * Returns the most recent control action with sensor data
     */
    public function getLatestPrediction(Request $request)
    {
        try {
            $deviceId = $request->query('device_id');

            $query = ControlActions::query()
                ->with(['device'])
                ->where('method', 'ANFIS')
                ->orderBy('created_at', 'desc');

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            }

            $latestAction = $query->first();

            if (!$latestAction) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Belum ada prediksi ANFIS',
                ], 200);
            }

            // Get the latest sensor readings for this device at the same time
            $device = $latestAction->device;
            $temperature = Temperature::where('device_id', $device->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $humidity = Humidity::where('device_id', $device->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $soilPH = SoilPH::where('device_id', $device->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Determine status berdasarkan ANFIS decision
            $status = 'Aman';
            $statusColor = 'success';

            if ($latestAction->pump_status) {
                if ($latestAction->mist_duration > 10) {
                    $status = 'Kondisi Buruk';
                    $statusColor = 'danger';
                } else {
                    $status = 'Peringatan';
                    $statusColor = 'warning';
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'status' => $status,
                    'status_color' => $statusColor,
                    'pump_status' => $latestAction->pump_status,
                    'mist_duration' => $latestAction->mist_duration,
                    'method' => $latestAction->method,
                    'temperature' => $temperature?->value_temp,
                    'humidity' => $humidity?->value_humidity,
                    'soil_ph' => $soilPH?->value_ph,
                    'timestamp' => $latestAction->created_at->format('Y-m-d H:i:s'),
                    'timestamp_readable' => $latestAction->created_at->locale('id')->translatedFormat('l, d F Y H:i'),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil prediksi ANFIS: ' . $e->getMessage(),
            ], 500);
        }
    }
}
