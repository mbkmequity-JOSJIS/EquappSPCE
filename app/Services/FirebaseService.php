<?php

namespace App\Services;

use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\Log;

use Kreait\Firebase\Contract\Auth;
    
class FirebaseService
{
    protected Database $database;
    protected Auth $auth;


    public function __construct()
    {

        $this->database = (new \Kreait\Firebase\Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
            ->withDatabaseUri('https://equapp-5718f-default-rtdb.firebaseio.com')
            ->createDatabase();

        $this->auth = (new \Kreait\Firebase\Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
            ->createAuth();
    }

    // Fungsi untuk mendapatkan data mentah dari Firebase berdasarkan jenis device
    public function getRawDataMonitoring($device): array
    {

        $path = match ($device) {
            'aquaviska' => 'water_quality',
            'climeet' => 'weather_station',
            default => null,
        };
        if (!$path) {
            return [];
        }

        try {
            $data = $this->database->getReference($path)->getValue();
            return $data ?? [];
        } catch (\Exception $e) {
            // Log error jika terjadi masalah saat mengambil data
            Log::error('FirebaseService Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getDataAquaviskaByDeviceCode($deviceCode)
    {
        $data = $this->getRawDataMonitoring('aquaviska');
        return $data[$deviceCode] ?? [];
    }

    public function getDataClimeetByDeviceCode($deviceCode)
    {
        $data = $this->getRawDataMonitoring('climeet');
        return $data[$deviceCode] ?? [];
    }



    public function getDeviceData($device)
    {
        $path = match ($device) {
            'aquaviska' => 'water_quality',
            'climeet' => 'weather_station',
            default => null,
        };

        if (!$path) {
            return [];
        }

        try {
            $data = $this->database->getReference($path)->getValue();
            $dataDevices = $data['devices'] ?? [];
            return $dataDevices;
        } catch (\Exception $e) {
            Log::error("FirebaseService Error fetching data for device '{$device}': " . $e->getMessage());
            return [];
        }
    }



    public function setCalibration(string $device, string $deviceCode, array $data)
    {
        $path = match ($device) {
            'aquaviska' => 'water_quality',
            'climeet' => 'weather_station',
            default => null,
        };

        if (! $path) {
            throw new \InvalidArgumentException('Invalid device type.');
        }

        $payload = array_diff($data, [
            'device_code' => $deviceCode,
            'updated_at' => now()->toDateTimeString(),
        ]);

        return $this->database
            ->getReference("{$path}/{$deviceCode}/Calibration/{$data['sensor_type']}")
            ->set($payload);
    }

    public function getHistoryData(string $device, string $deviceCode, string $sensorType): array
    {
        $path = match ($device) {
            'aquaviska' => 'water_quality',
            'climeet' => 'weather_station',
            default => null,
        };

        if (! $path) {
            throw new \InvalidArgumentException('Invalid device type.');
        }

        try {
            $data = $this->database
                ->getReference("{$path}/{$deviceCode}/history")
                ->getValue();

            return $data ? array_values($data) : [];
        } catch (\Exception $e) {
            Log::error("FirebaseService Error fetching history data for device '{$device}', sensor '{$sensorType}': " . $e->getMessage());
            return [];
        }
    }

    // public function getDataClimeet()
    // {
    //     return $this->database->getReference('climeet')->getValue() ?? [];
    // }
}
