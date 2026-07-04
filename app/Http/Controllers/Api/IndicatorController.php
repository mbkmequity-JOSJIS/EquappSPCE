<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeviceDataService;
use Illuminate\Http\JsonResponse;

class IndicatorController extends Controller
{
    public function __construct(protected DeviceDataService $deviceDataService)
    {
    }

    /**
     * Get AQUA VISKA indicators with real-time data and status
     */
    public function aquaviska($area = 'Area-1'): JsonResponse
    {
        try {
            $devices = $this->deviceDataService->getDevicesByModule('aquaviska');
            $indicators = $this->buildIndicators($devices, $area);
            
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
            $devices = $this->deviceDataService->getDevicesByModule('aquaviska');
            $device = collect($devices)->values()->get(max(0, (int) $locationId - 1));
            $indicators = $device ? [$device] : [];

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
            $devices = $this->deviceDataService->getDevicesByModule('climeet');
            $device = collect($devices)->values()->get(max(0, (int) $locationId - 1));
            $indicators = $device ? [$device] : [];

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

    private function buildIndicators(array $devices, string $area): array
    {
        $filteredDevices = array_values(array_filter($devices, function (array $device) use ($area) {
            if ($area === 'Area-1') {
                return true;
            }

            $city = strtolower((string) data_get($device, 'location.city', ''));
            $deviceCode = strtolower((string) ($device['device_code'] ?? ''));

            return $city === strtolower($area) || $deviceCode === strtolower($area);
        }));

        return array_map(function (array $device) {
            return [
                'device_code' => $device['device_code'] ?? '',
                'device_name' => $device['device_name'] ?? '',
                'location' => $device['location'] ?? [],
                'status' => $device['status'] ?? 'offline',
                'condition_score' => $device['condition_score'] ?? 0,
                'recommendation' => $device['recommendation'] ?? '',
                'sensors' => $device['sensors'] ?? [],
                'latest_data' => $device['latest_data'] ?? [],
            ];
        }, $filteredDevices);
    }
}
