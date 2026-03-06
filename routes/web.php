<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//auth routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', function () {
    return view('auth.register.index');
})->name('register');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password.index');
})->name('forgot-password');


// Dashboard - Protected
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard.index');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Data Users - Only Admin can access
    Route::middleware(['not.employee'])->group(function () {
        Route::get('/data-user', [UserController::class, 'index'])->name('data-user');
        Route::get('/data-user/create', [UserController::class, 'create'])->name('data-user.create');
        Route::post('/data-user/store', [UserController::class, 'store'])->name('data-user.store');
        Route::get('/data-user/{id}', [UserController::class, 'show'])->name('data-user.show');
        Route::delete('/data-user/{id}', [UserController::class, 'destroy'])->name('data-user.destroy');
        Route::get('/data-user/{id}/edit', [UserController::class, 'edit'])->name('data-user.edit');
        Route::put('/data-user/{id}', [UserController::class, 'update'])->name('data-user.update');
    });

    // Censor Data
    Route::get('/censor-data/temperature', function () {
        return view('pages.censorData.temperature.index');
    })->name('censor-data.temperature');

    Route::get('/censor-data/humidity', function () {
        return view('pages.censorData.humidity.index');
    })->name('censor-data.humidity');

    Route::get('/censor-data/soilPh', function () {
        return view('pages.censorData.soilPh.index');
    })->name('censor-data.soilPh');

    // Control Pump
    Route::get('/environmental-control', function () {
        return view('pages.controlEnviron.index');
    })->name('environmental-control');
});
