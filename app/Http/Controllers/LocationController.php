<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Services\DeviceDataService;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    protected array $devices = [];
    
    public function __construct(protected DeviceDataService $deviceDataService)
    {
        $this->loadDevices();
    }
    
    protected function loadDevices(): void
    {
        try {
            $this->devices = $this->formatDevicesForMap(array_merge(
                $this->deviceDataService->getDevicesByModule('aquaviska'),
                $this->deviceDataService->getDevicesByModule('climeet')
            ));
            
        } catch (\Exception $e) {
            Log::error('Failed to load devices: ' . $e->getMessage());
            $this->devices = [];
        }
    }
    
    protected function formatDevicesForMap(array $devices): array
    {
        $formatted = [];
        
        foreach ($devices as $device) {
            // Pastikan device memiliki koordinat
            if (isset($device['location']['latitude']) && isset($device['location']['longitude'])) {
                $formatted[] = [
                    'id' => $device['device_code'] ?? null,
                    'device_code' => $device['device_code'] ?? '',
                    'name' => $device['device_name'] ?? 'Unknown',
                    'device_name' => $device['device_name'] ?? 'Unknown',
                    'type' => $device['type'] ?? 'AQUAVISKA',
                    'status' => $device['status'] ?? 'inactive',
                    'status_label' => $this->getStatusLabel($device['status'] ?? 'inactive'),
                    'condition_score' => $device['condition_score'] ?? 0,
                    'recommendation' => $device['recommendation'] ?? '',
                    'location' => [
                        'name' => $device['location']['name'] ?? '-',
                        'address' => $device['location']['address'] ?? '-',
                        'city' => $device['location']['city'] ?? '-',
                        'province' => $device['location']['province'] ?? '-',
                        'latitude' => (float) $device['location']['latitude'],
                        'longitude' => (float) $device['location']['longitude'],
                    ],
                    'latest_data' => $device['latest_data'] ?? [],
                    'sensors' => $device['sensors'] ?? [],
                ];
            }
        }
        
        return $formatted;
    }
    
    public function index(): View
    {
        return view('locations.index', [
            'devices' => $this->devices
        ]);
    }
    
    /**
     * Get device locations as JSON for API
     */
    public function getLocations(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'devices' => $this->devices,
            'total' => count($this->devices)
        ]);
    }

    public function show(string $id): View
    {
        $location = $this->findDevice($id);

        abort_if(empty($location), 404);

        return view('locations.show', [
            'location' => $location,
        ]);
    }

    public function getSensorData(): JsonResponse
    {
        return $this->getLocations();
    }

    public function getLocationDetail(string $id): JsonResponse
    {
        return $this->getLocation($id);
    }
    
    /**
     * Get device location by ID
     */
    public function getLocation(string $id): JsonResponse
    {
        $device = $this->findDevice($id);
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'device' => $device
        ]);
    }

    private function findDevice(string $deviceCode): array
    {
        return collect($this->devices)->firstWhere('device_code', $deviceCode) ?? [];
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'online', 'active', 'normal' => 'Aktif',
            'warning', 'waspada', 'maintenance' => 'Waspada',
            'offline', 'damaged', 'rusak', 'critical' => 'Bermasalah',
            default => 'Unknown',
        };
    }
}