<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calibrations', function (Blueprint $table) {
            $table->id();
            
            // RELASI MENGGUNAKAN device_code
            $table->string('device_code', 50);
            $table->foreign('device_code')
                  ->references('device_code')
                  ->on('devices')
                  ->cascadeOnDelete();
            
            $table->decimal('calib_do', 10, 5)->nullable();
            $table->decimal('calib_ph', 4, 2)->nullable();
            $table->decimal('calib_tds', 10, 3)->nullable();
            $table->decimal('calib_turbidity', 10, 2)->nullable();
            $table->decimal('calib_temperature', 5, 2)->nullable();
            
            $table->decimal('calib_intensitas_hujan', 10, 2)->nullable();
            $table->decimal('calib_kecepatan_angin', 10, 2)->nullable();
            $table->decimal('calib_kelembaban', 5, 2)->nullable();
            $table->decimal('calib_pm25', 10, 2)->nullable();
            $table->decimal('calib_tekanan', 10, 2)->nullable();
            $table->decimal('calib_total_hujan', 10, 2)->nullable();
            $table->decimal('calib_uv_index', 5, 2)->nullable();
            
            $table->timestamp('calibrated_at')->useCurrent();
            $table->string('calibrated_by', 100)->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calibrations');
    }
};