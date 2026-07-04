<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            // Water Quality Devices
            [
                // 'id' => Str::uuid(),
                'device_code' => 'AQU-01',
                'device_name' => 'Rowo Jombor WTQ',
                'device_type' => 'aquaviska',
                'category' => 'water_quality',
                'img_url' => 'rowo-jombor.png',
                'status' => 'online',
                'address' => 'Desa Rowo Jombor',
                'city' => 'Klaten',
                'province' => 'Jawa Tengah',
                'latitude' => -7.75164071,
                'longitude' => 110.62367482,
                'description' => 'IOT Jawa Tengah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                // 'id' => Str::uuid(),
                'device_code' => 'AQU-02',
                'device_name' => 'Waduk Cengklik WTQ',
                'device_type' => 'aquaviska',
                'category' => 'water_quality',
                'img_url' => 'cengklik.png',
                'status' => 'online',
                'address' => 'Desa Cengklik',
                'city' => 'Klaten',
                'province' => 'Jawa Tengah',
                'latitude' => -7.72000000,
                'longitude' => 110.60000000,
                'description' => 'IOT Jawa Tengah',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Weather Station Devices
            [
                // 'id' => Str::uuid(),
                'device_code' => 'CLM-01',
                'device_name' => 'Sekolah Alam Klaten WAQ',
                'device_type' => 'climeet',
                'category' => 'weather_station',
                'img_url' => 'sekolah-alam.png',
                'status' => 'online',
                'address' => 'PKBM Banyutowo',
                'city' => 'Klaten',
                'province' => 'Jawa Tengah',
                'latitude' => -7.71170472,
                'longitude' => 110.50292401,
                'description' => 'IOT Jawa Tengah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                // 'id' => Str::uuid(),
                'device_code' => 'CLM-02',
                'device_name' => 'Stasiun Cuaca Umbul Ponggok',
                'device_type' => 'climeet',
                'category' => 'weather_station',
                'img_url' => 'umbul-ponggok.png',
                'status' => 'offline',
                'address' => 'Desa Ponggok',
                'city' => 'Klaten',
                'province' => 'Jawa Tengah',
                'latitude' => -7.68000000,
                'longitude' => 110.55000000,
                'description' => 'IOT Jawa Tengah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('devices')->insert($devices);
    }
}