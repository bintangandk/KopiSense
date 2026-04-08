<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\DeviceController;

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

// Sensor Data Filter Routes
Route::get('/temperatures', [SensorDataController::class, 'getTemperatureData']);
Route::get('/humidities', [SensorDataController::class, 'getHumidityData']);
Route::get('/soil-ph', [SensorDataController::class, 'getSoilPHData']);

// ANFIS Prediction Routes
Route::get('/latest-prediction', [SensorDataController::class, 'getLatestPrediction']);
