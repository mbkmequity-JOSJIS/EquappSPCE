@extends('layouts.app')
@section('title', 'Device Detail')

@section('style')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&display=swap');

        .sensor-card {
            position: relative;
        }

        .sensor-card.is-updated {
            border-color: rgba(34, 197, 94, 0.75) !important;
            box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.28), 0 0 24px rgba(34, 197, 94, 0.2), 0 12px 24px -12px rgba(34, 197, 94, 0.45) !important;
        }

        .sensor-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 0.95rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 10;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.45);
        }

        .sensor-status.good {
            background: rgba(34, 197, 94, 0.16);
            color: #166534;
            border-color: rgba(34, 197, 94, 0.25);
        }

        .sensor-status.medium {
            background: rgba(245, 158, 11, 0.16);
            color: #92400e;
            border-color: rgba(245, 158, 11, 0.25);
        }

        .sensor-status.bad {
            background: rgba(239, 68, 68, 0.16);
            color: #991b1b;
            border-color: rgba(239, 68, 68, 0.25);
        }

        .seven-seg-value {
            font-family: 'Orbitron', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
            font-size: 3rem;
            line-height: 1;
            letter-spacing: 0;
            font-weight: 500;
            color: #0f172a;
            text-shadow: 0 0 1px rgba(15, 23, 42, 0.05);
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .seven-seg-unit {
            font-size: 0.9rem;
            font-weight: 500;
            color: #475569;
            display: block;
            margin-top: 0.25rem;
        }

        .sensor-display {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .sensor-card .sensor-bar-track,
        .sensor-card .sensor-bar-fill {
            display: none;
        }

        /* Modal Custom Styles */
        .modal-transition {
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .modal-transition.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .modal-transition:not(.hidden) {
            opacity: 1;
            visibility: visible;
        }

        /* AI Recommendations Styles */
        .ai-card {
            transition: all 0.3s ease;
        }

        .ai-card:hover {
            transform: translateY(-2px);
        }

        .progress-ring {
            transition: stroke-dashoffset 0.5s ease;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Animasi bounce untuk floating button */
        @keyframes soft-bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .whatsapp-float {
            animation: soft-bounce 2s ease-in-out infinite;
        }

        /* Pulse ring effect */
        .pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background-color: rgba(16, 185, 129, 0.4);
            animation: pulse-ring 1.5s ease-out infinite;
            pointer-events: none;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(1);
                opacity: 0.6;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }

            /* Modal Custom Styles */
            .modal-transition {
                transition: opacity 0.2s ease, visibility 0.2s ease;
            }

            .modal-transition.hidden {
                opacity: 0;
                visibility: hidden;
            }

            .modal-transition:not(.hidden) {
                opacity: 1;
                visibility: visible;
            }

            /* AI Recommendations Styles */
            .ai-card {
                transition: all 0.3s ease;
            }

            .ai-card:hover {
                transform: translateY(-2px);
            }

            .progress-ring {
                transition: stroke-dashoffset 0.5s ease;
            }

            /* Chart Container */
            .chart-container {
                position: relative;
                height: 300px;
                width: 100%;
            }

            /* Animasi bounce untuk floating button */
            @keyframes soft-bounce {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-5px);
                }
            }

            .whatsapp-float {
                animation: soft-bounce 2s ease-in-out infinite;
            }

            /* Pulse ring effect */
            .pulse-ring {
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background-color: rgba(16, 185, 129, 0.4);
                animation: pulse-ring 1.5s ease-out infinite;
                pointer-events: none;
            }

            @keyframes pulse-ring {
                0% {
                    transform: scale(1);
                    opacity: 0.6;
                }

                100% {
                    transform: scale(1.5);
                    opacity: 0;
                }
            }
    </style>
@endsection

@section('content')
    @php
        $deviceType = $deviceDataInfo['type'] ?? ($device === 'aquaviska' ? 'AQUAVISKA' : 'CLIMEET');
        $deviceName = $deviceDataInfo['device_name'] ?? 'Device Detail';
        $deviceAddress = data_get($deviceDataInfo, 'location.address', '-');
        $deviceScore = (int) ($deviceDataInfo['condition_score'] ?? 0);
        $deviceStatus = $deviceDataInfo['status'] ?? 'normal';
        $statusTone = $deviceStatus === 'normal' ? 'good' : ($deviceStatus === 'waspada' ? 'medium' : 'bad');
        $excludedSensorLabels = ['tekanan', 'totalhujan', 'rainfall', 'curah', 'pressure'];
        $sensorCards = array_filter(
            $deviceDataMonitoring['sensors'] ?? [],
            fn($sensor) => !in_array(strtolower(str_replace(' ', '', $sensor['label'])), $excludedSensorLabels, true),
        );
        $detailApi = route('api.device.detail', [
            'device' => $device,
            'id' => $deviceDataInfo['device_code'] ?? request()->route('id'),
        ]);
        $aiRecommendations = $deviceDataMonitoring['ai_recommendations'] ?? [];
    @endphp

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8" data-device-api="{{ $detailApi }}"
            data-device-code="{{ $deviceDataInfo['device_code'] ?? '' }}" data-device-type="{{ $device }}">

            <!-- Breadcrumb -->
            <div class="mb-6 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                <a href="{{ route('device.list', $device) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 font-medium text-emerald-600 transition hover:text-emerald-700">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Daftar Device
                </a>
                <span class="text-slate-300">/</span>
                <span>{{ $deviceName }}</span>
            </div>

            <!-- Hero Section -->
            <section class="relative overflow-hidden rounded-3xl border border-white/60 bg-slate-900 shadow-xl mb-8">
                <div class="absolute inset-0">
                    <div class="h-full w-full bg-cover bg-center opacity-35"
                        style="background-image: url('{{ asset('storage/img_loc/' . ($deviceDataInfo['img_url'] ?? 'images/location-placeholder.jpg')) }}')">
                    </div>
                </div>

                <div class="relative grid gap-6 p-6 sm:p-8 lg:grid-cols-[1.35fr_0.65fr] lg:gap-8 lg:p-10">
                    <div class="flex flex-col justify-end gap-5 text-white">
                        <div
                            class="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur uppercase">
                            <i class="fas fa-{{ $deviceType === 'AQUAVISKA' ? 'water' : 'cloud-sun' }}"></i>
                            {{ $deviceType }}
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">{{ $deviceName }}</h1>
                            <p class="mt-3 flex items-center gap-2 text-sm text-slate-200 sm:text-base">
                                <i class="fas fa-map-marker-alt text-emerald-300"></i>
                                {{ $deviceAddress }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col justify-end items-end gap-4">
                        <div class="rounded-3xl border border-white/15 bg-white/90 p-5 shadow-xl backdrop-blur"
                            id="score-badge">
                            <p class="text-sm font-medium text-slate-500">Skor Kondisi</p>
                            <div class="mt-2 flex items-end gap-2">
                                <span class="text-5xl font-black tracking-tight text-slate-900"
                                    id="condition-score">{{ $deviceScore }}</span>
                                <span class="pb-1 text-lg font-semibold text-slate-500">/100</span>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-white/15 bg-white/10 p-5 text-white backdrop-blur">
                            <p class="text-sm text-slate-200">Live Status</p>
                            <div class="mt-4 rounded-2xl bg-white/10 px-4 py-3 text-sm text-slate-100">
                                <div class="flex items-center gap-3">
                                    <span class="relative flex h-3 w-3" id="status-indicator">
                                        @if($deviceStatus === 'online')
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60 ping-anim"></span>
                                            <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-400 dot-color"></span>
                                        @else
                                            <span class="absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-60 ping-anim hidden"></span>
                                            <span class="relative inline-flex h-3 w-3 rounded-full bg-red-500 dot-color"></span>
                                        @endif
                                    </span>
                                    <span class="text-sm font-semibold" id="report-status">
                                        {{ $deviceStatus === 'online' ? 'Online - Siap dipantau' : 'Offline - Tidak aktif' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sensor Cards Section -->
            <section class="mb-8">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-slate-900">
                            <i class="fas fa-tachometer-alt text-emerald-500"></i>
                            Sensor & Perangkat
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Data real-time dari sensor dan perangkat</p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                        <span class="relative flex h-2.5 w-2.5">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        </span>
                        Live Data
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($sensorCards as $sensor)
                        @php
                            $sensorTone =
                                $sensor['status'] === 'normal'
                                    ? 'good'
                                    : ($sensor['status'] === 'waspada'
                                        ? 'medium'
                                        : 'bad');

                            // Determine icon (keep previous mapping)
                            $icon = match (true) {
                                str_contains(strtolower($sensor['label']), 'temperatur') ||
                                    str_contains(strtolower($sensor['label']), 'suhu')
                                    => 'thermometer-half',
                                str_contains($sensor['label'], 'pH') => 'flask',
                                str_contains(strtolower($sensor['label']), 'turbidity') ||
                                    str_contains(strtolower($sensor['label']), 'kekeruhan')
                                    => 'eye',
                                str_contains(strtolower($sensor['label']), 'dissolved oxygen') ||
                                    str_contains(strtolower($sensor['label']), 'do')
                                    => 'wind',
                                str_contains(strtolower($sensor['label']), 'total dissolved solids') => 'tint',
                                str_contains(strtolower($sensor['label']), 'kelembapan') ||
                                    str_contains(strtolower($sensor['label']), 'kelembaban')
                                    => 'droplet',
                                str_contains(strtolower($sensor['label']), 'tvoc') ||
                                    str_contains(strtolower($sensor['label']), 'co2')
                                    => 'cloud',
                                str_contains(strtolower($sensor['label']), 'uv') => 'sun',
                                str_contains(strtolower($sensor['label']), 'pm25') ||
                                    str_contains(strtolower($sensor['label']), 'pm 25')
                                    => 'smog',
                                str_contains(strtolower($sensor['label']), 'hujan') ||
                                    str_contains(strtolower($sensor['label']), 'rain')
                                    => 'cloud-rain',
                                default => 'microchip',
                            };

                            // Translate label to Indonesian (display only)
                            $labelLower = strtolower($sensor['label']);
                            if (
                                str_contains($labelLower, 'intensitas') ||
                                str_contains($labelLower, 'hujan') ||
                                str_contains($labelLower, 'curah')
                            ) {
                                $displayLabel = 'Intensitas Hujan';
                            } elseif (
                                str_contains($labelLower, 'kelembab') ||
                                str_contains($labelLower, 'kelembapan') ||
                                str_contains($labelLower, 'humidity')
                            ) {
                                $displayLabel = 'Kelembaban';
                            } elseif (
                                str_contains($labelLower, 'wind') ||
                                str_contains($labelLower, 'kecepatan') ||
                                str_contains($labelLower, 'angin') ||
                                str_contains($labelLower, 'wind speed')
                            ) {
                                $displayLabel = 'Kecepatan Angin';
                            } elseif (str_contains($labelLower, 'pm25') || str_contains($labelLower, 'pm 25')) {
                                $displayLabel = 'PM2.5';
                            } elseif (str_contains($labelLower, 'temperatur') || str_contains($labelLower, 'suhu')) {
                                $displayLabel = 'Temperatur';
                            } elseif (
                                str_contains($labelLower, 'kekeruhan') ||
                                str_contains($labelLower, 'turbidity')
                            ) {
                                $displayLabel = 'Kekeruhan';
                            } elseif (
                                str_contains($labelLower, 'dissolved oxygen') ||
                                str_contains($labelLower, 'do')
                            ) {
                                $displayLabel = 'Oksigen Terlarut';
                            } elseif (
                                str_contains($labelLower, 'total dissolved solids') ||
                                str_contains($labelLower, 'tds')
                            ) {
                                $displayLabel = 'TDS';
                            } elseif (str_contains($labelLower, 'uv')) {
                                $displayLabel = 'Indeks UV';
                            } elseif (str_contains($labelLower, 'ph')) {
                                $displayLabel = 'pH';
                            } else {
                                // fallback: title case the original label
                                $displayLabel = ucwords(str_replace(['_', '-'], ' ', $sensor['label']));
                            }


                            // Determine unit defaults if empty
                            $unit = trim((string) ($sensor['unit'] ?? ''));
                            if ($unit === '') {
                                if (
                                    str_contains($labelLower, 'intensitas') ||
                                    str_contains($labelLower, 'hujan') ||
                                    str_contains($labelLower, 'curah')
                                ) {
                                    $unit = 'mm/day';
                                } elseif (
                                    str_contains($labelLower, 'kelembab') ||
                                    str_contains($labelLower, 'kelembapan')
                                ) {
                                    $unit = '%RH';
                                } elseif (str_contains($labelLower, 'pm25') || str_contains($labelLower, 'pm 25')) {
                                    $unit = 'µg/m³';
                                } elseif (
                                    str_contains($labelLower, 'wind') ||
                                    str_contains($labelLower, 'angin') ||
                                    str_contains($labelLower, 'kecepatan')
                                ) {
                                    $unit = 'm/s';
                                }
                            }
                        @endphp

                        <article
                            class="sensor-card overflow-hidden rounded-2xl border border-slate-200 p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg flex flex-col justify-between relative bg-white"
                            data-sensor-label="{{ $sensor['label'] }}">
                            <span class="sensor-status {{ $sensorTone }}">{{ ucfirst($sensor['status']) }}</span>
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-xl text-slate-700">
                                    <i class="fas fa-{{ $icon }}"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-base font-semibold text-slate-900">{{ $displayLabel }}</p>
                                </div>
                            </div>

                            <div class="text-center w-full mt-6">
                                <div class="sensor-display">
                                    <span class="sensor-value seven-seg-value text-[2rem]" data-current-value="{{ $sensor['value'] }}">
                                        {{ $sensor['value'] }}
                                    </span>
                                    <span class="seven-seg-unit">{{ $unit }}</span>
                                </div>
                            </div>

                        </article>
                    @endforeach
                </div>
            </section>

            <!-- Chart and AI Section -->
            <div class="flex flex-wrap gap-6">
                <!-- Chart Section -->
                <div class="rounded-2xl border flex-3 border-slate-200 row-span-1 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <h2 class="flex items-center gap-3 text-xl font-bold text-slate-900">
                                <i class="fas fa-chart-line text-sky-500"></i>
                                Grafik Tren Data Sensor
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">Data historis sensor dalam 24 jam terakhir</p>
                        </div>
                    </div>

                    <!-- Range Buttons -->
                    <div class="mb-4 flex flex-wrap gap-2">
                        <button onclick="changeChartRange('6')" id="range6Btn"
                            class="range-btn rounded-full border px-4 py-2 text-sm font-semibold transition bg-emerald-500 text-white border-emerald-500">
                            6 Jam
                        </button>
                        <button onclick="changeChartRange('12')" id="range12Btn"
                            class="range-btn rounded-full border px-4 py-2 text-sm font-semibold transition bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                            12 Jam
                        </button>
                        <button onclick="changeChartRange('24')" id="range24Btn"
                            class="range-btn rounded-full border px-4 py-2 text-sm font-semibold transition bg-white text-slate-600 border-slate-200 hover:bg-slate-50">
                            24 Jam
                        </button>
                    </div>

                    <!-- Chart Canvas -->
                    <div class="chart-container">
                        <canvas id="sensorChart"></canvas>
                    </div>

                    <!-- Chart Info -->
                    <div class="mt-4 text-center text-xs text-slate-400">
                        <i class="fas fa-chart-line mr-1"></i> Grafik menunjukkan tren nilai sensor dari waktu ke waktu
                    </div>
                </div>
            </div>

            <!-- AI Recommendations Section -->
            <div class="space-y-6 mt-6 flex gap-2">

                <!-- Mitigation Tips -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm w-1/3">
                    <h3 class="text-lg font-bold text-slate-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-shield-halved text-emerald-500"></i>
                        Tips Mitigasi
                    </h3>
                    <div class="grid gap-2">
                        @forelse ($aiRecommendations['mitigation_tips'] ?? [] as $tip)
                            <div class="flex items-start gap-2 p-2 rounded-lg hover:bg-slate-50 transition">
                                <i class="fas {{ $tip['icon'] ?? 'fa-circle-info' }} text-sky-500 mt-0.5 text-sm"></i>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">{{ $tip['title'] ?? 'Tips' }}</p>
                                    <p class="text-xs text-slate-500">{{ $tip['description'] ?? '' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Tips mitigasi akan muncul berdasarkan kondisi sensor.</p>
                        @endforelse
                    </div>
                </div>

                <!-- AI Summary Card -->
                <div
                    class="rounded-2xl border border-slate-200 w-full bg-gradient-to-r from-slate-900 to-slate-800 p-5 shadow-lg relative">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-xl bg-sky-500/20 flex items-center justify-center">
                            <i class="fas fa-robot text-sky-400 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white mb-1">AI Analysis Summary</h3>
                            <p class="text-sm text-slate-300 leading-relaxed" id="ai-summary-text">
                                {{ $aiRecommendations['summary'] ?? 'Menganalisis data sensor...' }}
                                <br>
                                <span class="font-semibold text-white">Recommendations:</span>
                                <br>
                            <div class="space-x-3 flex ">
                                @forelse(($deviceDataMonitoring['ai_recommendations']['recommendations'] ?? []) as $rec)
                                    <div
                                        class="rounded-xl p-3 {{ $rec['level'] === 'danger' ? 'bg-red-50 border border-red-200' : ($rec['level'] === 'warning' ? 'bg-amber-50 border border-amber-200' : 'bg-emerald-50 border border-emerald-200') }}">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg flex items-center justify-center {{ $rec['level'] === 'danger' ? 'bg-red-100' : ($rec['level'] === 'warning' ? 'bg-amber-100' : 'bg-emerald-100') }}">
                                                <i
                                                    class="fas {{ $rec['level'] === 'danger' ? 'fa-circle-exclamation text-red-500' : ($rec['level'] === 'warning' ? 'fa-triangle-exclamation text-amber-500' : 'fa-circle-check text-emerald-500') }}"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-semibold text-sm text-slate-800">
                                                    {{ $rec['title'] ?? 'Perhatian' }}</p>
                                                <p class="text-xs text-slate-600 mt-0.5">{{ $rec['message'] ?? '' }}</p>
                                                <p class="text-xs font-medium text-sky-600 mt-1">
                                                    <i class="fas fa-tools mr-1"></i>
                                                    {{ $rec['action'] ?? 'Lakukan pengecekan' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-slate-400">
                                        <i class="fas fa-check-circle text-3xl mb-2"></i>
                                        <p>Tidak ada alert untuk saat ini</p>
                                    </div>
                                @endforelse
                            </div>
                            <br>
                            <span class="font-semibold text-white">Mitigation Tips:</span>
                            <div class="grid gap-2 text-white">
                                @forelse(($deviceDataMonitoring['ai_recommendations']['mitigation_tips'] ?? []) as $tip)
                                    <div class="flex items-start gap-2 p-2 rounded-lg hover:bg-slate-50 transition">
                                        <i
                                            class="fas {{ $tip['icon'] ?? 'fa-circle-info' }} text-sky-500 mt-0.5 text-sm"></i>
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ $tip['title'] ?? 'Tips' }}
                                            </p>
                                            <p class="text-xs text-white">{{ $tip['description'] ?? '' }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Tips mitigasi akan muncul berdasarkan kondisi sensor.
                                    </p>
                                @endforelse
                            </div>
                            </p>
                            <div class="mt-3 flex items-center gap-2 text-xs text-slate-400 absolute bottom-3 right-3">
                                <i class="far fa-clock"></i>
                                <span>Last analysis: <span
                                        id="last-analysis-time">{{ $aiRecommendations['last_analysis'] ?? now()->format('H:i:s') }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Report -->
                <div class="fixed bottom-6 right-6 z-50 group">
                    <!-- Tooltip -->
                    <div
                        class="absolute bottom-16 right-0 mb-2 w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-0 translate-y-2">
                        <div class="bg-slate-800 text-white text-sm rounded-xl p-3 shadow-lg relative">
                            <div class="absolute -bottom-2 right-4 w-4 h-4 bg-slate-800 transform rotate-45"></div>
                            <div class="flex items-center gap-2">
                                <i class="fab fa-whatsapp text-emerald-400"></i>
                                <span class="font-semibold">Laporkan Masalah</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-1">Klik untuk melaporkan kondisi device ini via WhatsApp
                            </p>
                            <div class="mt-2 text-[10px] text-slate-400">
                                <p><span class="font-semibold">Device:</span> {{ $deviceName }}</p>
                                <p><span class="font-semibold">Lokasi:</span> {{ Str::limit($deviceAddress, 30) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Button -->
                    <a href="https://wa.me/6281234567890?text={{ rawurlencode('Halo Admin, saya ingin melaporkan kondisi device ' . $deviceName . ' di ' . $deviceAddress . ' dengan status ' . $deviceStatus . ' (Skor: ' . $deviceScore . '/100).') }}"
                        target="_blank"
                        class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-500 text-white shadow-lg hover:bg-emerald-600 hover:scale-110 transition-all duration-300 group-hover:shadow-xl"
                        id="whatsappFloatBtn">
                        <i class="fab fa-whatsapp text-2xl"></i>
                        <!-- Badge notification -->
                        <span
                            class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[10px] flex items-center justify-center text-white animate-pulse">
                            !
                        </span>
                    </a>

                    <!-- Label floating saat hover -->
                    <div
                        class="absolute right-16 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-all duration-300 whitespace-nowrap bg-slate-800 text-white text-sm px-3 py-1.5 rounded-lg shadow-md pointer-events-none">
                        <i class="fab fa-whatsapp text-emerald-400 mr-1"></i> Laporkan Masalah
                        <div
                            class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-full w-0 h-0 border-y-4 border-y-transparent border-l-4 border-l-slate-800">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('script')
    <script>
        // Global variables
        let sensorChart;
        let currentRange = '24';
        let deviceType = document.querySelector('[data-device-type]')?.dataset.deviceType || 'aquaviska';
        let deviceCode = document.querySelector('[data-device-api]')?.dataset.deviceCode || '';

        // Initialize Chart
        async function initChart() {
            const ctx = document.getElementById('sensorChart').getContext('2d');

            sensorChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Loading data...',
                        data: [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0'
                            },
                            title: {
                                display: true,
                                text: 'Nilai Sensor',
                                font: {
                                    size: 10
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Waktu',
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });

            await loadChartData(currentRange);
        }

        // Load chart data from API
        async function loadChartData(range) {
            try {
                const response = await fetch(`/device/${deviceType}/${deviceCode}/history?range=${range}`);
                const result = await response.json();

                if (result.success && result.data && result.data[range]) {
                    const chartData = result.data[range];
                    const dataset = chartData.datasets[0];

                    sensorChart.data.labels = chartData.labels;
                    sensorChart.data.datasets[0].label = dataset.label;
                    sensorChart.data.datasets[0].data = dataset.data;
                    sensorChart.data.datasets[0].borderColor = deviceType === 'aquaviska' ? '#06b6d4' : '#f97316';
                    sensorChart.data.datasets[0].backgroundColor = deviceType === 'aquaviska' ?
                        'rgba(6, 182, 212, 0.1)' : 'rgba(249, 115, 22, 0.1)';
                    sensorChart.update();
                } else {
                    console.warn('No chart data available');
                }
            } catch (error) {
                console.error('Error loading chart data:', error);
            }
        }

        // Change chart range
        async function changeChartRange(range) {
            currentRange = range;

            // Update button styles
            document.querySelectorAll('.range-btn').forEach(btn => {
                btn.classList.remove('bg-emerald-500', 'text-white', 'border-emerald-500');
                btn.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
            });

            const activeBtn = document.getElementById(`range${range}Btn`);
            if (activeBtn) {
                activeBtn.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
                activeBtn.classList.add('bg-emerald-500', 'text-white', 'border-emerald-500');
            }

            await loadChartData(range);
        }

        // Real-time polling for device data
        (function() {
            const root = document.querySelector('[data-device-api]');
            if (!root) return;

            const apiUrl = root.dataset.deviceApi;
            const pollInterval = 5000;
            let lastSignature = '';
            const highlightTimers = new WeakMap();

            function statusClass(status) {
                return status === 'normal' ? 'good' : (status === 'waspada' ? 'medium' : 'bad');
            }

            function flashUpdatedCard(card) {
                if (!card) return;

                const existingTimer = highlightTimers.get(card);
                if (existingTimer) {
                    clearTimeout(existingTimer);
                }

                card.classList.add('is-updated');

                const timer = window.setTimeout(() => {
                    card.classList.remove('is-updated');
                    highlightTimers.delete(card);
                }, 1800);

                highlightTimers.set(card, timer);
            }

            async function fetchDeviceDetail() {
                try {
                    const response = await fetch(`${apiUrl}?_=${Date.now()}`, {
                        cache: 'no-store'
                    });
                    if (!response.ok) throw new Error('Failed to fetch device detail');
                    return await response.json();
                } catch (error) {
                    console.warn('Device polling error:', error);
                    return null;
                }
            }

            function updateHeader(data) {
                const scoreValue = document.getElementById('condition-score');
                const reportStatus = document.getElementById('report-status');
                const reportScore = document.getElementById('report-score');

                if (scoreValue && typeof data.condition_score !== 'undefined') scoreValue.textContent = data
                    .condition_score;
                if (reportStatus && data.status) {
                    const isOnline = data.status === 'online';
                    reportStatus.textContent = isOnline ? 'Online - Siap dipantau' : 'Offline - Tidak aktif';
                    
                    const indicator = document.getElementById('status-indicator');
                    if(indicator) {
                        const pingAnim = indicator.querySelector('.ping-anim');
                        const dotColor = indicator.querySelector('.dot-color');
                        
                        if(isOnline) {
                            if(pingAnim) {
                                pingAnim.classList.remove('hidden', 'bg-red-500');
                                pingAnim.classList.add('animate-ping', 'bg-emerald-400');
                            }
                            if(dotColor) {
                                dotColor.classList.remove('bg-red-500');
                                dotColor.classList.add('bg-emerald-400');
                            }
                        } else {
                            if(pingAnim) {
                                pingAnim.classList.remove('animate-ping', 'bg-emerald-400');
                                pingAnim.classList.add('hidden', 'bg-red-500');
                            }
                            if(dotColor) {
                                dotColor.classList.remove('bg-emerald-400');
                                dotColor.classList.add('bg-red-500');
                            }
                        }
                    }
                }
                if (reportScore && typeof data.condition_score !== 'undefined') reportScore.textContent = data
                    .condition_score;

                // Update AI summary if available
                if (data.ai_recommendations) {
                    const summaryEl = document.getElementById('ai-summary-text');
                    const timeEl = document.getElementById('last-analysis-time');
                    if (summaryEl && data.ai_recommendations.summary) summaryEl.textContent = data.ai_recommendations
                        .summary;
                    if (timeEl && data.ai_recommendations.last_analysis) timeEl.textContent = data.ai_recommendations
                        .last_analysis;

                    // Update recommendations
                    const recContainer = document.getElementById('recommendations-container');
                    if (recContainer && data.ai_recommendations.recommendations) {
                        recContainer.innerHTML = data.ai_recommendations.recommendations.map(rec => `
                            <div class="ai-card rounded-xl p-3 ${rec.bgColor || 'bg-slate-50'} border ${rec.borderColor || 'border-slate-200'}">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center ${rec.bgColor || 'bg-slate-100'}">
                                        <i class="fas ${rec.icon || 'fa-info-circle'} ${rec.iconColor || 'text-slate-500'}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-sm text-slate-800">${rec.title || 'Perhatian'}</p>
                                        <p class="text-xs text-slate-600 mt-0.5">${rec.message || ''}</p>
                                        <p class="text-xs font-medium text-sky-600 mt-1">
                                            <i class="fas fa-tools mr-1"></i> ${rec.action || 'Lakukan pengecekan'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    }
                }
            }

            function updateSensorCards(data) {
                if (!Array.isArray(data.sensors)) return;

                data.sensors.forEach((sensor) => {
                    const card = document.querySelector(`[data-sensor-label="${sensor.label}"]`);
                    if (!card) return;

                    const valueEl = card.querySelector('.sensor-value');
                    const statusEl = card.querySelector('.sensor-status');
                    const barEl = card.querySelector('.sensor-bar-fill');
                    const nextValue = sensor.value ?? '—';
                    const previousValue = valueEl?.dataset.currentValue ?? valueEl?.textContent?.trim() ?? '';
                    const valueChanged = String(previousValue).trim() !== String(nextValue).trim();

                    if (valueEl) {
                        valueEl.textContent = nextValue;
                        valueEl.dataset.currentValue = String(nextValue);
                    }
                    if (statusEl) {
                        const tone = statusClass(sensor.status);
                        statusEl.textContent = sensor.status.charAt(0).toUpperCase() + sensor.status.slice(1);
                        statusEl.className = `sensor-status ${tone}`;
                    }
                    if (barEl) {
                        const tone = statusClass(sensor.status);
                        barEl.className = `sensor-bar-fill ${tone}`;
                        barEl.style.width = `${sensor.pct}%`;
                    }

                    if (valueChanged) {
                        flashUpdatedCard(card);
                    }
                });
            }

            async function refresh() {
                const newData = await fetchDeviceDetail();
                if (!newData) return;

                const signature = JSON.stringify(newData);
                if (signature === lastSignature) return;

                updateHeader(newData);
                updateSensorCards(newData);
                lastSignature = signature;
            }

            refresh();
            setInterval(refresh, pollInterval);
        })();

        function showToast(icon, title, text) {
            if (!window.Swal) {
                alert(text || title);
                return;
            }
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon,
                title,
                text
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initChart();
        });

        // Optional: Tambahkan efek tambahan saat button di-click
        document.addEventListener('DOMContentLoaded', function() {
            const waButton = document.getElementById('whatsappFloatBtn');
            if (waButton) {
                waButton.addEventListener('click', function(e) {
                    // Tambahkan efek klik
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);

                    // Optional: Track click event
                    console.log('WhatsApp report clicked for device: {{ $deviceName }}');
                });
            }
        });
    </script>
@endsection
