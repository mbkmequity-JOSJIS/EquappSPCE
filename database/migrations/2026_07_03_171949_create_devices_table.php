<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id(); // Tetap gunakan auto-increment untuk primary key internal
            
            // device_code WAJIB unique karena akan jadi referensi foreign key
            $table->string('device_code', 50)->unique(); 
            
            $table->string('device_name', 255);
            $table->string('device_type', 50);
            $table->string('category', 50);
            $table->string('img_url')->nullable();
            $table->string('status', 50)->default('offline');
            
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->boolean('is_deleted')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};