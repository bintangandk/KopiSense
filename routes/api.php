<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Api\GeminiRecommendationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Location API routes
Route::get('/provinces', [LocationController::class, 'getProvinces']);
Route::get('/provinces/{provinceId}/cities', [LocationController::class, 'getCitiesByProvince']);

// IoT Sensor Data API routes
Route::post('/sensor-data', [SensorDataController::class, 'store']);
Route::get('/devices', [SensorDataController::class, 'index']);
Route::get('/devices/{deviceId}', [SensorDataController::class, 'show']);

// Device API routes for frontend
Route::get('/devices-list', [DeviceController::class, 'getDevices']);
Route::get('/device-data/{deviceId}', [DeviceController::class, 'getDeviceData']);

// Manual pump control - emergency override
Route::post('/device/{deviceId}/manual-pump-control', [DeviceController::class, 'manualPumpControl']);

// Latest analysis from all devices
Route::get('/latest-analysis', [DeviceController::class, 'getLatestAnalysis']);

// Latest pH recommendation
Route::get('/ph-recommendation', [DeviceController::class, 'getLatestPhRecommendation']);

// Sensor Data Filter Routes
Route::get('/temperatures', [SensorDataController::class, 'getTemperatureData']);
Route::get('/humidities', [SensorDataController::class, 'getHumidityData']);
Route::get('/soil-ph', [SensorDataController::class, 'getSoilPHData']);

// ANFIS Prediction Routes
Route::get('/latest-prediction', [SensorDataController::class, 'getLatestPrediction']);

// Aggregated Gemini Recommendation (untuk dashboard - auto load, no rate limit)
Route::get('/ph-recommendation-aggregated', [GeminiRecommendationController::class, 'getAggregatedRecommendation']);

// On-demand Gemini Recommendation (user clicks button - rate limited 1/hour, 24h cache, optimize tokens)
Route::post('/ph-recommendation-on-demand', [GeminiRecommendationController::class, 'getRecommendationOnDemand']);
