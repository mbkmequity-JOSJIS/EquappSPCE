<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\FirebaseService;

class ModulController extends Controller
{
    protected array $deviceDataInfo = [];
    
    public function __construct(protected FirebaseService $firebase)
    {
        
        $aquaviska = $this->firebase->getDeviceData('aquaviska') ?? [];
        $climeet = $this->firebase->getDeviceData('climeet') ?? [];
        $this->deviceDataInfo = array_merge($aquaviska, $climeet);
    }

    public function index(): View
    {
        $locations = $this->deviceDataInfo;
        return view('locations.index', compact('locations'));
    }


    // /**
    //  * API endpoint: Ambil data sensor terkini untuk partial refresh
    //  */
    // public function getSensorData()
    // {
    //     $catalog = $this->buildCatalog($this->firebase->getDeviceData());
    //     $data = [];

    //     foreach ($catalog as $loc) {
    //         $data[] = [
    //             'id' => $loc['id'],
    //             'name' => $loc['name'],
    //             'condition_score' => $loc['condition_score'],
    //             'status' => $loc['status'],
    //             'status_label' => $loc['status_label'],
    //             'sensors' => $loc['sensors'] ?? [],
    //         ];
    //     }

    //     return response()
    //         ->json($data)
    //         ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
    //         ->header('Pragma', 'no-cache');
    // }

    // /**
    //  * API endpoint: Ambil detail lokasi spesifik dengan data Firebase terkini
    //  */
    // public function getLocationDetail(int $id)
    // {
    //     if (! isset($this->catalog[$id])) {
    //         return response()->json(['error' => 'Location not found'], 404);
    //     }

    //     $catalog = $this->buildCatalog($this->firebase->getRawWaterQualityData());

    //     if (! isset($catalog[$id])) {
    //         return response()->json(['error' => 'Location not found'], 404);
    //     }

    //     $loc = $catalog[$id];

    //     return response()
    //         ->json([
    //             'id' => $loc['id'],
    //             'name' => $loc['name'],
    //             'condition_score' => $loc['condition_score'],
    //             'status' => $loc['status'],
    //             'status_label' => $loc['status_label'],
    //             'sensors' => $loc['sensors'] ?? [],
    //             'recommendation' => $loc['recommendation'] ?? '',
    //         ])
    //         ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
    //         ->header('Pragma', 'no-cache');
    // }
}