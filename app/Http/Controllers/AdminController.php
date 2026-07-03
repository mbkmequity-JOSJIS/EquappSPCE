<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class AdminController extends Controller
{
    public function index()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
            ->withDatabaseUri('https://equapp-5718f-default-rtdb.firebaseio.com');

        $database = $factory->createDatabase();
        
        $waterQualityDevices = $database->getReference('water_quality/devices')->getValue() ?? [];
        $weatherStationDevices = $database->getReference('weather_station/devices')->getValue() ?? [];

        return view('admin.index', [
            'waterQualityDevices' => $waterQualityDevices,
            'weatherStationDevices' => $weatherStationDevices
        ]);
    }
    
    public function updateFirebaseStatus(Request $request)
    {
        $node = $request->input('node');
        $type = $request->input('type'); // 'AQUAVISKA' or 'IOT Climate'
        $status = $request->input('status');

        if ($node && $type && $status) {
            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
                ->withDatabaseUri('https://equapp-5718f-default-rtdb.firebaseio.com');

            $database = $factory->createDatabase();
            $path = $type === 'AQUAVISKA' ? 'water_quality/devices/' : 'weather_station/devices/';
            $database->getReference($path . $node . '/status')->set($status);
            
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 400);
    }

    public function saveFirebaseDevice(Request $request)
    {
        $data = $request->all();
        $oldNode = $data['old_node'] ?? null;
        $oldType = $data['old_type'] ?? null;
        
        $node = $data['node'];
        $type = $data['type']; // 'AQUAVISKA' or 'IOT Climate'
        
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
            ->withDatabaseUri('https://equapp-5718f-default-rtdb.firebaseio.com');

        $database = $factory->createDatabase();
        
        $newPathPrefix = $type === 'AQUAVISKA' ? 'water_quality/devices/' : 'weather_station/devices/';
        $firebaseType = $type === 'AQUAVISKA' ? 'water' : 'climeet';
        
        // Prepare base data to save
        $deviceData = [
            'device_name' => $data['device_name'] ?? '',
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'offline',
            'type' => $firebaseType,
            'location' => [
                'address' => $data['location']['address'] ?? '',
                'city' => $data['location']['city'] ?? '',
                'province' => $data['location']['province'] ?? '',
                'latitude' => floatval($data['location']['latitude'] ?? 0),
                'longitude' => floatval($data['location']['longitude'] ?? 0),
            ]
        ];

        // Fetch existing data (either from old node if it changed, or current node)
        $existingData = null;
        if ($oldNode && $oldType) {
            $oldPathPrefix = $oldType === 'AQUAVISKA' ? 'water_quality/devices/' : 'weather_station/devices/';
            $existingData = $database->getReference($oldPathPrefix . $oldNode)->getValue();
            
            // If path changed or node changed, delete the old one
            if ($oldPathPrefix !== $newPathPrefix || $oldNode !== $node) {
                $database->getReference($oldPathPrefix . $oldNode)->remove();
            }
        } else {
            // Even if no oldNode, it might already exist in firebase because hardware created it
            $existingData = $database->getReference($newPathPrefix . $node)->getValue();
        }

        if ($existingData) {
            // Merge to keep fields like device_code, img_url, etc.
            $deviceData = array_merge($existingData, $deviceData);
        } else {
            // Fallback if truly new
            $deviceData['device_code'] = $data['device_code'] ?? '';
        }

        // Set draft/approval status
        $deviceData['is_preview'] = true;
        $deviceData['is_deleted'] = false;

        $database->getReference($newPathPrefix . $node)->set($deviceData);
        
        return response()->json(['success' => true]);
    }

    public function deleteFirebaseDevice(Request $request)
    {
        $node = $request->input('node');
        $type = $request->input('type');

        if ($node && $type) {
            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
                ->withDatabaseUri('https://equapp-5718f-default-rtdb.firebaseio.com');

            $database = $factory->createDatabase();
            $path = $type === 'AQUAVISKA' ? 'water_quality/devices/' : 'weather_station/devices/';
            
            // Soft delete
            $database->getReference($path . $node)->update([
                'is_deleted' => true,
                'is_preview' => false,
            ]);
            
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 400);
    }
}
