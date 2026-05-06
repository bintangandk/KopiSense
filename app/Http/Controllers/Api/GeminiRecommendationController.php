<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SoilPH;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiRecommendationController extends Controller
{
    /**
     * Get aggregated pH recommendation for dashboard
     * Calculates average pH from all SENSOR devices and provides single AI recommendation
     */
    public function getAggregatedRecommendation(Request $request)
    {
        try {
            Log::info('🤖 [GeminiRecommendation] Aggregated recommendation request started');

            // Get aggregated pH data from all SENSOR devices (same logic as ANFIS)
            $aggregatedPhData = $this->aggregateSoilPHData();

            if (!$aggregatedPhData) {
                Log::warning('⚠️ [GeminiRecommendation] No pH data available from any sensor');
                return response()->json([
                    'success' => false,
                    'message' => 'Data pH belum tersedia dari sensor manapun.'
                ], 404);
            }

            $currentPh = $aggregatedPhData['average_ph'];
            $sensorCount = $aggregatedPhData['sensor_count'];

            Log::info('📊 [GeminiRecommendation] Aggregated data', [
                'average_ph' => $currentPh,
                'sensor_count' => $sensorCount,
                'ph_values' => $aggregatedPhData['ph_values']
            ]);

            // Check if pH is within safe range (5.5 - 6.5 untuk kopi robusta)
            if ($currentPh >= 5.5 && $currentPh <= 6.5) {
                Log::info('✅ [GeminiRecommendation] pH is within safe range - returning static recommendation');
                return response()->json([
                    'success' => true,
                    'status' => 'Aman',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => 'Kadar pH tanah saat ini sangat ideal untuk bibit kopi Robusta (rata-rata: ' . round($currentPh, 2) . '). Lanjutkan perawatan rutin dan monitor secara berkala.',
                    'recommendation_type' => 'static'
                ]);
            }

            // If pH is outside safe range, call Gemini API
            Log::info('⚠️ [GeminiRecommendation] pH is abnormal - checking cache first');

            // Create cache key based on pH range (to reuse recommendations)
            $phRange = $this->getPhRange($currentPh);
            $cacheKey = 'gemini_recommendation_' . $phRange;
            $circuitBreakerKey = 'gemini_circuit_breaker_' . $phRange;

            // Check if circuit breaker is active (due to recent API failures or rate limits)
            if (\Illuminate\Support\Facades\Cache::has($circuitBreakerKey)) {
                Log::warning('🛑 [GeminiRecommendation] Circuit Breaker aktif! Menghindari spam API ke Gemini.');
                return response()->json([
                    'success' => true,
                    'status' => 'Peringatan',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => \Illuminate\Support\Facades\Cache::get($circuitBreakerKey),
                    'recommendation_type' => 'fallback_cooldown',
                    'message' => 'API sedang dalam masa cooldown. Menggunakan rekomendasi default.'
                ]);
            }

            // Check if cached recommendation exists (valid for 6 hours)
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                Log::info('💾 [GeminiRecommendation] Using cached recommendation', [
                    'cache_key' => $cacheKey
                ]);

                $cachedRecommendation = \Illuminate\Support\Facades\Cache::get($cacheKey);

                return response()->json([
                    'success' => true,
                    'status' => 'Peringatan',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => $cachedRecommendation,
                    'recommendation_type' => 'ai_cached'
                ]);
            }

            // No cache, try calling Gemini API
            Log::info('📤 [GeminiRecommendation] No cached recommendation - calling Gemini API');

            $prompt = "Kamu adalah ahli agronomi kopi robusta. Data sensor IoT greenhouse saya:
                        - Jumlah sensor: {$sensorCount} device
                        - pH tanah rata-rata: {$currentPh} (rentang optimal bibit robusta: 5.5-6.5, ref: Puslitkoka)

                        Berikan rekomendasi koreksi pH dalam format teks biasa bernomor (tanpa markdown):

                        1. STATUS: kondisi pH saat ini dan dampaknya pada bibit robusta
                        2. TINDAKAN 1: nama bahan + dosis + cara aplikasi + waktu tunggu
                        3. TINDAKAN 2: nama bahan + dosis + cara aplikasi + waktu tunggu  
                        4. PERINGATAN: 1 risiko spesifik di lingkungan greenhouse
                        5. TARGET: pH yang harus dicapai dan frekuensi cek sensor

                        Jawab maksimal 200 kata, bahasa Indonesia, teknis namun jelas.";

            $geminiResult = $this->callGeminiApi($prompt);

            Log::info('📤 [GeminiRecommendation] Gemini API result', $geminiResult);

            if ($geminiResult['success']) {
                Log::info('✅ [GeminiRecommendation] AI recommendation retrieved successfully');

                // Cache the recommendation for 6 hours
                \Illuminate\Support\Facades\Cache::put($cacheKey, $geminiResult['recommendation'], now()->addHours(6));

                return response()->json([
                    'success' => true,
                    'status' => 'Peringatan',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => $geminiResult['recommendation'],
                    'recommendation_type' => 'ai'
                ]);
            }

            // If API call failed, activate circuit breaker for 30 minutes to prevent repeated failures
            Log::error('❌ [GeminiRecommendation] Gemini API failed, activating circuit breaker.');
            $fallbackRecommendation = $this->getFallbackRecommendation($currentPh);

            // Cache fallback recommendation for 10 minutes to prevent repeated API calls during downtime
            \Illuminate\Support\Facades\Cache::put(
                $cacheKey,
                $fallbackRecommendation,
                now()->addMinutes(60)
            );

            return response()->json([
                'success' => true,
                'status' => 'Peringatan',
                'ph' => round($currentPh, 2),
                'sensor_count' => $sensorCount,
                'recommendation' => $fallbackRecommendation,
                'recommendation_type' => 'fallback',
                'message' => 'Menggunakan rekomendasi default (API tidak tersedia)'
            ]);
        } catch (\Exception $e) {
            Log::error('🚨 [GeminiRecommendation] Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aggregate soil pH data from all SENSOR devices
     * Returns average pH and count of devices contributing data
     */
    private function aggregateSoilPHData()
    {
        try {
            // Get all SENSOR devices with their latest pH reading
            $sensorDevices = Device::where('device_type', 'SENSOR')
                ->with([
                    'soilPHs' => function ($query) {
                        $query->latest('created_at')->limit(1);
                    }
                ])
                ->get();

            Log::debug('📱 [aggregateSoilPHData] SENSOR devices found', [
                'count' => $sensorDevices->count()
            ]);

            if ($sensorDevices->isEmpty()) {
                Log::warning('⚠️ [aggregateSoilPHData] No SENSOR devices found');
                return null;
            }

            // Collect all pH values
            $phValues = [];
            foreach ($sensorDevices as $device) {
                if ($device->soilPHs->count() > 0) {
                    $phValue = $device->soilPHs->first()->value_ph;
                    $phValues[] = $phValue;
                    Log::debug('📊 [aggregateSoilPHData] Device pH', [
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'ph_value' => $phValue
                    ]);
                }
            }

            if (empty($phValues)) {
                Log::warning('⚠️ [aggregateSoilPHData] No pH values collected from any device');
                return null;
            }

            // Calculate average
            $averagePh = array_sum($phValues) / count($phValues);

            Log::info('✅ [aggregateSoilPHData] Aggregation complete', [
                'ph_values_count' => count($phValues),
                'average_ph' => $averagePh,
                'ph_values' => $phValues
            ]);

            return [
                'average_ph' => $averagePh,
                'sensor_count' => count($phValues),
                'ph_values' => $phValues,
            ];
        } catch (\Exception $e) {
            Log::error('🚨 [aggregateSoilPHData] Exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Call Gemini API to get recommendation with RETRY LOGIC for 429 errors
     */
    private function callGeminiApi($prompt)
    {
        $apiKey = env('GEMINI_API_KEY');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::timeout(15)
                ->withOptions(['http_errors' => false])
                ->post($url, $payload);

            $retryCount = 0;
            while ($response->status() === 429 && $retryCount < 2) {
                sleep(2);
                $response = Http::timeout(15)->withOptions(['http_errors' => false])->post($url, $payload);
                $retryCount++;
            }

            if ($response->status() === 429) {
                Log::warning('⚠️ [Gemini API] Quota benar-benar habis (429)');
                return [
                    'success' => false,
                    'message' => 'Rate limit exceeded (429 Too Many Requests)',
                    'quota_status' => 'EXHAUSTED'
                ];
            }

            if ($response->successful()) {
                $geminiResult = $response->json();
                return [
                    'success' => true,
                    'recommendation' => $geminiResult['candidates'][0]['content']['parts'][0]['text'] ?? 'Format response AI tidak sesuai.'
                ];
            }

            Log::error('🤖 [Gemini Error Raw]', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'API call failed with status: ' . $response->status()
            ];
        } catch (\Throwable $e) {
            Log::error('🚨 [Gemini Exception]', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception during API call: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get pH range for cache key
     * Maps pH value to range (e.g., 4.5 -> "4-5", 7.8 -> "7-8")
     */
    // private function getPhRange($ph)
    // {
    //     $floor = floor($ph);
    //     return "{$floor}-" . ($floor + 1);
    // }

    private function getPhRange($ph)
    {
        // Bulatkan ke 1 angka di belakang koma (misal: 4.74 menjadi 4.7)
        $roundedPh = round($ph, 1);

        // Ubah titik menjadi underscore untuk format key cache yang aman (misal: "4.7" menjadi "4_7")
        return str_replace('.', '_', (string) $roundedPh);
    }

    /**
     * Get fallback recommendation when API fails
     */
    private function getFallbackRecommendation($currentPh)
    {
        if ($currentPh < 5.5) {
            return "pH tanah terlalu asam ({$currentPh}). Langkah yang disarankan:\n1. Tambahkan kapur pertanian (pupuk kapur) secara bertahap\n2. Tingkatkan drainase tanah untuk mengurangi asam\n3. Monitor pH setiap minggu sampai mencapai rentang 5.5-6.5";
        } else {
            return "pH tanah terlalu basa ({$currentPh}). Langkah yang disarankan:\n1. Tambahkan sulfur atau belerang untuk menurunkan pH\n2. Gunakan pupuk yang bersifat asam seperti amonium sulfat\n3. Monitor pH setiap minggu sampai mencapai rentang 5.5-6.5";
        }
    }

    /**
     * On-demand Gemini recommendation (user clicks button)
     * Rate limited: Max 1 call per hour per pH range
     * Cache: 24 hours
     * This optimizes token usage by preventing repeated API calls
     */
    public function getRecommendationOnDemand(Request $request)
    {
        try {
            Log::info('🎯 [OnDemandRecommendation] User request started');

            // Get aggregated pH data
            $aggregatedPhData = $this->aggregateSoilPHData();

            if (!$aggregatedPhData) {
                Log::warning('⚠️ [OnDemandRecommendation] No pH data available');
                return response()->json([
                    'success' => false,
                    'message' => 'Data pH tidak tersedia dari sensor manapun.'
                ], 404);
            }

            $currentPh = $aggregatedPhData['average_ph'];
            $sensorCount = $aggregatedPhData['sensor_count'];

            Log::info('📊 [OnDemandRecommendation] Current pH', [
                'ph' => $currentPh,
                'sensor_count' => $sensorCount
            ]);

            // ========== STEP 1: Check if pH is normal ==========
            if ($currentPh >= 5.5 && $currentPh <= 6.5) {
                Log::info('✅ [OnDemandRecommendation] pH is within safe range');
                return response()->json([
                    'success' => true,
                    'status' => 'Aman',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => 'Kadar pH tanah saat ini sangat ideal untuk bibit kopi Robusta (rata-rata: ' . round($currentPh, 2) . '). Lanjutkan perawatan rutin dan monitor secara berkala.',
                    'recommendation_type' => 'static',
                    'api_called' => false,
                    'cache_status' => 'N/A'
                ]);
            }

            // ========== STEP 2: Create cache & rate limit keys ==========
            $phRange = $this->getPhRange($currentPh);
            $cacheKey = "gemini_ondemand_{$phRange}";
            $rateLimitKey = "gemini_ratelimit_{$phRange}";

            Log::debug('🔑 [OnDemandRecommendation] Keys generated', [
                'phRange' => $phRange,
                'cacheKey' => $cacheKey,
                'rateLimitKey' => $rateLimitKey
            ]);

            // ========== STEP 3: Check cache (7 days for free tier optimization) ==========
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);
                Log::info('💾 [OnDemandRecommendation] Returning cached recommendation (7 days cache)', [
                    'cache_key' => $cacheKey,
                    'cache_age' => 'within 7 days',
                    'reason' => 'Free tier quota optimization'
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'Peringatan',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => $cachedData,
                    'recommendation_type' => 'ai_cached_7d',
                    'api_called' => false,
                    'cache_status' => 'HIT (7 days cache)',
                    'message' => 'Menggunakan rekomendasi dari cache (valid 7 hari)'
                ]);
            }

            // ========== STEP 4: Check rate limit (max 1 per 2 minutes) ==========
            if (\Illuminate\Support\Facades\Cache::has($rateLimitKey)) {
                $rateLimitData = \Illuminate\Support\Facades\Cache::get($rateLimitKey);
                $remainingSeconds = $rateLimitData['expire_at'] - now()->timestamp;
                $remainingMinutes = ceil($remainingSeconds / 60);

                Log::info('⏱️ [OnDemandRecommendation] Rate limited - max 1 call per 2 minutes reached', [
                    'rate_limit_key' => $rateLimitKey,
                    'next_call_available_in_minutes' => $remainingMinutes
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'Peringatan',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => $rateLimitData['fallback'],
                    'recommendation_type' => 'fallback_rate_limited',
                    'api_called' => false,
                    'cache_status' => 'RATE_LIMITED (1 call/2min)',
                    'message' => "Limit tercapai: max 1 analisis per 2 menit. Coba lagi dalam {$remainingMinutes} menit.",
                    'next_available_in_seconds' => ($rateLimitData['expire_at'] - now()->timestamp)
                ]);
            }

            // ========== STEP 5: Call Gemini API (allowed) ==========
            Log::info('🚀 [OnDemandRecommendation] Calling Gemini API - within rate limit');

            $prompt = "Saya adalah petani bibit kopi robusta di greenhouse dengan sistem IoT. Saat ini sensor saya (dari {$sensorCount} device) mendeteksi pH tanah rata-rata berada di angka {$currentPh}. Angka ini di luar batas normal (5.5-6.5). Sebagai ahli pertanian, berikan 2-3 langkah praktis, murah, dan singkat untuk menormalkan kembali pH tanah tersebut. Berikan dalam format teks biasa, tidak perlu markdown kompleks. Singkat dan langsung ke solusi.";

            $geminiResult = $this->callGeminiApi($prompt);

            // ========== CHECK QUOTA STATUS ==========
            if (isset($geminiResult['quota_status']) && $geminiResult['quota_status'] === 'EXHAUSTED') {
                Log::warning('⚠️ [OnDemandRecommendation] Gemini quota completely exhausted - free tier limit reached', [
                    'retry_recommended' => false,
                    'action' => 'Using fallback immediately'
                ]);

                $fallbackRecommendation = $this->getFallbackRecommendation($currentPh);

                // Store fallback with 24-hour cache for quota-exhausted case
                \Illuminate\Support\Facades\Cache::put(
                    $rateLimitKey,
                    [
                        'expire_at' => now()->addHours(24)->timestamp,
                        'fallback' => $fallbackRecommendation
                    ],
                    now()->addHours(24)
                );

                Log::info('💾 [OnDemandRecommendation] Fallback cached for 24 hours (quota exhausted)');

                return response()->json([
                    'success' => true,
                    'status' => 'Peringatan',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => $fallbackRecommendation,
                    'recommendation_type' => 'fallback_quota_exhausted',
                    'api_called' => true,
                    'cache_status' => 'QUOTA_EXHAUSTED',
                    'message' => '❌ Quota Gemini Free Tier habis untuk hari ini. Menampilkan rekomendasi fallback. Coba lagi besok atau upgrade ke plan berbayar.'
                ]);
            }

            if ($geminiResult['success']) {
                Log::info('✅ [OnDemandRecommendation] Gemini API call successful');

                $recommendation = $geminiResult['recommendation'];

                // Store in 7-day cache (free tier quota optimization)
                \Illuminate\Support\Facades\Cache::put(
                    $cacheKey,
                    $recommendation,
                    now()->addDays(7)
                );

                // Store rate limit key (2 minutes)
                \Illuminate\Support\Facades\Cache::put(
                    $rateLimitKey,
                    [
                        'expire_at' => now()->addMinutes(2)->timestamp,
                        'fallback' => $recommendation
                    ],
                    now()->addMinutes(2)
                );

                Log::info('💾 [OnDemandRecommendation] Recommendation cached for 7 days (free tier optimization)');

                return response()->json([
                    'success' => true,
                    'status' => 'Peringatan',
                    'ph' => round($currentPh, 2),
                    'sensor_count' => $sensorCount,
                    'recommendation' => $recommendation,
                    'recommendation_type' => 'ai_fresh',
                    'api_called' => true,
                    'cache_status' => 'MISS - Fresh API call',
                    'message' => 'Rekomendasi AI segar dari Gemini (disimpan di cache 7 hari)',
                    'cache_duration_days' => 7
                ]);
            }

            // ========== STEP 6: API failed - use fallback ==========
            Log::error('❌ [OnDemandRecommendation] Gemini API failed', [
                'error' => $geminiResult['message'] ?? 'Unknown error'
            ]);

            $fallbackRecommendation = $this->getFallbackRecommendation($currentPh);

            // Store fallback as rate limit to prevent repeated API calls
            \Illuminate\Support\Facades\Cache::put(
                $rateLimitKey,
                [
                    'expire_at' => now()->addMinutes(2)->timestamp,
                    'fallback' => $fallbackRecommendation
                ],
                now()->addMinutes(2)
            );

            return response()->json([
                'success' => true,
                'status' => 'Peringatan',
                'ph' => round($currentPh, 2),
                'sensor_count' => $sensorCount,
                'recommendation' => $fallbackRecommendation,
                'recommendation_type' => 'fallback_api_error',
                'api_called' => true,
                'cache_status' => 'API_ERROR - Using fallback',
                'message' => 'API Gemini tidak tersedia, menggunakan rekomendasi default'
            ]);
        } catch (\Exception $e) {
            Log::error('🚨 [OnDemandRecommendation] Exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
