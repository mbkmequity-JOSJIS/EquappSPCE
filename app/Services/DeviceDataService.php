<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DeviceDataService
{
    public function getDevicesByModule(string $module): array
    {
        $devices = DB::table('devices')
            ->where('device_type', $module)
            ->orderBy('device_name')
            ->get();

        $deviceMap = [];

        foreach ($devices as $device) {
            $deviceMap[$device->device_code] = $this->buildDeviceRecord((array) $device);
        }

        return $deviceMap;
    }

    public function getDeviceByCode(string $module, string $deviceCode): array
    {
        $device = DB::table('devices')
            ->where('device_type', $module)
            ->where('device_code', $deviceCode)
            ->first();

        if (!$device) {
            return [];
        }

        return $this->buildDeviceRecord((array) $device);
    }

    public function getLatestReading(string $module, string $deviceCode): array
    {
        $table = $this->getReadingTable($module);

        if (!$table) {
            return [];
        }

        $reading = DB::table($table)
            ->where('device_code', $deviceCode)
            ->orderByDesc('recorded_at')
            ->orderByDesc('created_at')
            ->first();

        return $reading ? $this->normalizeReading((array) $reading, $module) : [];
    }

    public function getHistory(string $module, string $deviceCode): array
    {
        $table = $this->getReadingTable($module);

        if (!$table) {
            return [];
        }

        return DB::table($table)
            ->where('device_code', $deviceCode)
            ->orderBy('recorded_at')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($row) => $this->normalizeReading((array) $row, $module, true))
            ->all();
    }

    public function buildSensorSummary(array $latestData): array
    {
        $sensors = [];

        foreach ($latestData as $sensor => $value) {
            if ($sensor === 'recorded_at' || $sensor === 'latest_recorded_at' || $sensor === 'device_code') {
                continue;
            }

            if (!is_numeric($value)) {
                continue;
            }

            $status = $this->getSensorStatus($sensor, (float) $value);

            $sensors[] = [
                'label' => $this->getSensorLabel($sensor),
                'value' => round((float) $value, 2),
                'unit' => $this->getSensorUnit($sensor),
                'status' => $status,
                'pct' => $this->getSensorPct($sensor, (float) $value, $status),
            ];
        }

        return $sensors;
    }

    public function calculateConditionScore(array $statusBySensor): int
    {
        $bahayaCount = count(array_filter($statusBySensor, fn ($value) => $value === 'bahaya'));
        $waspadaCount = count(array_filter($statusBySensor, fn ($value) => $value === 'waspada'));
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

    public function buildRecommendation(string $module, array $sensors, int $conditionScore): string
    {
        $bahaya = count(array_filter($sensors, fn ($sensor) => ($sensor['status'] ?? null) === 'bahaya'));
        $waspada = count(array_filter($sensors, fn ($sensor) => ($sensor['status'] ?? null) === 'waspada'));

        if ($bahaya > 0) {
            return $module === 'aquaviska'
                ? 'Segera cek kualitas air, lakukan inspeksi probe, dan siapkan tindakan korektif.'
                : 'Segera periksa kondisi cuaca dan pastikan perangkat terlindungi dari gangguan lingkungan.';
        }

        if ($waspada > 0 || $conditionScore < 70) {
            return $module === 'aquaviska'
                ? 'Ada parameter kualitas air yang mulai menyimpang. Lakukan monitoring berkala dan kalibrasi bila diperlukan.'
                : 'Ada parameter cuaca yang mulai menyimpang. Lanjutkan pemantauan dan cek kestabilan sensor.';
        }

        return $module === 'aquaviska'
            ? 'Kondisi kualitas air stabil. Pertahankan pemantauan rutin.'
            : 'Kondisi cuaca dan sensor stabil. Pertahankan monitoring rutin.';
    }

    public function buildDeviceRecord(array $device): array
    {
        $module = $device['device_type'] ?? 'aquaviska';
        $latestData = $this->getLatestReading($module, $device['device_code']);
        $sensors = $this->buildSensorSummary($latestData);
        $statusBySensor = array_column($sensors, 'status');
        $conditionScore = $this->calculateConditionScore($statusBySensor);

        return [
            'id' => $device['device_code'],
            'node' => $device['device_code'],
            'device_code' => $device['device_code'],
            'device_name' => $device['device_name'] ?? 'Unknown Device',
            'type' => strtoupper($module),
            'device_type' => $module,
            'category' => $device['category'] ?? null,
            'img_url' => $device['img_url'] ?? null,
            'status' => $device['status'] ?? 'offline',
            'condition_score' => $conditionScore,
            'recommendation' => $this->buildRecommendation($module, $sensors, $conditionScore),
            'location' => [
                'name' => $device['city'] ?? $device['device_name'] ?? '-',
                'address' => $device['address'] ?? '-',
                'city' => $device['city'] ?? '-',
                'province' => $device['province'] ?? '-',
                'latitude' => isset($device['latitude']) ? (float) $device['latitude'] : null,
                'longitude' => isset($device['longitude']) ? (float) $device['longitude'] : null,
            ],
            'is_preview' => (bool) ($device['is_preview'] ?? false),
            'is_deleted' => (bool) ($device['is_deleted'] ?? false),
            'latest_data' => $latestData,
            'sensors' => $sensors,
            'latest_recorded_at' => $latestData['recorded_at'] ?? null,
        ];
    }

    public function storeCalibration(string $module, string $deviceCode, array $data, ?string $calibratedBy = null): void
    {
        $calibrationColumnMap = [
            'aquaviska' => [
                'pH' => 'calib_ph',
                'DO' => 'calib_do',
                'Suhu' => 'calib_temperature',
                'TDS' => 'calib_tds',
                'Kekeruhan' => 'calib_turbidity',
                'Temperature' => 'calib_temperature',
            ],
            'climeet' => [
                'Suhu' => 'calib_temperature',
                'Kelembapan' => 'calib_kelembaban',
                'TVOC' => 'calib_turbidity',
                'CO2' => 'calib_pm25',
                'UV' => 'calib_uv_index',
                'Angin' => 'calib_kecepatan_angin',
                'Curah Hujan' => 'calib_intensitas_hujan',
                'PM25' => 'calib_pm25',
            ],
        ];

        $sensorType = $data['sensor_type'] ?? '';
        $targetColumn = $calibrationColumnMap[$module][$sensorType] ?? null;

        $payload = [
            'device_code' => $deviceCode,
            'calibrated_at' => now(),
            'calibrated_by' => $calibratedBy,
            'notes' => $this->buildCalibrationNotes($data),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($targetColumn) {
            $payload[$targetColumn] = $this->parseNumericValue($data['reference_value'] ?? null);
        }

        DB::table('calibrations')->insert($payload);
    }

    private function buildCalibrationNotes(array $data): ?string
    {
        $notes = [
            'sensor_type' => $data['sensor_type'] ?? null,
            'current_value' => $data['current_value'] ?? null,
            'reference_value' => $data['reference_value'] ?? null,
            'offset_value' => $data['offset_value'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        $filtered = array_filter($notes, fn ($value) => $value !== null && $value !== '');

        return empty($filtered) ? null : json_encode($filtered, JSON_UNESCAPED_UNICODE);
    }

    private function getReadingTable(string $module): ?string
    {
        return match ($module) {
            'aquaviska' => 'water_quality_latest',
            'climeet' => 'weather_station_latest',
            default => null,
        };
    }

    private function normalizeReading(array $row, string $module, bool $keepMeta = false): array
    {
        $normalized = match ($module) {
            'aquaviska' => [
                'do' => $row['do_value'] ?? null,
                'ph' => $row['ph_value'] ?? null,
                'tds' => $row['tds_value'] ?? null,
                'turbidity' => $row['turbidity_value'] ?? null,
                'temperature' => $row['temperature_value'] ?? null,
            ],
            'climeet' => [
                'intensitasHujan' => $row['intensitas_hujan'] ?? null,
                'kecepatanAngin' => $row['kecepatan_angin'] ?? null,
                'kelembaban' => $row['kelembaban'] ?? null,
                'pm25' => $row['pm25'] ?? null,
                'tekanan' => $row['tekanan'] ?? null,
                'temperature' => $row['temperature'] ?? null,
                'totalHujan' => $row['total_hujan'] ?? null,
                'uvIndex' => $row['uv_index'] ?? null,
            ],
            default => [],
        };

        if ($keepMeta) {
            $normalized['recorded_at'] = $row['recorded_at'] ?? null;
            $normalized['created_at'] = $row['created_at'] ?? null;
            $normalized['updated_at'] = $row['updated_at'] ?? null;
            $normalized['device_code'] = $row['device_code'] ?? null;
        }

        return array_filter($normalized, fn ($value) => $value !== null && $value !== '');
    }

    private function getSensorStatus(string $sensor, float $value): string
    {
        $sensorLower = strtolower($sensor);

        return match (true) {
            str_contains($sensorLower, 'ph') => match (true) {
                $value < 5.5 || $value > 9.0 => 'bahaya',
                $value < 6.5 || $value > 8.5 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'do') => match (true) {
                $value < 3 => 'bahaya',
                $value < 5 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'temperature') || str_contains($sensorLower, 'suhu') => match (true) {
                $value < 20 || $value > 33 => 'bahaya',
                $value < 25 || $value > 30 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'tds') => match (true) {
                $value > 1000 => 'bahaya',
                $value >= 500 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'turbidity') => match (true) {
                $value > 50 => 'bahaya',
                $value >= 25 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'humidity') || str_contains($sensorLower, 'kelembaban') => match (true) {
                $value < 35 || $value > 95 => 'bahaya',
                $value < 45 || $value > 85 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'pm25') => match (true) {
                $value > 150 => 'bahaya',
                $value > 75 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'uv') => match (true) {
                $value > 10 => 'bahaya',
                $value > 7 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'angin') => match (true) {
                $value > 25 => 'bahaya',
                $value > 15 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'hujan') || str_contains($sensorLower, 'curah') => match (true) {
                $value > 100 => 'bahaya',
                $value > 50 => 'waspada',
                default => 'normal',
            },
            str_contains($sensorLower, 'tekanan') => match (true) {
                $value < 980 || $value > 1035 => 'bahaya',
                $value < 990 || $value > 1028 => 'waspada',
                default => 'normal',
            },
            default => 'normal',
        };
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
            str_contains($sensorLower, 'humidity') || str_contains($sensorLower, 'kelembaban') => '%',
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

    private function getSensorLabel(string $sensor): string
    {
        $sensorLower = strtolower($sensor);

        return match (true) {
            str_contains($sensorLower, 'ph') => 'pH',
            str_contains($sensorLower, 'do') => 'Dissolved Oxygen',
            str_contains($sensorLower, 'tds') => 'TDS',
            str_contains($sensorLower, 'temperature') || str_contains($sensorLower, 'suhu') => 'Temperature',
            str_contains($sensorLower, 'turbidity') || str_contains($sensorLower, 'kekeruhan') => 'Turbidity',
            str_contains($sensorLower, 'humidity') || str_contains($sensorLower, 'kelembaban') => 'Humidity',
            str_contains($sensorLower, 'tvoc') => 'TVOC',
            str_contains($sensorLower, 'co2') || str_contains($sensorLower, 'co₂') => 'CO₂',
            str_contains($sensorLower, 'uv') => 'UV Index',
            str_contains($sensorLower, 'angin') => 'Wind Speed',
            str_contains($sensorLower, 'hujan') || str_contains($sensorLower, 'curah') => 'Rainfall',
            str_contains($sensorLower, 'tekanan') => 'Pressure',
            default => ucfirst($sensor),
        };
    }

    private function parseNumericValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $normalized = preg_replace('/[^0-9,\.\-]/', '', $value);

            if ($normalized === '' || $normalized === null) {
                return null;
            }

            if (str_contains($normalized, ',') && !str_contains($normalized, '.')) {
                $normalized = str_replace(',', '.', $normalized);
            }

            return is_numeric($normalized) ? (float) $normalized : null;
        }

        return null;
    }
}