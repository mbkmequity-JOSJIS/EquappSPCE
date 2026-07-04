<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_quality_latest', function (Blueprint $table) {
            $table->id();
            
            // RELASI MENGGUNAKAN device_code
            $table->string('device_code', 50);
            $table->foreign('device_code')
                  ->references('device_code')
                  ->on('devices')
                  ->cascadeOnDelete();
            
            $table->decimal('do_value', 10, 5)->nullable();
            $table->decimal('ph_value', 4, 2)->nullable();
            $table->decimal('tds_value', 10, 3)->nullable();
            $table->decimal('turbidity_value', 10, 2)->nullable();
            $table->decimal('temperature_value', 5, 2)->nullable();
            
            $table->timestamp('recorded_at');
            $table->timestamps();

            // Index disesuaikan dengan kolom device_code
            $table->index(['device_code', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_quality_readings');
    }
};