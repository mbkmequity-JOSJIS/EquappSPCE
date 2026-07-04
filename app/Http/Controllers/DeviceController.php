<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\DeviceDataService;
use App\Services\GroqAIService;

class DeviceController extends Controller
{
    public function __construct(
        protected DeviceDataService $deviceDataService,
        protected GroqAIService $aiService,
    ) {
    }

    public function index()
    {
        return view('devices.index');
    }

    public function devices($device)
    {
        if (!in_array($device, ['aquaviska', 'climeet'], true)) {
            abort(404);
        }

        $dataDevices = array_filter(
            $this->deviceDataService->getDevicesByModule($device),
            fn (array $data) => ($data['is_preview'] ?? false) && !($data['is_deleted'] ?? false)
        );

        return view('devices.device-list', compact('dataDevices', 'device'));
    }

    public function show($device, $device_code)
    {
        [$deviceDataInfo, $deviceDataMonitoring] = $this->resolveDeviceDetail($device, $device_code);

        // Tambahkan AI Recommendations
        try {
            // $aiRecommendations = $this->aiService->generateRecommendations($deviceDataInfo, $deviceDataMonitoring);
            $deviceDataMonitoring['ai_recommendations'] = [
                'summary' => $aiRecommendations['summary'] ?? 'Tidak ada ringkasan',
                'recommendations' => $aiRecommendations['recommendations'] ?? [],
                'mitigation_tips' => $aiRecommendations['mitigation_tips'] ?? [],
                'last_analysis' => $aiRecommendations['last_analysis'] ?? now()->format('H:i:s'),
                'status' => $aiRecommendations['status'] ?? 'unknown'
            ];
            // dd($deviceDataMonitoring['ai_recommendations']);
        } catch (\Exception $e) {
            Log::error('AI Recommendation error: ' . $e->getMessage());
            $deviceDataMonitoring['ai_recommendations'] = [
                'summary' => 'AI tidak tersedia saat ini',
                'recommendations' => [],
                'mitigation_tips' => [],
                'last_analysis' => now()->format('H:i:s'),
                'status' => 'error'
            ];
        }

        return view('devices.show-detail', compact('deviceDataMonitoring', 'deviceDataInfo', 'device'));
    }

    public function getDetail($device, $device_code): JsonResponse
    {
        [$deviceDataInfo, $deviceDataMonitoring] = $this->resolveDeviceDetail($device, $device_code);

        if (empty($deviceDataInfo)) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        // Tambahkan AI recommendations ke response API untuk real-time update
        try {
            $aiRecommendations = $this->aiService->generateRecommendations($deviceDataInfo, $deviceDataMonitoring);
        } catch (\Exception $e) {
            Log::error('AI Recommendation API error: ' . $e->getMessage());
            $aiRecommendations = [
                'summary' => 'AI tidak tersedia',
                'recommendations' => [],
                'mitigation_tips' => [],
                'last_analysis' => now()->format('H:i:s'),
                'status' => 'error'
            ];
        }

        return response()
            ->json([
                'device_code' => $device_code,
                'device_name' => $deviceDataInfo['device_name'] ?? '',
                'type' => $deviceDataInfo['type'] ?? '',
                'location' => $deviceDataInfo['location'] ?? [],
                'status' => $deviceDataInfo['status'] ?? '',
                'status_label' => $deviceDataInfo['status'] ?? '',
                'condition_score' => $deviceDataInfo['condition_score'] ?? 0,
                'recommendation' => $deviceDataInfo['recommendation'] ?? '',
                'sensors' => $deviceDataMonitoring['sensors'] ?? [],
                'ai_recommendations' => $aiRecommendations,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function storeCalibration(Request $request, $device, $device_code): JsonResponse
    {
        $calibrationData = $request->validate([
            'sensor_label' => 'required|string|max:120',
            'sensor_type' => 'required|string|max:50',
            'current_value' => 'nullable|string|max:100',
            'reference_value' => 'required|numeric',
            'offset_value' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->deviceDataService->storeCalibration(
                $device,
                $device_code,
                $calibrationData,
                session('firebase_user.email') ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Data kalibrasi berhasil disimpan ke database.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update calibration data',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    private function resolveDeviceDetail($device, $device_code): array
    {
        if (!in_array($device, ['aquaviska', 'climeet'], true)) {
            abort(404);
        }

        $deviceDataInfo = $this->deviceDataService->getDeviceByCode($device, $device_code);
        if (empty($deviceDataInfo)) {
            return [[], []];
        }

        $latestReading = $this->deviceDataService->getLatestReading($device, $device_code);
        $deviceDataMonitoring = $this->prepareDeviceMonitoring(['latest' => $latestReading]);
        $deviceDataMonitoring['type'] = $deviceDataInfo['type'] ?? ($device === 'aquaviska' ? 'AQUAVISKA' : 'CLIMEET');

        // Ambil data history untuk chart
        $deviceHistory = $this->deviceDataService->getHistory($device, $device_code);
        $deviceDataMonitoring['chart'] = $this->buildHistoryChartSeries($deviceHistory, $deviceDataMonitoring['type']);

        return [$deviceDataInfo, $deviceDataMonitoring];
    }

    private function prepareDeviceMonitoring(array $deviceDataMonitoring): array
    {
        $latest = $deviceDataMonitoring['latest'] ?? $deviceDataMonitoring;
        $sensors = [];

        foreach ($latest as $sensor => $value) {
            if (in_array($sensor, ['timestamp', 'recorded_at', 'created_at', 'updated_at', 'condition_score', 'status', 'device_code'], true)) {
                continue;
            }

            if (!is_numeric($value)) {
                continue;
            }

            $status = $this->getSensorStatus($sensor, (float) $value);
            $label = $this->getSensorLabel($sensor);
            $unit = $this->getSensorUnit($sensor);

            $sensors[] = [
                'label' => $label,
                'value' => $value,
                'unit' => $unit,
                'status' => $status,
                'pct' => $this->getSensorPct($sensor, (float) $value, $status),
            ];
        }

        $deviceDataMonitoring['sensors'] = $sensors;

        // Hitung condition score dari status sensor
        $statusBySensor = array_column($sensors, 'status');
        $deviceDataMonitoring['calculated_score'] = $this->calculateConditionScore($statusBySensor);

        return $deviceDataMonitoring;
    }

    private function getSensorUnit(string $sensor): string
    {
        $sensorLower = strtolower($sensor);

        return match (true) {
            str_contains($sensorLower, 'temperature') || str_contains($sensorLower, 'suhu') => '°C',
            str_contains($sensorLower, 'ph') => '',
            str_contains($sensorLower, 'turbidity') || str_contains($sensorLower, 'kekeruhan') => 'NTU',
            str_contains($sensorLower, 'do') => 'mg/L',
            str_contains($sensorLower, 'tds') => 'ppm',
            str_contains($sensorLower, 'humidity') || str_contains($sensorLower, 'kelembapan') => '%',
            str_contains($sensorLower, 'tvoc') => 'ppb',
            str_contains($sensorLower, 'co2') || str_contains($sensorLower, 'co₂') => 'ppm',
            str_contains($sensorLower, 'uv') => 'index',
            str_contains($sensorLower, 'angin') => 'm/s',
            str_contains($sensorLower, 'hujan') || str_contains($sensorLower, 'curah') => 'mm',
            str_contains($sensorLower, 'tekanan') => 'hPa',
            default => '',
        };
    }

    private function getSensorPct(string $sensor, float $value, string $status): int
    {
        $sensorLower = strtolower($sensor);

        if ($status === 'bahaya' || $value <= 0) {
            return 0;
        }

        return match (true) {
            str_contains($sensorLower, 'ph') => (int) round(min(max(($value / 14) * 100, 0), 100)),
            str_contains($sensorLower, 'temperature') || str_contains($sensorLower, 'suhu') => (int) round(min(max((($value + 10) / 50) * 100, 0), 100)),
            str_contains($sensorLower, 'do') => (int) round(min(max(($value / 10) * 100, 0), 100)),
            str_contains($sensorLower, 'tds') => (int) round(min(max(($value / 1500) * 100, 0), 100)),
            str_contains($sensorLower, 'turbidity') => (int) round(min(max(($value / 100) * 100, 0), 100)),
            str_contains($sensorLower, 'humidity') => (int) round(min(max($value, 0), 100)),
            str_contains($sensorLower, 'angin') => (int) round(min(max(($value / 30) * 100, 0), 100)),
            str_contains($sensorLower, 'hujan') || str_contains($sensorLower, 'curah') => (int) round(min(max(($value / 150) * 100, 0), 100)),
            str_contains($sensorLower, 'tekanan') => (int) round(min(max((($value - 900) / 150) * 100, 0), 100)),
            default => (int) round(min(max($value, 0), 100)),
        };
    }

    private function buildHistoryChartSeries(array $historyData, string $deviceType): array
    {
        if (empty($historyData)) {
            return $this->buildFallbackChartSeries($deviceType);
        }

        $normalized = [];
        $allSensorKeys = [];

        foreach ($historyData as $row) {
            if (!is_array($row)) {
                continue;
            }

            $timestamp = $this->extractHistoryTimestamp($row);
            if (!$timestamp) {
                continue;
            }

            $values = $this->extractHistoryValues($row);
            $allSensorKeys = array_merge($allSensorKeys, array_keys($values));

            $normalized[] = [
                'timestamp' => $timestamp,
                'label' => $this->formatHistoryLabel($timestamp),
                'values' => $values,
            ];
        }

        if (empty($normalized)) {
            return $this->buildFallbackChartSeries($deviceType);
        }

        usort($normalized, fn($a, $b) => $a['timestamp']->timestamp - $b['timestamp']->timestamp);

        $sensorKeys = array_unique($allSensorKeys);

        // Pilih sensor utama untuk chart
        $prioritySensors = ['ph', 'do', 'temperature', 'tds', 'turbidity'];
        $primarySensor = null;
        foreach ($prioritySensors as $priority) {
            if (in_array($priority, $sensorKeys)) {
                $primarySensor = $priority;
                break;
            }
        }

        if (!$primarySensor && !empty($sensorKeys)) {
            $primarySensor = $sensorKeys[0];
        }

        $ranges = ['6', '12', '24'];
        $result = [];

        foreach ($ranges as $range) {
            $cutoff = now()->subHours((int) $range);
            $window = array_values(array_filter($normalized, function ($row) use ($cutoff) {
                return $row['timestamp']->greaterThanOrEqualTo($cutoff);
            }));

            if (empty($window)) {
                $window = array_slice($normalized, -min(count($normalized), 6));
            }

            $labels = array_map(fn($row) => $row['label'], $window);

            $data = [];
            foreach ($window as $row) {
                $data[] = isset($row['values'][$primarySensor]) ? round($row['values'][$primarySensor], 2) : null;
            }

            $result[$range] = [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $this->getSensorLabel($primarySensor),
                    'data' => $data,
                    'unit' => $this->getSensorUnit($primarySensor),
                ]],
            ];
        }

        return $result;
    }

    private function buildFallbackChartSeries(string $deviceType): array
    {
        if ($deviceType === 'AQUAVISKA') {
            $baseSeries = ['label' => 'pH', 'data' => [7.0, 7.1, 7.1, 7.2, 7.2, 7.3, 7.2], 'unit' => ''];
        } else {
            $baseSeries = ['label' => 'Temperature', 'data' => [28.0, 28.4, 28.8, 29.1, 29.4, 29.7, 29.5], 'unit' => '°C'];
        }

        return [
            '6' => ['labels' => ['-6j', '-5j', '-4j', '-3j', '-2j', '-1j', 'Sekarang'], 'datasets' => [$baseSeries]],
            '12' => ['labels' => ['-12j', '-10j', '-8j', '-6j', '-4j', '-2j', 'Sekarang'], 'datasets' => [$baseSeries]],
            '24' => ['labels' => ['-24j', '-20j', '-16j', '-12j', '-8j', '-4j', 'Sekarang'], 'datasets' => [$baseSeries]],
        ];
    }

    private function extractHistoryTimestamp(array $row): ?\Carbon\Carbon
    {
        foreach (['timestamp', 'created_at', 'time', 'date'] as $key) {
            if (!empty($row[$key])) {
                try {
                    return \Carbon\Carbon::parse($row[$key]);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return null;
    }

    private function formatHistoryLabel(\Carbon\Carbon $timestamp): string
    {
        return $timestamp->format('H:i');
    }

    private function extractHistoryValues(array $row): array
    {
        $values = [];
        $excludeKeys = ['timestamp', 'created_at', 'time', 'date', 'id', 'device_code', 'condition_score', 'status'];

        foreach ($row as $key => $value) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }
            if (is_numeric($value)) {
                $values[$key] = (float) $value;
            }
        }
        return $values;
    }

    private function getSensorStatus(string $sensor, float $value): string
    {
        $sensorLower = strtolower($sensor);

        return match (true) {
            str_contains($sensorLower, 'ph') => match (true) {
                $value < 5.5 || $value > 9.0 => 'bahaya',
                $value < 6.5 || $value > 8.5 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'do') => match (true) {
                $value < 3 => 'bahaya',
                $value < 5 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'temperature') || str_contains($sensorLower, 'suhu') => match (true) {
                $value < 20 || $value > 33 => 'bahaya',
                $value < 25 || $value > 30 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'tds') => match (true) {
                $value > 1000 => 'bahaya',
                $value >= 500 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'turbidity') => match (true) {
                $value > 50 => 'bahaya',
                $value >= 25 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'pm25') => match (true) {
                $value > 150 => 'bahaya',
                $value > 75 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'angin') => match (true) {
                $value > 25 => 'bahaya',
                $value > 15 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'hujan') || str_contains($sensorLower, 'curah') => match (true) {
                $value > 100 => 'bahaya',
                $value > 50 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'tekanan') => match (true) {
                $value < 980 || $value > 1035 => 'bahaya',
                $value < 990 || $value > 1028 => 'waspada',
                default => 'normal'
            },
            str_contains($sensorLower, 'uv') => match (true) {
                $value > 10 => 'bahaya',
                $value > 7 => 'waspada',
                default => 'normal'
            },
            default => 'normal'
        };
    }

    private function calculateConditionScore(array $statusBySensor): int
    {
        $bahayaCount = count(array_filter($statusBySensor, fn($v) => $v === 'bahaya'));
        $waspadaCount = count(array_filter($statusBySensor, fn($v) => $v === 'waspada'));
        $totalSensors = count($statusBySensor);

        if ($totalSensors === 0) {
            return 0;
        }

        $score = 100;
        $score -= ($bahayaCount * 25);
        $score -= ($waspadaCount * 10);

        if ($bahayaCount === 0 && $waspadaCount === 0) {
            $score = min(100, $score + 5);
        }

        return max(0, min(100, $score));
    }

    private function getSensorLabel(string $sensor): string
    {
        $sensorLower = strtolower($sensor);

        return match (true) {
            str_contains($sensorLower, 'ph') => 'pH',
            str_contains($sensorLower, 'do') => 'Dissolved Oxygen',
            str_contains($sensorLower, 'tds') => 'TDS',
            str_contains($sensorLower, 'temperature') || str_contains($sensorLower, 'suhu') => 'Temperature',
            str_contains($sensorLower, 'turbidity') || str_contains($sensorLower, 'kekeruhan') => 'Turbidity',
            str_contains($sensorLower, 'humidity') || str_contains($sensorLower, 'kelembapan') => 'Humidity',
            str_contains($sensorLower, 'tvoc') => 'TVOC',
            str_contains($sensorLower, 'co2') || str_contains($sensorLower, 'co₂') => 'CO₂',
            str_contains($sensorLower, 'uv') => 'UV Index',
            str_contains($sensorLower, 'angin') => 'Wind Speed',
            str_contains($sensorLower, 'hujan') || str_contains($sensorLower, 'curah') => 'Rainfall',
            str_contains($sensorLower, 'tekanan') => 'Pressure',
            default => ucfirst($sensor)
        };
    }

    public function getHistory($device, $device_code): JsonResponse
    {
        try {
            if (!in_array($device, ['aquaviska', 'climeet'], true)) {
                abort(404);
            }

            $dataHistory = $this->deviceDataService->getHistory($device, $device_code);

            $formattedHistory = $this->buildHistoryChartSeries($dataHistory, $device);

            $latestData = $this->deviceDataService->getLatestReading($device, $device_code);

            $latestSensors = $this->prepareDeviceMonitoring(['latest' => $latestData])['sensors'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $formattedHistory,
                'latest' => $latestSensors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch history data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
