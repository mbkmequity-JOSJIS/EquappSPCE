<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Services\DeviceDataService;

class ModulController extends Controller
{
    protected array $deviceDataInfo = [];
    
    public function __construct(protected DeviceDataService $deviceDataService)
    {
        $this->deviceDataInfo = array_merge(
            $this->deviceDataService->getDevicesByModule('aquaviska'),
            $this->deviceDataService->getDevicesByModule('climeet')
        );
    }

    public function index(): View
    {
        $locations = $this->deviceDataInfo;
        return view('locations.index', compact('locations'));
    }
}