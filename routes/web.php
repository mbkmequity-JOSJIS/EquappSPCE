<?php


use App\Http\Controllers\Api\BmkgProxyController;
use App\Http\Controllers\Api\IndicatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ModulController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;


Route::get('/test-firebase', function () {
    $factory = (new Factory)
        ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
        ->withDatabaseUri('https://equapp-5718f-default-rtdb.firebaseio.com');

    $database = $factory->createDatabase();

    $data = $database->getReference('water_quality')->getValue();

    return response()->json($data);
});

Route::get('/', function () {
    return view('index');
})->name('welcome');

// Auth Routes
Route::get('/login', [AuthController::class, 'index'])->name('login.index');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');


// Admin Routes
Route::middleware('auth')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('index');
        Route::post('/update-firebase-status', [AdminController::class, 'updateFirebaseStatus'])->name('update.firebase.status');
        Route::post('/save-firebase-device', [AdminController::class, 'saveFirebaseDevice'])->name('save.firebase.device');
        Route::post('/delete-firebase-device', [AdminController::class, 'deleteFirebaseDevice'])->name('delete.firebase.device');
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});




Route::middleware(['guest'])->group(function () {
    // Device Routes
    Route::get('/modul/devices', [DeviceController::class, 'index'])->name('devices');
    Route::get('/modul/history/{device}/{device_code}', [DeviceController::class, 'getHistory'])->name('history');
    Route::get('/modul/devices/{device}', [DeviceController::class, 'devices'])->name('device.list');
    Route::get('/modul/devices/{device}/{id}', [DeviceController::class, 'show'])->name('device.detail');
    Route::get('/modul/device/{device}/{device_code}/history', [DeviceController::class, 'getHistory'])->name('device.history');
    Route::get('/modul/api/devices/{device}/{id}', [DeviceController::class, 'getDetail'])->name('api.device.detail');
    Route::post('/modul/api/devices/{device}/{id}/calibration', [DeviceController::class, 'storeCalibration'])->name('api.device.calibration.store');

    // locations Routes
    Route::get('/modul/locations', [LocationController::class, 'index'])->name('locations');
    Route::get('/modul/locations/{id}', [LocationController::class, 'show'])->name('location.detail');

    // dashboard
    Route::get('/modul/{module}', [DashboardController::class, 'dashboard'])->name('module');

    // home
    Route::get('/modul/', [DashboardController::class, 'index'])->name('home');

    // routes/web.php
    Route::get('/test-groq', function () {
        $groq = app(App\Services\GroqAIService::class);

        $mockDevice = ['device_name' => 'Test Tambak', 'type' => 'AQUAVISKA', 'condition_score' => 65];
        $mockMonitoring = [
            'sensors' => [
                ['label' => 'pH', 'value' => 8.5, 'unit' => '', 'status' => 'waspada'],
                ['label' => 'Dissolved Oxygen', 'value' => 3.2, 'unit' => 'mg/L', 'status' => 'bahaya'],
                ['label' => 'Temperature', 'value' => 29, 'unit' => '°C', 'status' => 'normal'],
            ]
        ];

        $result = $groq->generateRecommendations($mockDevice, $mockMonitoring);

        return response()->json($result);
    });
});





