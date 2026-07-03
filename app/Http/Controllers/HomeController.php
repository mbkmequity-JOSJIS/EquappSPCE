<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $locations = [
            [
                'id' => 1,
                'name' => 'Embung Nglanggeran',
                'type' => 'AQUAVISKA',
                'status' => 'normal',
            ],
            [
                'id' => 2,
                'name' => 'Stasiun Klimatologi Kampus',
                'type' => 'IOT Climate',
                'status' => 'waspada',
            ],
            [
                'id' => 3,
                'name' => 'Daerah Irigasi Tegal',
                'type' => 'AQUAVISKA',
                'status' => 'bahaya',
            ],
        ];

        $totalNormal = 5;
        $totalBroken = 2;
        $totalDevices = $totalNormal + $totalBroken;
        $totalLocations = count($locations);

        return view('public.index', compact(
            'locations',
            'totalNormal',
            'totalBroken',
            'totalDevices',
            'totalLocations'
        ));
    }
}
