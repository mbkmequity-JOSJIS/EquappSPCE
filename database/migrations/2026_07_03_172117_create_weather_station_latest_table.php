<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_station_latest', function (Blueprint $table) {
            $table->id();
            
            // RELASI MENGGUNAKAN device_code
            $table->string('device_code', 50);
            $table->foreign('device_code')
                  ->references('device_code')
                  ->on('devices')
                  ->cascadeOnDelete();
            
            $table->decimal('intensitas_hujan', 10, 2)->nullable();
            $table->decimal('kecepatan_angin', 10, 2)->nullable();
            $table->decimal('kelembaban', 5, 2)->nullable();
            $table->decimal('pm25', 10, 2)->nullable();
            $table->decimal('tekanan', 10, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('total_hujan', 10, 2)->nullable();
            $table->decimal('uv_index', 5, 2)->nullable();
            
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['device_code', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_station_readings');
    }
};