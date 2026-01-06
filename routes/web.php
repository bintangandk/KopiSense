<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/', function () {
    return view(('auth.login.index'));
})->name('login');

Route::get('/register', function () {
    return view('auth.register.index');
})->name('register');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password.index');
})->name('forgot-password');


// Dashboard
Route::get('/dashboard', function () {
    return view('pages.dashboard.index');
})->name('dashboard');


// Profile
Route::get('/profile', function () {
    return view('pages.profile.index');
})->name('profile');


// Data Users
Route::get('/data-user', function () {
    return view('pages.dataUser.index');
})->name('data-user');

Route::get('/data-user/create', function () {
    return view('pages.dataUser.create.index');
})->name('data-user.create');


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
