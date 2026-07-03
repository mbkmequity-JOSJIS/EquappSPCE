<?php


use App\Http\Controllers\Api\BmkgProxyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;


Route::get('/test-firebase', function () {
    $factory = (new Factory)
        ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
        ->withDatabaseUri('https://equapp-5718f-default-rtdb.firebaseio.com');

    $database = $factory->createDatabase();

    $data = $database->getReference('KualitasAir')->getValue();

    return response()->json($data);
});

Route::get('/', function () {
    return view('index');
})->name('welcome');

Route::get('/modul/', [DashboardController::class, 'index'])->name('home');

Route::get('/modul/lokasi', [LocationController::class, 'index'])->name('lokasi');
Route::get('/modul/perangkat', [LocationController::class, 'index'])->name('perangkat');
Route::get('/modul/lokasi/{id}', [LocationController::class, 'show'])->name('location.detail');
Route::get('/modul/api/sensor-data', [LocationController::class, 'getSensorData'])->name('api.sensor.data');
Route::get('/modul/api/location/{id}', [LocationController::class, 'getLocationDetail'])->name('api.location.detail');

Route::get('/modul/api/bmkg/forecast', BmkgProxyController::class)->name('api.bmkg.forecast');
