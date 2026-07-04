<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $waterQualityDevices = $this->getDeviceGroup('water_quality', 'AQUAVISKA');
        $weatherStationDevices = $this->getDeviceGroup('weather_station', 'IOT Climate');

        return view('admin.index', [
            'waterQualityDevices' => $waterQualityDevices,
            'weatherStationDevices' => $weatherStationDevices
        ]);
    }
    
    public function updateFirebaseStatus(Request $request)
    {
        $data = $request->validate([
            'node' => 'required|string|max:50',
            'status' => 'required|string|max:50',
            'type' => 'nullable|string|max:50',
        ]);

        $updated = DB::table('devices')
            ->where('device_code', $data['node'])
            ->update([
                'status' => $data['status'],
                'updated_at' => now(),
            ]);

        return response()->json(['success' => (bool) $updated]);
    }

    public function saveFirebaseDevice(Request $request)
    {
        $data = $request->validate([
            'node' => 'required|string|max:50',
            'type' => 'required|string|max:50',
            'device_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|string|max:50',
            'location.address' => 'nullable|string|max:255',
            'location.city' => 'nullable|string|max:100',
            'location.province' => 'nullable|string|max:100',
            'location.latitude' => 'nullable|numeric',
            'location.longitude' => 'nullable|numeric',
            'old_node' => 'nullable|string|max:50',
            'old_type' => 'nullable|string|max:50',
        ]);

        $deviceType = $data['type'] === 'AQUAVISKA' ? 'aquaviska' : 'climeet';
        $category = $data['type'] === 'AQUAVISKA' ? 'water_quality' : 'weather_station';

        DB::transaction(function () use ($data, $deviceType, $category) {
            $oldNode = $data['old_node'] ?? null;
            $oldType = $data['old_type'] ?? null;

            if ($oldNode && ($oldNode !== $data['node'] || $oldType !== $data['type'])) {
                DB::table('devices')->where('device_code', $oldNode)->delete();
            }

            $existing = DB::table('devices')->where('device_code', $data['node'])->first();

            DB::table('devices')->updateOrInsert(
                ['device_code' => $data['node']],
                [
                    'device_name' => $data['device_name'],
                    'device_type' => $deviceType,
                    'category' => $category,
                    'img_url' => $existing->img_url ?? null,
                    'description' => $data['description'] ?? null,
                    'status' => $data['status'] ?? 'offline',
                    'address' => $data['location']['address'] ?? null,
                    'city' => $data['location']['city'] ?? null,
                    'province' => $data['location']['province'] ?? null,
                    'latitude' => $data['location']['latitude'] ?? null,
                    'longitude' => $data['location']['longitude'] ?? null,
                    'is_preview' => true,
                    'is_deleted' => false,
                    'created_at' => $existing->created_at ?? now(),
                    'updated_at' => now(),
                ]
            );
        });

        return response()->json(['success' => true]);
    }

    public function deleteFirebaseDevice(Request $request)
    {
        $data = $request->validate([
            'node' => 'required|string|max:50',
            'type' => 'nullable|string|max:50',
        ]);

        $updated = DB::table('devices')
            ->where('device_code', $data['node'])
            ->update([
                'is_deleted' => true,
                'is_preview' => false,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => (bool) $updated]);
    }

    private function getDeviceGroup(string $category, string $typeLabel): array
    {
        $devices = DB::table('devices')
            ->where('category', $category)
            ->orderBy('device_code')
            ->get();

        $group = [];

        foreach ($devices as $device) {
            $group[$device->device_code] = [
                'device_name' => $device->device_name,
                'device_code' => $device->device_code,
                'type' => $typeLabel,
                'description' => $device->description ?? '',
                'status' => $device->status ?? 'offline',
                'location' => [
                    'address' => $device->address ?? '',
                    'city' => $device->city ?? '',
                    'province' => $device->province ?? '',
                    'latitude' => $device->latitude,
                    'longitude' => $device->longitude,
                ],
                'is_preview' => (bool) ($device->is_preview ?? false),
                'is_deleted' => (bool) ($device->is_deleted ?? false),
            ];
        }

        return $group;
    }
}
