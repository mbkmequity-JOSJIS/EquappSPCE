<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class WaterQualityReadingSeeder extends Seeder
{
    public function run(): void
    {
        $devices = DB::table('devices')->where('category', 'water_quality')->get();

        foreach ($devices as $device) {
            $readings = [];
            
            for ($i = 0; $i < 50; $i++) {
                $readings[] = [
                    // GUNAKAN device_code, bukan device_id
                    'device_code' => $device->device_code,
                    'do_value' => Faker::create()->randomFloat(5, 4.00000, 12.00000),
                    'ph_value' => Faker::create()->randomFloat(2, 6.50, 8.50),
                    'tds_value' => Faker::create()->randomFloat(3, 100.000, 500.000),
                    'turbidity_value' => Faker::create()->randomFloat(2, 0.00, 50.00),
                    'temperature_value' => Faker::create()->randomFloat(2, 20.00, 32.00),
                    'recorded_at' => now()->subHours($i)->subMinutes(rand(0, 59)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('water_quality_latest')->insert($readings);
        }
    }
}