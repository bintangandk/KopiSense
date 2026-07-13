<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Temperature;
use App\Models\Humidity;
use App\Models\SoilPH;
use App\Models\Device;
use App\Models\ControlActions;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SensorDataController extends Controller
{
    /**
     * Store sensor data from IoT device with ANFIS aggregation
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'temperature' => 'required|numeric',
            'humidity' => 'required|numeric',
            'soil_ph' => 'nullable|numeric',
        ]);

        $validated['soil_ph'] = $validated['soil_ph'] ?? null;

        DB::beginTransaction();
        try {
            // Step 1: Find or update the reporting device (SENSOR type) cleanly
            $device = Device::updateOrCreate(
                ['name' => $validated['name']],
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'device_type' => 'SENSOR',
                ]
            );

            // Step 2 & 3: Store temperature and humidity data
            Temperature::create([
                'device_id' => $device->id,
                'value_temp' => $validated['temperature'],
            ]);

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
            $kategori_detail = $anfisResult['kategori'] ?? null;

            // Step 7: Get or create ACTUATOR device for control actions
            $actuatorDevice = $this->getOrCreateActuatorDevice();

            // Step 8: Create control action record
            $sensorDeviceIds = Device::where('device_type', 'SENSOR')->pluck('id')->toArray();

            ControlActions::create([
                'device_id' => $actuatorDevice->id,
                'sensor_device_ids' => $sensorDeviceIds,
                'pump_status' => $pump_status,
                'mist_duration' => $mist_duration,
                'method' => $method,
                'aggregation_type' => 'AVERAGE',
            ]);

            DB::commit();

            // Store API decision in cache for frontend read
            Cache::put('latest_anfis_decision', [
                'skor_anfis' => $score,
                'pump_status' => $pump_status,
                'mist_duration' => $mist_duration,
                'method' => $method,
                'kategori_detail' => $kategori_detail,
                'aggregated_data' => $aggregatedData,
                'sensor_count' => count($sensorDeviceIds),
                'timestamp' => now()->toDateTimeString(),
            ], now()->addHours(24));

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
                    'kategori_detail' => $kategori_detail
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
    public function show($deviceId): JsonResponse
    {
        try {
            $device = Device::with([
                'temperatures' => fn($q) => $q->latest()->limit(10),
                'humidities' => fn($q) => $q->latest()->limit(10),
                'soilPHs' => fn($q) => $q->latest()->limit(10),
            ])->findOrFail($deviceId);

            return response()->json(['success' => true, 'data' => $device], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan'], 404);
        }
    }

    /**
     * Get all devices
     */
    public function index(): JsonResponse
    {
        try {
            $devices = Device::with([
                'temperatures' => fn($q) => $q->latest()->first(),
                'humidities' => fn($q) => $q->latest()->first(),
                'soilPHs' => fn($q) => $q->latest()->first(),
            ])->get();

            return response()->json(['success' => true, 'data' => $devices], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data devices'], 500);
        }
    }

    /**
     * Get temperature data with date range filter
     */
    public function getTemperatureData(Request $request): JsonResponse
    {
        return $this->getHistoricalSensorData($request, Temperature::class, 'value_temp', 'temperatureValues');
    }

    /**
     * Get humidity data with date range filter
     */
    public function getHumidityData(Request $request): JsonResponse
    {
        return $this->getHistoricalSensorData($request, Humidity::class, 'value_humidity', 'humidityValues');
    }

    /**
     * Get soil pH data with date range filter
     */
    public function getSoilPHData(Request $request): JsonResponse
    {
        return $this->getHistoricalSensorData($request, SoilPH::class, 'value_ph', 'soilPHValues');
    }

    /**
     * Get latest ANFIS prediction for dashboard with aggregated sensor data
     */
    public function getLatestPrediction(Request $request): JsonResponse
    {
        try {
            $latestAction = ControlActions::with(['device'])->latest('created_at')->first();

            if (!$latestAction) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Belum ada prediksi ANFIS',
                ], 200);
            }

            $cachedDecision = Cache::get('latest_anfis_decision', []);
            $kategoriDetail = $cachedDecision['kategori_detail'] ?? $cachedDecision['kategori'] ?? null;

            $aggregatedData = $this->aggregateSensorData();
            $sensorDeviceCount = Device::where('device_type', 'SENSOR')->count();

            // Determine environment status
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

            // Map UI badges dynamically using centralized helper
            $tempUI = $this->resolveFuzzyUiState($kategoriDetail, 'temperature');
            $humUI  = $this->resolveFuzzyUiState($kategoriDetail, 'humidity');
            $phUI   = $this->resolveFuzzyUiState($kategoriDetail, 'soil_ph');

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'status_color' => $statusColor,
                    'status_message' => $statusMessage,
                    'skor_anfis' => $cachedDecision['skor_anfis'] ?? null,
                    'pump_status' => $latestAction->pump_status,
                    'mist_duration' => $latestAction->mist_duration,
                    'method' => $latestAction->method,
                    'aggregated_data' => [
                        'temperature' => round($aggregatedData['temperature'], 2),
                        'temperature_status' => $tempUI['status'],
                        'temperature_badge' => $tempUI['badge'],
                        'humidity' => round($aggregatedData['humidity'], 2),
                        'humidity_status' => $humUI['status'],
                        'humidity_badge' => $humUI['badge'],
                        'soil_ph' => round($aggregatedData['soil_ph'], 2),
                        'soil_ph_status' => $phUI['status'],
                        'soil_ph_badge' => $phUI['badge'],
                        'sensor_count' => $sensorDeviceCount,
                    ],
                    'fuzzy_details' => $kategoriDetail,
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

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Aggregate latest sensor readings from all SENSOR devices
     */
    private function aggregateSensorData(): ?array
    {
        $sensorDevices = Device::where('device_type', 'SENSOR')
            ->with([
                'temperatures' => fn($q) => $q->latest('created_at')->limit(1),
                'humidities' => fn($q) => $q->latest('created_at')->limit(1),
                'soilPHs' => fn($q) => $q->latest('created_at')->limit(1),
            ])
            ->get();

        if ($sensorDevices->isEmpty()) {
            return null;
        }

        $tempVals = [];
        $humVals  = [];
        $phVals   = [];

        foreach ($sensorDevices as $device) {
            if ($device->temperatures->isNotEmpty()) $tempVals[] = $device->temperatures->first()->value_temp;
            if ($device->humidities->isNotEmpty())   $humVals[]  = $device->humidities->first()->value_humidity;
            if ($device->soilPHs->isNotEmpty())      $phVals[]   = $device->soilPHs->first()->value_ph;
        }

        return [
            'temperature'  => !empty($tempVals) ? array_sum($tempVals) / count($tempVals) : 0.0,
            'humidity'     => !empty($humVals) ? array_sum($humVals) / count($humVals) : 0.0,
            'soil_ph'      => !empty($phVals) ? array_sum($phVals) / count($phVals) : 7.0,
            'sensor_count' => $sensorDevices->count(),
        ];
    }

    /**
     * Call ANFIS prediction API (Internal VPS Port 5001)
     */
    private function callAnfisPredict(array $aggregatedData): array
    {
        $pump_status = false;
        $mist_duration = 0.0;
        $method = 'ANFIS';
        $score = 0.0;
        $kategori_detail = null;

        try {
            $response = Http::timeout(5)
                ->asJson()
                ->acceptJson()
                ->post('http://127.0.0.1:5001/predict', [
                    'temperature' => $aggregatedData['temperature'],
                    'humidity'    => $aggregatedData['humidity'],
                    'soil_ph'     => $aggregatedData['soil_ph'],
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $score = $result['skor_anfis'] ?? 0.0;
                $pump_status = $result['pump_status'] ?? false;
                $mist_duration = $result['mist_duration'] ?? 0.0;
                $kategori_detail = $result['kategori'] ?? null;
                $method = 'ANFIS';
            } else {
                $method = 'ERROR_FALLBACK';
            }
        } catch (\Exception $e) {
            $method = 'CONNECTION_FAILED';
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
            'kategori' => $kategori_detail
        ];
    }

    /**
     * Get or create ACTUATOR master device
     */
    private function getOrCreateActuatorDevice(): Device
    {
        return Device::firstOrCreate(
            ['name' => 'System Actuator - Pump Control', 'device_type' => 'ACTUATOR'],
            ['latitude' => 0, 'longitude' => 0, 'device_type' => 'ACTUATOR']
        );
    }

    /**
     * Reusable engine for historical sensor data endpoints
     */
    private function getHistoricalSensorData(Request $request, string $modelClass, string $valueColumn, string $responseKey): JsonResponse
    {
        try {
            $validated = $request->validate([
                'start_date' => ['sometimes', 'date_format:Y-m-d'],
                'end_date'   => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                'device_id'  => ['sometimes', 'string', 'max:100'],
            ]);

            $query = $modelClass::query();

            if (!empty($validated['start_date']) && !empty($validated['end_date'])) {
                $query->whereBetween('created_at', [
                    $validated['start_date'] . ' 00:00:00',
                    $validated['end_date']   . ' 23:59:59',
                ]);
            }

            if (!empty($validated['device_id'])) {
                $query->where('device_id', $validated['device_id']);
            }

            $records = $query->orderBy('created_at')->get();

            $groupedByDate = $records->groupBy(fn($item) => $item->created_at->format('Y-m-d'));

            $values = [];
            $dates  = [];

            foreach ($groupedByDate as $date => $items) {
                $values[] = round($items->avg($valueColumn), 2);
                $dates[]  = Carbon::createFromFormat('Y-m-d', $date)->format('d-m-Y');
            }

            return response()->json([
                'success' => true,
                $responseKey => $values,
                'dates' => $dates,
                'average' => count($values) > 0 ? round(collect($values)->avg(), 2) : 0,
                'total_records' => count($records),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat data sensor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve fuzzy UI labels and badges from ANFIS memberships
     */
    private function resolveFuzzyUiState($kategoriDetail, string $metric): array
    {
        $fallback = ['dominant_category' => null, 'status' => 'Normal', 'badge' => 'bg-success'];

        if (!is_array($kategoriDetail) || empty($kategoriDetail)) {
            return $fallback;
        }

        $metricDetail = $kategoriDetail[$metric] ?? $kategoriDetail[$metric . '_detail'] ?? null;

        if (is_string($metricDetail) && $metricDetail !== '') {
            $dominantCategory = $metricDetail;
        } elseif (is_array($metricDetail) && !empty($metricDetail)) {
            $scores = array_filter($metricDetail, static fn($value) => is_numeric($value));
            if (empty($scores)) return $fallback;

            $dominantCategory = array_keys($scores, max($scores), true)[0] ?? null;
        } else {
            return $fallback;
        }

        if ($dominantCategory === null) return $fallback;

        return [
            'dominant_category' => $dominantCategory,
            'status' => match ($metric) {
                'temperature' => match ($dominantCategory) {
                    'rendah' => 'Rendah',
                    'sedang' => 'Sedang',
                    'tinggi' => 'Tinggi',
                    default  => ucfirst($dominantCategory),
                },
                'humidity' => match ($dominantCategory) {
                    'rendah' => 'Rendah',
                    'sedang' => 'Sedang',
                    'tinggi' => 'Tinggi',
                    default  => ucfirst($dominantCategory),
                },
                'soil_ph' => match ($dominantCategory) {
                    'rendah' => 'Rendah',
                    'sedang' => 'Sedang',
                    'tinggi' => 'Tinggi',
                    default  => ucfirst($dominantCategory),
                },
                default => 'Normal',
            },
            'badge' => match ($metric) {
                'temperature' => match ($dominantCategory) {
                    'rendah' => 'bg-success',
                    'sedang' => 'bg-info',
                    'tinggi' => 'bg-danger',
                    default  => 'bg-success',
                },
                'humidity' => match ($dominantCategory) {
                    'rendah' => 'bg-danger',
                    'sedang' => 'bg-info',
                    'tinggi' => 'bg-success',
                    default  => 'bg-success',
                },
                'soil_ph' => match ($dominantCategory) {
                    'rendah' => 'bg-danger',
                    'sedang' => 'bg-info',
                    'tinggi' => 'bg-warning',
                    default  => 'bg-success',
                },
                default => 'bg-success',
            },
        ];
    }
}
