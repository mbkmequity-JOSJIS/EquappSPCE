<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqAIService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', '');
        $this->model = env('GROQ_MODEL', 'llama3-8b-8192');
    }

    public function generateRecommendations(array $deviceDataInfo, array $deviceDataMonitoring): array
    {
        // Cek API Key
        if (empty($this->apiKey)) {
            Log::warning('GROQ_API_KEY not set in .env file');
            return $this->fallbackRecommendations($deviceDataMonitoring);
        }

        try {
            $prompt = $this->buildPrompt($deviceDataInfo, $deviceDataMonitoring);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah pakar kualitas air dan budidaya. Berikan respons dalam format JSON valid tanpa teks tambahan.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.5,
                'max_tokens' => 800,
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content', '');
                $parsed = json_decode($content, true);
                
                if (json_last_error() === JSON_ERROR_NONE && !empty($parsed)) {
                    return [
                        'summary' => $parsed['summary'] ?? $this->getDefaultSummary($deviceDataMonitoring),
                        'recommendations' => $parsed['recommendations'] ?? $this->getDefaultRecommendations($deviceDataMonitoring),
                        'mitigation_tips' => $parsed['mitigation_tips'] ?? $this->getDefaultMitigationTips(),
                        'last_analysis' => now()->format('H:i:s'),
                        'status' => 'online',
                        'provider' => 'groq'
                    ];
                }
            }

            Log::warning('Groq API error: ' . $response->body());
            return $this->fallbackRecommendations($deviceDataMonitoring);

        } catch (\Exception $e) {
            Log::error('Groq exception: ' . $e->getMessage());
            return $this->fallbackRecommendations($deviceDataMonitoring);
        }
    }

    private function buildPrompt(array $deviceDataInfo, array $deviceDataMonitoring): string
    {
        $sensors = $deviceDataMonitoring['sensors'] ?? [];
        $sensorText = '';
        
        foreach ($sensors as $sensor) {
            $statusEmoji = match($sensor['status']) {
                'bahaya' => '🔴',
                'waspada' => '🟡', 
                'normal' => '🟢',
                default => '⚪'
            };
            $sensorText .= "{$statusEmoji} {$sensor['label']}: {$sensor['value']} {$sensor['unit']} (status: {$sensor['status']})\n";
        }

        return "Data sensor saat ini:\n{$sensorText}\n\nBerikan rekomendasi mitigasi dalam format JSON berikut:\n{
    \"summary\": \"ringkasan singkat kondisi (max 100 karakter)\",
    \"recommendations\": [
        {
            \"level\": \"danger/warning/good\",
            \"title\": \"judul rekomendasi\",
            \"message\": \"penjelasan kondisi\",
            \"action\": \"tindakan yang harus dilakukan\",
            \"sensor\": \"nama sensor\"
        }
    ],
    \"mitigation_tips\": [
        {
            \"title\": \"judul tips\",
            \"description\": \"deskripsi tips\",
            \"icon\": \"fa-icon\"
        }
    ]
}\n\nGunakan bahasa Indonesia. Hanya output JSON, tanpa teks lain.";
    }

    private function getDefaultSummary(array $deviceDataMonitoring): string
    {
        $sensors = $deviceDataMonitoring['sensors'] ?? [];
        $bahaya = count(array_filter($sensors, fn($s) => $s['status'] === 'bahaya'));
        $waspada = count(array_filter($sensors, fn($s) => $s['status'] === 'waspada'));
        
        if ($bahaya > 0) return "🔴 {$bahaya} sensor kritis! Segera tindakan.";
        if ($waspada > 0) return "🟡 {$waspada} sensor waspada. Perlu monitoring.";
        return "🟢 Semua sensor normal. Pertahankan kondisi.";
    }

    private function getDefaultRecommendations(array $deviceDataMonitoring): array
    {
        $recommendations = [];
        foreach ($deviceDataMonitoring['sensors'] ?? [] as $sensor) {
            if ($sensor['status'] === 'bahaya') {
                $recommendations[] = [
                    'level' => 'danger',
                    'title' => "🔴 {$sensor['label']} KRITIS",
                    'message' => "Nilai: {$sensor['value']} {$sensor['unit']}",
                    'action' => $this->getAction($sensor['label'], 'danger'),
                    'sensor' => $sensor['label'],
                    'priority' => 'high'
                ];
            } elseif ($sensor['status'] === 'waspada') {
                $recommendations[] = [
                    'level' => 'warning',
                    'title' => "🟡 {$sensor['label']} WASPADA",
                    'message' => "Nilai: {$sensor['value']} {$sensor['unit']}",
                    'action' => $this->getAction($sensor['label'], 'warning'),
                    'sensor' => $sensor['label'],
                    'priority' => 'medium'
                ];
            }
        }
        
        if (empty($recommendations)) {
            $recommendations[] = [
                'level' => 'good',
                'title' => '✅ Kondisi Normal',
                'message' => 'Semua parameter dalam batas aman',
                'action' => 'Lakukan monitoring rutin',
                'sensor' => null,
                'priority' => 'low'
            ];
        }
        
        return $recommendations;
    }

    private function getAction(string $sensor, string $level): string
    {
        $actions = [
            'pH' => ['danger' => 'Pengapuran darurat 300-400 kg/ha', 'warning' => 'Aplikasi kapur 150-200 kg/ha'],
            'Dissolved Oxygen' => ['danger' => 'Nyalakan semua aerator, kurangi pakan 50%', 'warning' => 'Aktifkan aerator tambahan'],
            'Temperature' => ['danger' => 'Pasang paranet 75%, tambah kincir', 'warning' => 'Pasang paranet 50%'],
            'TDS' => ['danger' => 'Ganti air 50-70%', 'warning' => 'Ganti air parsial 30%'],
            'Turbidity' => ['danger' => 'Koagulan tawas 15-20 ppm', 'warning' => 'Aplikasi probiotik'],
        ];
        
        return $actions[$sensor][$level] ?? 'Lakukan pengecekan dan monitoring intensif';
    }

    private function getDefaultMitigationTips(): array
    {
        return [
            ['title' => 'Aerasi Optimal', 'description' => 'Nyalakan aerator malam hingga pagi', 'icon' => 'fa-fan'],
            ['title' => 'Manajemen Pakan', 'description' => 'Berikan pakan bertahap 3-4x/hari', 'icon' => 'fa-fish'],
            ['title' => 'Pengapuran Berkala', 'description' => 'Lakukan pengapuran setiap 2-4 minggu', 'icon' => 'fa-flask'],
            ['title' => 'Sirkulasi Air', 'description' => 'Ganti air parsial 10-20% per minggu', 'icon' => 'fa-water']
        ];
    }

    private function fallbackRecommendations(array $deviceDataMonitoring): array
    {
        return [
            'summary' => $this->getDefaultSummary($deviceDataMonitoring),
            'recommendations' => $this->getDefaultRecommendations($deviceDataMonitoring),
            'mitigation_tips' => $this->getDefaultMitigationTips(),
            'last_analysis' => now()->format('H:i:s'),
            'status' => 'fallback',
            'provider' => 'fallback'
        ];
    }
}