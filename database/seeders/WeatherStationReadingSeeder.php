<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeatherStationReadingSeeder extends Seeder
{
    public function run(): void
    {
        $devices = DB::table('devices')->where('category', 'weather_station')->get();

        foreach ($devices as $device) {
            $readings = [];
            
            for ($i = 0; $i < 50; $i++) {
                $readings[] = [
                    // GUNAKAN device_code, bukan device_id
                    'device_code' => $device->device_code,
                    'intensitas_hujan' => fake()->randomFloat(2, 0.00, 150.00),
                    'kecepatan_angin' => fake()->randomFloat(2, 0.00, 40.00),
                    'kelembaban' => fake()->randomFloat(2, 40.00, 99.00),
                    'pm25' => fake()->randomFloat(2, 5.00, 150.00),
                    'tekanan' => fake()->randomFloat(2, 1000.00, 1025.00),
                    'temperature' => fake()->randomFloat(2, 22.00, 35.00),
                    'total_hujan' => fake()->randomFloat(2, 0.00, 500.00),
                    'uv_index' => fake()->randomFloat(2, 0.00, 11.00),
                    'recorded_at' => now()->subHours($i)->subMinutes(rand(0, 59)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('weather_station_latest')->insert($readings);
        }
    }
}