<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Services\FirebaseService;

class LocationController extends Controller
{
    protected array $devices = [];
    
    public function __construct(protected FirebaseService $firebase)
    {
        $this->loadDevices();
    }
    
    protected function loadDevices(): void
    {
        try {
            // Ambil data dari Firebase
            $aquaviska = $this->firebase->getDeviceData('aquaviska') ?? [];
            $climeet = $this->firebase->getDeviceData('climeet') ?? [];
            
            // Gabungkan menggunakan array_merge
            $allDevices = array_merge($aquaviska, $climeet);
            
            // Format data untuk map
            $this->devices = $this->formatDevicesForMap($allDevices);
            
        } catch (\Exception $e) {
            \Log::error('Failed to load devices: ' . $e->getMessage());
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
                    'id' => $device['id'] ?? null,
                    'device_code' => $device['device_code'] ?? '',
                    'device_name' => $device['device_name'] ?? 'Unknown',
                    'type' => $device['type'] ?? 'aquaviska',
                    'status' => $device['status'] ?? 'inactive',
                    'condition_score' => $device['condition_score'] ?? 0,
                    'location' => [
                        'name' => $device['location']['name'] ?? '-',
                        'address' => $device['location']['address'] ?? '-',
                        'city' => $device['location']['city'] ?? '-',
                        'province' => $device['location']['province'] ?? '-',
                        'latitude' => (float) $device['location']['latitude'],
                        'longitude' => (float) $device['location']['longitude'],
                    ],
                    'latest_data' => $device['latest_data'] ?? [],
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
    
    /**
     * Get device location by ID
     */
    public function getLocation($id): \Illuminate\Http\JsonResponse
    {
        $device = collect($this->devices)->firstWhere('device_code', $id);
        
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
}