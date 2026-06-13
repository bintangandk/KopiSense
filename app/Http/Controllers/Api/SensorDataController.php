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
     * Store sensor data from IoT device with ANFIS aggregation
     * 
     * This method:
     * 1. Saves individual sensor data (temperature, humidity, pH) from the device
     * 2. Aggregates latest data from all SENSOR devices
     * 3. Calls ANFIS API with aggregated data
     * 4. Creates a single ControlActions record for the ACTUATOR device
     * 
     * Expected JSON payload:
     * {
     *     "name": "Tunnel 1 Device 1",
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
            // Step 1: Find or create the reporting device (SENSOR type)
            $device = Device::firstOrCreate(
                [
                    'name' => $validated['name'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ],
                ['device_type' => 'SENSOR']
            );

            // Update device data if needed
            $device->update([
                'name' => $validated['name'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'device_type' => 'SENSOR',
            ]);

            // Step 2: Store temperature data for this device
            Temperature::create([
                'device_id' => $device->id,
                'value_temp' => $validated['temperature'],
            ]);

            // Step 3: Store humidity data for this device
            Humidity::create([
                'device_id' => $device->id,
                'value_humidity' => $validated['humidity'],
            ]);

            // Step 4: Store soil pH data if provided
            if (!is_null($validated['soil_ph'])) {
                SoilPH::create([
                    'device_id' => $device->id,
                    'value_ph' => $validated['soil_ph'],
                ]);
            }

            // Step 5: Aggregate data from all SENSOR devices
            $aggregatedData = $this->aggregateSensorData();

            if (!$aggregatedData) {
                throw new \Exception('Tidak ada data sensor yang tersedia untuk agregasi');
            }

            // Step 6: Call ANFIS API with aggregated data
            $anfisResult = $this->callAnfisPredict($aggregatedData);

            $pump_status = $anfisResult['pump_status'] ?? false;
            $mist_duration = $anfisResult['mist_duration'] ?? 0.0;
            $method = $anfisResult['method'] ?? 'ANFIS';
            $score = $anfisResult['skor_anfis'] ?? 0.0;

            // Step 7: Get or create ACTUATOR device for control actions
            $actuatorDevice = $this->getOrCreateActuatorDevice();

            // Step 8: Create control action record with aggregated sensor data reference
            $sensorDeviceIds = Device::where('device_type', 'SENSOR')
                ->pluck('id')
                ->toArray();

            ControlActions::create([
                'device_id' => $actuatorDevice->id,
                'sensor_device_ids' => $sensorDeviceIds,
                'pump_status' => $pump_status,
                'mist_duration' => $mist_duration,
                'method' => $method,
                'aggregation_type' => 'AVERAGE',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data sensor & tindakan kontrol berhasil disimpan dengan agregasi',
                'device_id' => $device->id,
                'aggregated_data' => $aggregatedData,
                'anfis_decision' => [
                    'pump_status' => $pump_status,
                    'mist_duration' => $mist_duration,
                    'method' => $method,
                    'skor_anfis' => $score,
                    'sensor_count' => count($sensorDeviceIds),
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
     * Aggregate latest sensor data from all SENSOR devices
     * 
     * Returns: [
     *     'temperature' => 28.5,
     *     'humidity' => 67.5,
     *     'soil_ph' => 6.8,
     *     'sensor_count' => 6
     * ]
     */
    private function aggregateSensorData()
    {
        // Get all SENSOR devices with their latest readings
        $sensorDevices = Device::where('device_type', 'SENSOR')
            ->with([
                'temperatures' => function ($query) {
                    $query->latest('created_at')->limit(1);
                },
                'humidities' => function ($query) {
                    $query->latest('created_at')->limit(1);
                },
                'soilPHs' => function ($query) {
                    $query->latest('created_at')->limit(1);
                }
            ])
            ->get();

        if ($sensorDevices->isEmpty()) {
            return null;
        }

        // Aggregate using AVERAGE method
        $temperatureValues = [];
        $humidityValues = [];
        $phValues = [];

        foreach ($sensorDevices as $device) {
            if ($device->temperatures->count() > 0) {
                $temperatureValues[] = $device->temperatures->first()->value_temp;
            }

            if ($device->humidities->count() > 0) {
                $humidityValues[] = $device->humidities->first()->value_humidity;
            }

            if ($device->soilPHs->count() > 0) {
                $phValues[] = $device->soilPHs->first()->value_ph;
            }
        }

        return [
            'temperature' => !empty($temperatureValues) ? array_sum($temperatureValues) / count($temperatureValues) : 0.0,
            'humidity' => !empty($humidityValues) ? array_sum($humidityValues) / count($humidityValues) : 0.0,
            'soil_ph' => !empty($phValues) ? array_sum($phValues) / count($phValues) : 7.0,
            'sensor_count' => $sensorDevices->count(),
        ];
    }

    /**
     * Call ANFIS prediction API with aggregated sensor data
     */
    private function callAnfisPredict(array $aggregatedData)
    {
        $pump_status = false;
        $mist_duration = 0.0;
        $method = 'ANFIS';
        $score = 0.0;

        try {
            $response = Http::post('http://fuzzy.kopisense.research-ai.my.id/predict', [
                'temperature' => $aggregatedData['temperature'],
                'humidity' => $aggregatedData['humidity'],
                'soil_ph' => $aggregatedData['soil_ph'],
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
            // Fallback logic when ANFIS unavailable
            if ($aggregatedData['temperature'] > 30 || $aggregatedData['humidity'] < 60) {
                $pump_status = true;
                $mist_duration = 5.0;
            }
        }

        return [
            'pump_status' => $pump_status,
            'mist_duration' => $mist_duration,
            'method' => $method,
            'skor_anfis' => $score,
        ];
    }

    /**
     * Get or create ACTUATOR device for pump control
     * 
     * This is the "master" device that receives control decisions
     */
    private function getOrCreateActuatorDevice()
    {
        return Device::firstOrCreate(
            ['name' => 'System Actuator - Pump Control', 'device_type' => 'ACTUATOR'],
            [
                'latitude' => 0,
                'longitude' => 0,
                'device_type' => 'ACTUATOR',
            ]
        );
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
     * Get latest ANFIS prediction for dashboard with aggregated sensor data
     * Returns the most recent control action with aggregated sensor readings from all SENSOR devices
     */
    public function getLatestPrediction(Request $request)
    {
        try {
            $query = ControlActions::query()
                ->with(['device'])
                ->orderBy('created_at', 'desc');

            $latestAction = $query->first();

            if (!$latestAction) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Belum ada prediksi ANFIS',
                ], 200);
            }

            // Get aggregated sensor data from all SENSOR devices
            $aggregatedData = $this->aggregateSensorData();
            $sensorDeviceCount = Device::where('device_type', 'SENSOR')->count();

            // Determine status berdasarkan ANFIS decision
            $status = 'Aman';
            $statusColor = 'success';
            $statusMessage = 'Lingkungan dalam kondisi baik';

            if ($latestAction->pump_status) {
                if ($latestAction->mist_duration > 10) {
                    $status = 'Kondisi Buruk';
                    $statusColor = 'danger';
                    $statusMessage = 'Lingkungan memerlukan perhatian khusus';
                } else {
                    $status = 'Peringatan';
                    $statusColor = 'warning';
                    $statusMessage = 'Lingkungan memerlukan penyesuaian';
                }
            } else {
                $status = 'Sesuai';
                $statusColor = 'success';
                $statusMessage = 'Lingkungan dalam kondisi baik';
            }

            // Determine temperature status
            // Normal: 20-28°C | Bahaya: di luar rentang tersebut
            $tempStatus = 'Normal';
            $tempBadge = 'bg-success';
            if ($aggregatedData['temperature'] < 20 || $aggregatedData['temperature'] > 28) {
                $tempStatus = 'Bahaya';
                $tempBadge = 'bg-danger';
            }

            // Determine humidity status
            // Normal: 70-100% | Bahaya: di luar rentang tersebut
            $humidityStatus = 'Normal';
            $humidityBadge = 'bg-success';
            if ($aggregatedData['humidity'] < 70 || $aggregatedData['humidity'] > 100) {
                $humidityStatus = 'Bahaya';
                $humidityBadge = 'bg-danger';
            }

            // Determine soil pH status
            // Normal: 5.5-6.5 | Bahaya: di luar rentang tersebut
            $phStatus = 'Normal';
            $phBadge = 'bg-success';
            if ($aggregatedData['soil_ph'] < 5.5 || $aggregatedData['soil_ph'] > 6.5) {
                $phStatus = 'Bahaya';
                $phBadge = 'bg-danger';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'status_color' => $statusColor,
                    'status_message' => $statusMessage,
                    'pump_status' => $latestAction->pump_status,
                    'mist_duration' => $latestAction->mist_duration,
                    'method' => $latestAction->method,
                    'aggregated_data' => [
                        'temperature' => round($aggregatedData['temperature'], 2),
                        'temperature_status' => $tempStatus,
                        'temperature_badge' => $tempBadge,
                        'humidity' => round($aggregatedData['humidity'], 2),
                        'humidity_status' => $humidityStatus,
                        'humidity_badge' => $humidityBadge,
                        'soil_ph' => round($aggregatedData['soil_ph'], 2),
                        'soil_ph_status' => $phStatus,
                        'soil_ph_badge' => $phBadge,
                        'sensor_count' => $sensorDeviceCount,
                    ],
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
