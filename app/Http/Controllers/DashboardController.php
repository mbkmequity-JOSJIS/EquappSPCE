<?php

namespace App\Http\Controllers;

use App\Services\DeviceDataService;

class DashboardController extends Controller
{
    public function __construct(protected DeviceDataService $deviceDataService)
    {
    }

    public function index()
    {
        return view('home');
    }



    public function dashboard(?string $module = null)
    {
        $module = in_array($module, ['aquaviska', 'climeet'], true) ? $module : 'aquaviska';
        
        $rawDevicesData = $this->deviceDataService->getDevicesByModule($module);
        
        $devicesData = [];
        if (is_array($rawDevicesData)) {
            foreach ($rawDevicesData as $key => $d) {
                $isPreview = !empty($d['is_preview']);
                $isDeleted = !empty($d['is_deleted']);
                
                if ($isPreview && !$isDeleted) {
                    $d['node'] = $key;
                    $devicesData[$key] = $d;
                }
            }
        }
        
        $totalDevices = count($devicesData);
        
        $activeDevices = count(array_filter($devicesData, function($d) {
            $status = $d['status'] ?? '';
            return in_array($status, ['online', 'active', 'normal']);
        }));
        
        $warningDevices = count(array_filter($devicesData, function($d) {
            $status = $d['status'] ?? '';
            $score = (int) ($d['condition_score'] ?? 0);

            return in_array($status, ['warning', 'waspada', 'maintenance']) || ($status !== 'offline' && $score > 0 && $score < 70);
        }));
        
        $totalScore = array_sum(array_column($devicesData, 'condition_score')) ?: 0;
        $avgScoreAll = $totalDevices > 0 ? round($totalScore / $totalDevices) : 0;
        
        $regionsFromFirebase = [];
        foreach ($devicesData as $device) {
            if (isset($device['location']['city'])) {
                $city = $device['location']['city'];
                if (!isset($regionsFromFirebase[$city])) {
                    $regionsFromFirebase[$city] = [
                        'scores' => [],
                        'device_count' => 0,
                        'address' => $device['location']['address'] ?? $city
                    ];
                }
                $regionsFromFirebase[$city]['scores'][] = $device['condition_score'] ?? 0;
                $regionsFromFirebase[$city]['device_count']++;
            }
        }
        
        // Format regions untuk grafik
        $regionsFormatted = [];
        foreach ($regionsFromFirebase as $city => $data) {
            $avgScore = count($data['scores']) > 0 ? round(array_sum($data['scores']) / count($data['scores'])) : 0;
            $regionsFormatted[] = [
                'name' => $city,
                'value' => $avgScore,
                'detail' => $data['address'] . ' (' . $data['device_count'] . ' device)',
            ];
        }
        usort($regionsFormatted, fn($a, $b) => $b['value'] <=> $a['value']);
        $regionsFormatted = array_slice($regionsFormatted, 0, 4);
        

        // pemetaan unit untuk satuan sensor
        $unitMapping = [
            'temperature' => '°C',
            'kelembaban' => '%',
            'tekanan' => 'hPa',
            'flow' => 'L/min',
            'level' => 'm',
            'do' => 'mg/L',
            'tds' => 'ppm',
            'turbidity' => 'NTU',
            'ph' => '',
            'pm25' => 'µg/m³',
            'pm10' => 'µg/m³',
            'uvIndex' => 'index',
            'intensitasHujan' => 'mm/h',
            'kecepatanAngin' => 'm/s',
        ];

        $sensorSlidesFromFirebase = [];
        foreach ($devicesData as $device) {
            if (isset($device['location']['latitude']) && isset($device['location']['longitude'])) {
                $sensors = [];
                $latestData = $device['latest_data'] ?? [];

                foreach ($latestData as $key => $value) {
                    $sensorInfo = $this->getSensorInfo($key, $value, $module);
                    if ($sensorInfo) {
                        $sensors[] = $sensorInfo;
                    }
                }
                
                $sensors = array_slice($sensors, 0, 5);
                $score = $device['condition_score'] ?? 0;
                $scoreColor = $score >= 70 ? 'text-emerald-600' : ($score >= 50 ? 'text-amber-600' : 'text-red-600');
                $sensorSlidesFromFirebase[] = [
                    'title' => $device['location']['city'] ?? $device['device_name'] ?? 'Unknown',
                    'subtitle' => $device['device_name'] ?? 'Device',
                    'location' => $device['location']['city'] ?? '-',
                    'score' => $score,
                    'scoreColor' => $scoreColor,
                    'sensors' => $sensors,
                    'badgeClass' => $module === 'aquaviska' ? 'bg-cyan-100 text-cyan-700' : 'bg-orange-100 text-orange-700',
                    'icon' => $module === 'aquaviska' ? 'fa-solid fa-water' : 'fa-solid fa-cloud-sun',
                    'bgClass' => $module === 'aquaviska' ? 'from-cyan-50 to-white' : 'from-orange-50 to-white',
                    'lastUpdate' => now()->format('H:i:s'),
                    'deviceCount' => 1,
                    'sparkline' => $this->generateSparklineData($score),
                    'trendColor' => 'text-emerald-500',
                    'trendIcon' => 'fa-arrow-up',
                    'trendValue' => '+5%',
                    'unit' => $unitMapping[$key] ?? '',
                ];
            }
        }
        
        $devicesList = [];
        foreach ($devicesData as $device) {
            $status = $device['status'] ?? 'unknown';
            $devicesList[] = [
                'name' => $device['device_name'] ?? 'Unknown Device',
                'status' => $this->getStatusLabel($status),
                'statusClass' => $this->getStatusClass($status),
                'detail' => $device['location']['city'] ?? $device['location']['address'] ?? 'Lokasi tidak tersedia',
                'device_code' => $device['device_code'] ?? '',
                'condition_score' => $device['condition_score'] ?? 0,
            ];
        }
        
        $damageList = [];
        foreach ($devicesData as $device) {
            if (!empty($device['recommendation'])) {
                $damageList[] = [
                    'label' => $device['device_name'] ?? 'Device',
                    'value' => $device['condition_score'] ?? 0,
                    'desc' => $device['recommendation'],
                ];
            }
        }
        
        $chartData = $this->getChartData($module, $devicesData);
        $chartLabels = $this->getChartLabels($chartData);
        $chartDatasets = $this->getChartDatasets($module, $devicesData, $regionsFormatted);
        
        $aiSummary = $this->generateAiSummary($module, $avgScoreAll, $warningDevices);
        
        $alerts = $this->generateAlerts($module, $devicesData);
        
        // Gunakan data yang sudah dikumpulkan, jangan pakai dummy jika ada data real
        $dashboard = [
            'key' => $module,
            'label' => $module === 'aquaviska' ? 'AquaViska' : 'Climeet',
            'subtitle' => $module === 'aquaviska' 
                ? 'Dashboard kualitas air tambak perikanan'
                : 'Analisis cuaca dan kegiatan luar ruang yang aman',
            'icon' => $module === 'aquaviska' ? 'fa-solid fa-water' : 'fa-solid fa-cloud-sun',
            'summaryIcons' => $module === 'aquaviska' 
                ? ['fa-solid fa-microchip', 'fa-solid fa-circle-check', 'fa-solid fa-triangle-exclamation', 'fa-solid fa-chart-line']
                : ['fa-solid fa-satellite-dish', 'fa-solid fa-circle-check', 'fa-solid fa-triangle-exclamation', 'fa-solid fa-cloud-rain'],
            'summary' => [
                ['label' => 'Total alat', 'value' => $totalDevices, 'note' => 'Perangkat terpasang'],
                ['label' => 'Alat aktif', 'value' => $activeDevices, 'note' => 'Berfungsi normal'],
                ['label' => 'Perlu perhatian', 'value' => $warningDevices, 'note' => 'Waspada / maintenance'],
                ['label' => 'Rata-rata skor', 'value' => $avgScoreAll, 'note' => 'Kondisi keseluruhan'],
            ],

            'ai_summary' => $aiSummary,
            'regions' => $regionsFormatted,
            'sensorSlides' => $sensorSlidesFromFirebase,
            'devices' => $devicesList,
            'damage' => $damageList,
            'alerts' => $alerts,
            'mitigation' => $this->getMitigationList($module),
            'chartLabels' => $chartLabels,
            'chartDatasets' => $chartDatasets,
        ];
        
        return view('dashboard', compact('dashboard'));
    }
    

    /**
     * Generate sparkline data untuk chart mini
     */
    private function generateSparklineData($currentScore)
    {
        $base = max(30, min(90, $currentScore));
        return [
            $base - rand(5, 15),
            $base - rand(0, 10),
            $base + rand(0, 5),
            $base - rand(0, 8),
            $base + rand(2, 10),
            $base + rand(0, 5),
            $currentScore
        ];
    }
    
    /**
     * Get chart labels berdasarkan data
     */
    private function getChartLabels($chartData)
    {
        if (empty($chartData)) {
            return ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        }
        return array_column($chartData, 'label');
    }
    
    /**
     * Get chart datasets untuk line chart
     */
    private function getChartDatasets($module, $devicesData, $regions)
    {
        $colors = $module === 'aquaviska' 
            ? ['#06b6d4', '#3b82f6', '#8b5cf6', '#10b981']
            : ['#f97316', '#ef4444', '#eab308', '#84cc16'];
        
        $datasets = [];
        foreach ($regions as $index => $region) {
            // Generate data berdasarkan score wilayah
            $baseValue = $region['value'];
            $datasets[] = [
                'label' => $region['name'],
                'data' => [
                    max(0, $baseValue - rand(2, 8)),
                    max(0, $baseValue - rand(0, 5)),
                    max(0, $baseValue + rand(0, 5)),
                    max(0, $baseValue - rand(1, 6)),
                    max(0, $baseValue + rand(1, 4)),
                    max(0, $baseValue - rand(0, 3)),
                    $baseValue,
                ],
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => 'rgba(' . hexdec(substr($colors[$index % count($colors)], 1, 2)) . ', ' . 
                                    hexdec(substr($colors[$index % count($colors)], 3, 2)) . ', ' . 
                                    hexdec(substr($colors[$index % count($colors)], 5, 2)) . ', 0.1)',
                'fill' => true,
                'tension' => 0.3,
            ];
        }
        
        return $datasets;
    }
    
    /**
     * Get sensor information based on sensor type
     */
    private function getSensorInfo($key, $value, $module)
    {
        $sensorConfig = [
            'ph' => ['icon' => 'fa-flask', 'color' => 'text-purple-500', 'bar' => 'bg-purple-500', 'max' => 14, 'unit' => ''],
            'temperature' => ['icon' => 'fa-thermometer-half', 'color' => 'text-red-500', 'bar' => 'bg-red-500', 'max' => 50, 'unit' => '°C'],
            'tds' => ['icon' => 'fa-tint', 'color' => 'text-cyan-500', 'bar' => 'bg-cyan-500', 'max' => 1000, 'unit' => 'ppm'],
            'turbidity' => ['icon' => 'fa-eye', 'color' => 'text-amber-500', 'bar' => 'bg-amber-500', 'max' => 100, 'unit' => 'NTU'],
            'do' => ['icon' => 'fa-wind', 'color' => 'text-blue-500', 'bar' => 'bg-blue-500', 'max' => 10, 'unit' => 'mg/L'],
            'kelembaban' => ['icon' => 'fa-droplet', 'color' => 'text-sky-500', 'bar' => 'bg-sky-500', 'max' => 100, 'unit' => '%'],
            'pm25' => ['icon' => 'fa-smog', 'color' => 'text-slate-500', 'bar' => 'bg-slate-500', 'max' => 500, 'unit' => 'µg/m³'],
            'pm10' => ['icon' => 'fa-cloud', 'color' => 'text-slate-400', 'bar' => 'bg-slate-400', 'max' => 500, 'unit' => 'µg/m³'],
            'uvIndex' => ['icon' => 'fa-sun', 'color' => 'text-amber-500', 'bar' => 'bg-amber-500', 'max' => 15, 'unit' => 'index'],
            'intensitasHujan' => ['icon' => 'fa-cloud-showers-heavy', 'color' => 'text-blue-600', 'bar' => 'bg-blue-600', 'max' => 50, 'unit' => 'mm/h'],
            'kecepatanAngin' => ['icon' => 'fa-wind', 'color' => 'text-gray-500', 'bar' => 'bg-gray-500', 'max' => 30, 'unit' => 'm/s'],
        ];

        $keyLower = strtolower($key);
        $config = $sensorConfig[$keyLower] ?? null;
        if (!$config) return null;

        $percentage = min(100, max(0, ($value / $config['max']) * 100));
        $displayValue = is_numeric($value) ? round($value, 1) . ' ' . $config['unit'] : $value;

        return [
            'label' => ucfirst(str_replace('_', ' ', $key)),
            'value' => $displayValue,
            'icon' => $config['icon'],
            'iconColor' => $config['color'],
            'barColor' => $config['bar'],
            'percentage' => round($percentage),
        ];
    }
    
    /**
     * Get chart data from devices
     */
    private function getChartData($module, $devicesData)
    {
        $chartData = [];
        $sensors = $module === 'aquaviska' 
            ? ['ph', 'temperature', 'tds', 'turbidity', 'do']
            : ['temperature', 'kelembaban', 'pm25', 'pm10', 'uvIndex', 'intensitasHujan', 'kecepatanAngin'];
        
        foreach ($sensors as $sensor) {
            $total = 0;
            $count = 0;
            foreach ($devicesData as $device) {
                if (isset($device['latest_data'][$sensor])) {
                    $total += $device['latest_data'][$sensor];
                    $count++;
                }
            }
            if ($count > 0) {
                $avgValue = $total / $count;
                $maxValue = $this->getMaxValue($sensor);
                $percentage = min(100, max(0, ($avgValue / $maxValue) * 100));
                $chartData[] = [
                    'label' => $this->getChartLabel($sensor),
                    'value' => round($percentage),
                ];
            }
        }
        
        return $chartData;
    }
    
    private function getMaxValue($sensor)
    {
        return match ($sensor) {
            'ph' => 14,
            'temperature' => 50,
            'do' => 10,
            'tds', 'turbidity' => 100,
            'humidity', 'kelembaban' => 100,
            'uv', 'uvIndex' => 15,
            'intensitasHujan', 'totalHujan' => 150,
            'kecepatanAngin' => 30,
            'tekanan' => 1050,
            default => 500,
        };
    }

    private function getChartLabel(string $sensor): string
    {
        return match ($sensor) {
            'ph' => 'pH',
            'temperature' => 'Temperature',
            'tds' => 'TDS',
            'turbidity' => 'Turbidity',
            'do' => 'Dissolved Oxygen',
            'kelembaban' => 'Humidity',
            'pm25' => 'PM2.5',
            'pm10' => 'PM10',
            'uvIndex' => 'UV Index',
            'intensitasHujan' => 'Rainfall',
            'kecepatanAngin' => 'Wind Speed',
            'totalHujan' => 'Total Rainfall',
            'tekanan' => 'Pressure',
            default => ucfirst(str_replace('_', ' ', $sensor)),
        };
    }
    
    private function getStatusLabel($status)
    {
        return match ($status) {
            'online', 'active', 'normal' => 'Aktif',
            'warning', 'waspada', 'maintenance' => 'Waspada',
            'offline', 'damaged', 'rusak', 'critical' => 'Bermasalah',
            default => 'Unknown',
        };
    }
    
    private function getStatusClass($status)
    {
        return match ($status) {
            'online', 'active', 'normal' => 'bg-emerald-100 text-emerald-700',
            'warning', 'waspada', 'maintenance' => 'bg-amber-100 text-amber-700',
            'offline', 'damaged', 'rusak', 'critical' => 'bg-rose-100 text-rose-700',
            default => 'bg-slate-100 text-slate-700',
        };
    }
    
    private function generateAiSummary($module, $avgScore, $warningCount)
    {
        if ($warningCount > 0) {
            return "⚠️ Terdapat {$warningCount} perangkat yang memerlukan perhatian. Segera lakukan pengecekan.";
        }
        
        if ($avgScore >= 80) {
            return "✅ Semua perangkat dalam kondisi baik. Monitoring berjalan normal.";
        } elseif ($avgScore >= 60) {
            return "⚠️ Beberapa parameter menunjukkan fluktuasi. Disarankan monitoring rutin.";
        }
        
        return "🔴 Beberapa perangkat menunjukkan kondisi kritis. Segera lakukan tindakan.";
    }
    
    private function generateAlerts($module, $devicesData)
    {
        $alerts = [];
        
        foreach ($devicesData as $device) {
            $latestData = $device['latest_data'] ?? [];
            $location = $device['location']['city'] ?? 'lokasi';
            
            if (isset($latestData['do']) && $latestData['do'] < 4) {
                $alerts[] = "⚠️ DO rendah di {$location} ({$latestData['do']} mg/L)";
            }
            if (isset($latestData['turbidity']) && $latestData['turbidity'] > 30) {
                $alerts[] = "🌊 Kekeruhan tinggi di {$location}";
            }
            if (isset($latestData['uvIndex']) && $latestData['uvIndex'] > 8) {
                $alerts[] = "☀️ UV tinggi di {$location} ({$latestData['uvIndex']})";
            }
            if (isset($latestData['pm25']) && $latestData['pm25'] > 100) {
                $alerts[] = "😷 Kualitas udara tidak sehat di {$location}";
            }
            if (isset($latestData['intensitasHujan']) && $latestData['intensitasHujan'] > 100) {
                $alerts[] = "🌧️ Intensitas hujan tinggi di {$location}";
            }
            if (isset($latestData['kecepatanAngin']) && $latestData['kecepatanAngin'] > 20) {
                $alerts[] = "💨 Kecepatan angin tinggi di {$location}";
            }
        }
        
        if (empty($alerts)) {
            $alerts = ["✅ Semua perangkat dalam kondisi normal. Tidak ada alert."];
        }
        
        return array_slice($alerts, 0, 3);
    }
    
    private function getMitigationList($module)
    {
        if ($module === 'aquaviska') {
            return [
                'Kalibrasi sensor air secara rutin untuk menjaga akurasi.',
                'Siapkan aerator cadangan ketika DO menurun.',
                'Bersihkan probe dari lumut secara berkala.',
                'Cek koneksi dan kabel dari potensi korosi.',
            ];
        } else {
            return [
                'Hindari aktivitas berat saat UV Index tinggi.',
                'Pindahkan kegiatan lapangan ke pagi hari.',
                'Gunakan penutup sensor untuk melindungi dari air hujan.',
                'Periksa baterai dan koneksi sebelum cuaca buruk.',
            ];
        }
    }
}