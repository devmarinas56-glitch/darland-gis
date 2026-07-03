<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LandRecordsController;

Route::get('/', function () { return redirect('/login'); });

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');
    Route::get('/map-viewer', fn() => view('map.viewer'))->name('map.viewer');
    Route::get('/land-records', [LandRecordsController::class, 'index'])->name('land-records.index');
    Route::post('/land-records', [LandRecordsController::class, 'store'])->name('land-records.store');
    Route::put('/land-records/{landLot}', [LandRecordsController::class, 'update'])->name('land-records.update');
    Route::delete('/land-records/{landLot}', [LandRecordsController::class, 'destroy'])->name('land-records.destroy');    Route::get('/api/lots', [LandRecordsController::class, 'apiLots'])->name('api.lots');
    Route::post('/api/check-overlap', [LandRecordsController::class, 'checkOverlap'])->name('api.check-overlap');
});
