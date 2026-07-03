<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;

class IndicatorController extends Controller
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Get AQUA VISKA indicators with real-time data and status
     */
    public function aquaviska($area = 'Area-1'): JsonResponse
    {
        try {
            $indicators = $this->firebaseService->getAquaviskaIndicators($area);
            
            return response()->json([
                'success' => true,
                'data' => $indicators,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sensor data for a specific location
     * Maps location ID to Firebase area
     */
    public function location($locationId): JsonResponse
    {
        try {
            // Map location ID to Firebase area
            // For now, all locations use Area-1
            // In future, you can expand this mapping
            $areaMapping = [
                1 => 'Area-1',
                2 => 'Area-1', // Placeholder
                3 => 'Area-1', // Placeholder
            ];

            $area = $areaMapping[$locationId] ?? 'Area-1';
            $indicators = $this->firebaseService->getAquaviskaIndicators($area);

            return response()->json([
                'success' => true,
                'data' => $indicators,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get IoT CLIMATE indicators for a specific location/device
     */
    public function iotClimate($locationId = 1): JsonResponse
    {
        try {
            // Map location ID to device ID
            $deviceMapping = [
                1 => 'device_1',
                2 => 'device_1', // Can be changed later
                3 => 'device_1', // Can be changed later
            ];

            $deviceId = $deviceMapping[$locationId] ?? 'device_1';
            $indicators = $this->firebaseService->getIotClimateIndicators($deviceId);

            return response()->json([
                'success' => true,
                'data' => $indicators,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
