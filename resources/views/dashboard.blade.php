@extends('layouts.app')
@section('title', 'Dashboard Module')

@php
    $activeModule = $dashboard['key'];
    $isAqua = $activeModule === 'aquaviska';
    $surfaceClass = $isAqua
        ? 'bg-gradient-to-br from-cyan-50 via-white to-sky-50'
        : 'bg-gradient-to-br from-orange-50 via-white to-amber-50';
    $badgeClass = $isAqua
        ? 'border-cyan-200 bg-cyan-50 text-cyan-700'
        : 'border-orange-200 bg-orange-50 text-orange-700';
    $buttonClass = $isAqua
        ? 'border-cyan-200 bg-cyan-50 text-cyan-800 hover:bg-cyan-100'
        : 'border-orange-200 bg-orange-50 text-orange-800 hover:bg-orange-100';
    $iconBoxClass = $isAqua ? 'bg-cyan-50 text-cyan-700' : 'bg-orange-50 text-orange-700';
    $sectionTitleClass = $isAqua ? 'text-cyan-700' : 'text-orange-700';
    $pillClass = $isAqua
        ? 'border-cyan-200 bg-cyan-50 text-cyan-700'
        : 'border-orange-200 bg-orange-50 text-orange-700';
    $graphClass = $isAqua ? 'from-cyan-400 to-cyan-600' : 'from-orange-400 to-orange-600';
    $chartLineColor = $isAqua ? '#06b6d4' : '#f97316';
@endphp

@section('style')
    <style>
        /* Chart Container */
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }

        /* Sensor preview card hover effect */
        .sensor-preview-card {
            transition: all 0.3s ease;
        }

        .sensor-preview-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        /* Mini progress bar animation */
        .sensor-progress-bar {
            transition: width 0.5s ease;
        }

        /* Tooltip custom */
        .chart-tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            pointer-events: none;
            z-index: 10;
            white-space: nowrap;
        }

        /* Custom scrollbar untuk sensor list */
        .sensor-grid {
            max-height: 280px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .sensor-grid::-webkit-scrollbar {
            width: 4px;
        }

        .sensor-grid::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .sensor-grid::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br {{ $surfaceClass }} text-slate-800">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-32 -top-24 h-72 w-72 rounded-full bg-cyan-300/25 blur-3xl"></div>
            <div class="absolute -right-32 top-40 h-80 w-80 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-32 left-1/3 h-80 w-80 rounded-full bg-amber-200/25 blur-3xl"></div>
        </div>

        <div class="relative mx-auto px-4 sm:px-6 py-6 lg:px-8 lg:py-8 max-w-7xl">
            <!-- Header Card -->
            <div
                class="mb-6 rounded-2xl sm:rounded-3xl border border-slate-200 bg-white/90 p-4 sm:p-6 shadow-xl shadow-slate-200/70 backdrop-blur-xl">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <h1 class="mt-3 sm:mt-4 text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-slate-900">
                            Dashboard <span
                                class="uppercase tracking-widest {{ $dashboard['label'] == 'Climeet' ? 'bg-orange-400' : 'bg-cyan-500' }} text-white shadow-md px-2 py-1 rounded-md">{{ $dashboard['label'] }}</span>
                        </h1>
                        <p
                            class="mt-2 sm:mt-4 max-w-2xl text-xs sm:text-sm lg:text-base leading-6 sm:leading-7 text-slate-600">
                            {{ $dashboard['subtitle'] }}
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <a href="#wilayah"
                            class="rounded-xl sm:rounded-2xl border px-3 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm font-semibold transition hover:-translate-y-0.5 {{ $buttonClass }} text-center">
                            <i class="fa-solid fa-map-location-dot mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Grafik</span> Wilayah
                        </a>
                        <a href="#alat"
                            class="rounded-xl sm:rounded-2xl border border-slate-200 bg-white px-3 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50 text-center">
                            <i class="fa-solid fa-microchip mr-1 sm:mr-2"></i>
                            Status Alat
                        </a>
                        <a href="#mitigasi"
                            class="rounded-xl sm:rounded-2xl border border-amber-200 bg-amber-50 px-3 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm font-semibold text-amber-800 transition hover:-translate-y-0.5 hover:bg-amber-100 text-center">
                            <i class="fa-solid fa-shield-halved mr-1 sm:mr-2"></i>
                            Mitigasi AI
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-3 sm:gap-4 grid-cols-2 md:grid-cols-4">
                @foreach ($dashboard['summary'] as $index => $stat)
                    <div
                        class="rounded-xl sm:rounded-2xl border border-slate-200 bg-white/90 p-3 sm:p-5 shadow-lg shadow-slate-200/70 backdrop-blur-sm">
                        <div class="flex items-center justify-between gap-2 sm:gap-3">
                            <p class="text-xs sm:text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                            <span
                                class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-xl sm:rounded-2xl {{ $iconBoxClass }}">
                                <i class="{{ $dashboard['summaryIcons'][$index] }} text-sm sm:text-base"></i>
                            </span>
                        </div>
                        <div class="mt-2 sm:mt-3 flex items-end justify-between gap-3 sm:gap-4">
                            <div class="text-2xl sm:text-4xl font-bold tracking-tight text-slate-900">{{ $stat['value'] }}
                            </div>
                            <span
                                class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-semibold text-slate-600 whitespace-nowrap">Real
                                time</span>
                        </div>
                        @if (isset($stat['note']))
                            <p class="text-[10px] sm:text-xs text-slate-400 mt-1 sm:mt-2">{{ $stat['note'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 sm:mt-8 grid gap-5 sm:gap-6 xl:grid-cols-2">
                <!-- GRAFIK WILAYAH - LINE CHART -->
                <section id="wilayah"
                    class="rounded-xl sm:rounded-3xl border  border-slate-200 bg-white/90 p-4 sm:p-6 shadow-xl shadow-slate-200/70 backdrop-blur-sm row">
                    <div
                        class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 border-b border-slate-200 pb-4 sm:pb-5">
                        <div>
                            <div
                                class="inline-flex rounded-full {{ $pillClass }} px-2 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-semibold uppercase tracking-[0.22em]">
                                <i class="{{ $dashboard['icon'] }} mr-1 sm:mr-2"></i>
                                {{ $dashboard['label'] }} aktif
                            </div>
                            <h2 class="mt-2 sm:mt-3 text-xl sm:text-2xl font-bold text-slate-900">Grafik Data Wilayah</h2>
                            <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-slate-600">Perbandingan kondisi dari beberapa
                                wilayah dalam bentuk grafik tren.</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="setChartRange('week')"
                                class="chart-range-btn px-2 py-1 sm:px-3 sm:py-1 text-[10px] sm:text-xs rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition">1
                                Minggu</button>
                            <button onclick="setChartRange('month')"
                                class="chart-range-btn px-2 py-1 sm:px-3 sm:py-1 text-[10px] sm:text-xs rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition">1
                                Bulan</button>
                            <button onclick="setChartRange('year')"
                                class="chart-range-btn px-2 py-1 sm:px-3 sm:py-1 text-[10px] sm:text-xs rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition">1
                                Tahun</button>
                        </div>
                    </div>

                    <!-- Canvas untuk Line Chart -->
                    <div class="chart-container mt-4">
                        <canvas id="regionChart"></canvas>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap justify-center gap-3 sm:gap-4 mt-4 pt-3 border-t border-slate-100">
                        @foreach ($dashboard['regions'] as $index => $region)
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full"
                                    style="background: {{ ['#06b6d4', '#3b82f6', '#8b5cf6', '#10b981'][$index % 4] }}"></span>
                                <span class="text-[10px] sm:text-xs text-slate-600">{{ $region['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <aside class="space-y-5 sm:space-y-6">
                    <!-- INFORMASI SENSOR PER WILAYAH - CAROUSEL DENGAN PREVIEW SENSOR DETAIL -->
                    <section
                        class="rounded-xl sm:rounded-3xl border border-slate-200 bg-white/90 p-4 sm:p-6 shadow-xl shadow-slate-200/70 backdrop-blur-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p
                                    class="text-xs sm:text-sm font-semibold uppercase tracking-[0.2em] {{ $sectionTitleClass }}">
                                    <i class="fa-solid fa-chart-line mr-1 sm:mr-2"></i>Data Sensor Wilayah
                                </p>
                                <h3 class="mt-1 sm:mt-2 text-lg sm:text-2xl font-bold text-slate-900">Informasi Sensor per
                                    Wilayah</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-xs font-semibold text-slate-600">
                                    <i class="fa-regular fa-clock mr-1"></i><span id="slideTimer">8</span> detik
                                </span>
                                <button onclick="toggleCarousel()"
                                    class="rounded-full border border-slate-200 bg-white p-1.5 sm:p-2 hover:bg-slate-50 transition">
                                    <i id="playPauseIcon" class="fa-solid fa-pause text-slate-600 text-xs sm:text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 sm:mt-5 relative min-h-[450px]">
                            @foreach ($dashboard['sensorSlides'] ?? [] as $index => $slide)
                                <div class="sensor-slide absolute inset-0 rounded-xl sm:rounded-2xl border border-slate-200 bg-gradient-to-br {{ $slide['bgClass'] ?? 'from-white to-slate-50' }} p-4 sm:p-5 transition-all duration-500 shadow-sm"
                                    data-slide-index="{{ $index }}"
                                    @if ($index !== 0) style="opacity: 0; transform: translateX(18px); pointer-events: none;"
                                    @else style="opacity: 1; transform: translateX(0);" @endif>

                                    <!-- Header Wilayah -->
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl {{ $slide['badgeClass'] }} flex items-center justify-center">
                                                <i class="{{ $slide['icon'] }} text-base sm:text-xl"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-base sm:text-lg font-bold text-slate-900">
                                                    {{ $slide['title'] }}</h4>
                                                <p class="text-[10px] sm:text-xs text-slate-500">{{ $slide['subtitle'] }}
                                                </p>
                                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                                    <span
                                                        class="inline-flex items-center gap-1 text-[9px] sm:text-[10px] text-slate-400">
                                                        <i class="fa-solid fa-location-dot"></i>
                                                        {{ $slide['location'] ?? 'Indonesia' }}
                                                    </span>
                                                    <span
                                                        class="inline-flex items-center gap-1 text-[9px] sm:text-[10px] text-slate-400">
                                                        <i class="fa-solid fa-microchip"></i> {{ $slide['deviceCount'] }}
                                                        devices
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="flex items-center gap-1">
                                                <span
                                                    class="text-2xl sm:text-3xl font-bold {{ $slide['scoreColor'] }}">{{ $slide['score'] }}</span>
                                                <span class="text-[10px] sm:text-xs text-slate-400">/100</span>
                                            </div>
                                            <div class="text-[8px] sm:text-[10px] text-slate-400 mt-1">Health Score</div>
                                        </div>
                                    </div>

                                    <!-- Data Sensor Grid - Preview Sensor Detail -->
                                    <div class="mt-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] sm:text-xs font-semibold text-slate-600">
                                                <i class="fa-solid fa-waveform mr-1"></i>Parameter Sensor Real-time
                                            </span>
                                            <span class="text-[8px] sm:text-[10px] text-slate-400">Nilai & Status</span>
                                        </div>

                                        <!-- Grid Sensor dengan Status -->
                                        <div class="grid grid-cols-2 gap-2 sm:gap-3 sensor-grid">
                                            @foreach ($slide['sensors'] as $sensor)
                                                <div
                                                    class="sensor-preview-card bg-white rounded-lg sm:rounded-xl p-2 sm:p-3 border border-slate-100 shadow-sm">
                                                    <div class="flex items-center justify-between mb-1.5">
                                                        <div class="flex items-center gap-1.5">
                                                            <i
                                                                class="fas {{ $sensor['icon'] ?? 'fa-microchip' }} text-[10px] sm:text-sm {{ $sensor['iconColor'] ?? 'text-slate-500' }}"></i>
                                                            <span
                                                                class="text-[9px] sm:text-xs font-medium text-slate-600">{{ $sensor['label'] }}</span>
                                                        </div>
                                                        <span
                                                            class="text-xs sm:text-sm font-bold text-slate-800">{{ $sensor['value'] }}
                                                            {{ $sensor['unit'] ?? '' }}</span>
                                                    </div>
                                                    <div class="relative mb-1.5">
                                                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                                            <div class="sensor-progress-bar h-full rounded-full {{ $sensor['barColor'] ?? 'bg-slate-400' }}"
                                                                style="width: {{ $sensor['percentage'] ?? 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-[7px] sm:text-[9px] text-slate-400">Normal
                                                            range</span>
                                                        <span
                                                            class="text-[7px] sm:text-[9px] font-medium {{ $sensor['percentage'] >= 70 ? 'text-emerald-600' : ($sensor['percentage'] >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                                                            <i
                                                                class="fa-solid {{ $sensor['percentage'] >= 70 ? 'fa-circle-check' : ($sensor['percentage'] >= 50 ? 'fa-triangle-exclamation' : 'fa-circle-exclamation') }} mr-0.5"></i>
                                                            {{ $sensor['status'] ?? ($sensor['percentage'] >= 70 ? 'Baik' : ($sensor['percentage'] >= 50 ? 'Waspada' : 'Kritis')) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Additional Info Footer -->
                                    <div
                                        class="mt-3 pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-[8px] sm:text-[10px] text-slate-400">
                                        <span><i class="fa-regular fa-clock mr-1"></i>Last update:
                                            {{ $slide['lastUpdate'] }}</span>
                                        <span><i class="fa-solid fa-chart-line mr-1"></i>Live monitoring aktif</span>
                                        <span><i class="fa-solid fa-sync-alt mr-1"></i>Real-time</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Dots Navigation -->
                        <div class="mt-4 flex items-center justify-center gap-1.5 sm:gap-2" id="slide-dots">
                            @foreach ($dashboard['sensorSlides'] ?? [] as $index => $slide)
                                <button type="button"
                                    class="slide-dot h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-slate-300 transition-all duration-300"
                                    data-dot-index="{{ $index }}"
                                    aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                    </section>
                </aside>
            </div>

            <!-- Navigation Footer -->
            <section
                class="mt-6 sm:mt-8 rounded-xl sm:rounded-3xl border border-slate-200 bg-white/90 p-4 sm:p-6 shadow-xl shadow-slate-200/70 backdrop-blur-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="text-center md:text-left">
                        <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.2em] {{ $sectionTitleClass }}">
                            <i class="fa-solid fa-compass mr-1 sm:mr-2"></i>Fokus halaman
                        </p>
                        <h3 class="mt-1 sm:mt-2 text-lg sm:text-2xl font-bold text-slate-900">Buka Menu Perangkat</h3>
                        <p class="mt-1 sm:mt-2 max-w-3xl text-xs sm:text-sm leading-5 sm:leading-6 text-slate-600">
                            Dashboard ini mengikuti menu yang sedang dibuka. Pilih perangkat untuk melihat detail
                            monitoring.
                        </p>
                    </div>
                    <a href="/modul/devices/{{ $activeModule }}"
                        class="w-1/2 h-full px-4 py-10 {{ $dashboard['label'] === 'AquaViska' ? 'bg-sky-400/20 hover:bg-sky-400/50' : 'bg-orange-400/20 hover:bg-orange-400/50' }}  font-semibold rounded-3xl flex items-center justify-center transition-all duration-300 hover:-translate-y-2">
                        <div
                            class=" flex items-center justify-center text-white font-semibold tracking-wider text-5xl rounded-3xl">
                            <i class="fa-solid fa-microchip mr-4"></i>
                            @if ($dashboard['label'] === 'Climeet')
                                <i class="fa-solid fa-cloud-sun mr-4"></i>
                            @else
                                <i class="fa-solid fa-water mr-4"></i>
                            @endif
                            <i class="fa-solid fa-location-arrow mr-4"></i>
                            <i class="fa-solid fa-shield-halved mr-4"></i>
                            <span class="uppercase">{{ $dashboard['label'] }}</span>
                        </div>

                    </a>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        let carouselInterval;
        let currentIndex = 0;
        let isPlaying = true;
        let currentTimerSeconds = 8;
        let timerInterval;
        let regionChart;

        // Data untuk chart wilayah dari controller
        const chartLabels = @json($dashboard['chartLabels'] ?? []);
        const chartDatasets = @json($dashboard['chartDatasets'] ?? []);

        // Inisialisasi Line Chart
        function initRegionChart() {
            const ctx = document.getElementById('regionChart').getContext('2d');

            regionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: chartDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 10,
                                usePointStyle: true,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleColor: '#fff',
                            bodyColor: '#e2e8f0',
                            padding: 8,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 105,
                            grid: {
                                color: '#e2e8f0'
                            },
                            title: {
                                display: true,
                                text: 'Skor Kondisi (%)',
                                font: {
                                    size: 10
                                }
                            },
                            ticks: {
                                stepSize: 20,
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Periode',
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.3,
                            borderWidth: 2
                        },
                        point: {
                            radius: 3,
                            hoverRadius: 5,
                            hitRadius: 10
                        }
                    },
                    layout: {
                        padding: {
                            top: 10,
                            bottom: 10,
                            left: 5,
                            right: 5
                        }
                    }
                }
            });
        }

        function setChartRange(range) {
            const btn = event.target;
            document.querySelectorAll('.chart-range-btn').forEach(btn => {
                btn.classList.remove('bg-{{ $isAqua ? 'cyan' : 'orange' }}-100',
                    'border-{{ $isAqua ? 'cyan' : 'orange' }}-300');
                btn.classList.add('bg-white');
            });
            btn.classList.add('bg-{{ $isAqua ? 'cyan' : 'orange' }}-100', 'border-{{ $isAqua ? 'cyan' : 'orange' }}-300');

            // Update chart berdasarkan range
            if (range === 'week') {
                regionChart.data.labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            } else if (range === 'month') {
                regionChart.data.labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
            } else {
                regionChart.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov',
                    'Des'
                ];
            }
            regionChart.update();
        }

        // Carousel Functions
        function showSlide(index) {
            const slides = document.querySelectorAll('.sensor-slide');
            const dots = document.querySelectorAll('.slide-dot');

            if (!slides.length) return;

            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === index;
                slide.style.opacity = active ? '1' : '0';
                slide.style.transform = active ? 'translateX(0)' : 'translateX(18px)';
                slide.style.pointerEvents = active ? 'auto' : 'none';
            });

            dots.forEach((dot, dotIndex) => {
                if (dotIndex === index) {
                    dot.classList.add('bg-slate-700', 'scale-150');
                    dot.classList.remove('bg-slate-300');
                } else {
                    dot.classList.remove('bg-slate-700', 'scale-150');
                    dot.classList.add('bg-slate-300');
                }
            });
        }

        function nextSlide() {
            const slides = document.querySelectorAll('.sensor-slide');
            if (!slides.length) return;
            currentIndex = (currentIndex + 1) % slides.length;
            showSlide(currentIndex);
            resetTimer();
        }

        function startCarousel() {
            if (carouselInterval) clearInterval(carouselInterval);
            carouselInterval = setInterval(() => {
                if (isPlaying) nextSlide();
            }, 8000);
        }

        function stopCarousel() {
            if (carouselInterval) clearInterval(carouselInterval);
            if (timerInterval) clearInterval(timerInterval);
        }

        function toggleCarousel() {
            isPlaying = !isPlaying;
            const icon = document.getElementById('playPauseIcon');
            const timerSpan = document.getElementById('slideTimer');

            if (isPlaying) {
                icon.className = 'fa-solid fa-pause text-slate-600 text-xs sm:text-sm';
                timerSpan.style.opacity = '1';
                startCarousel();
                startTimerDisplay();
            } else {
                icon.className = 'fa-solid fa-play text-slate-600 text-xs sm:text-sm';
                timerSpan.style.opacity = '0.5';
                stopCarousel();
            }
        }

        function startTimerDisplay() {
            if (timerInterval) clearInterval(timerInterval);
            currentTimerSeconds = 8;
            const timerSpan = document.getElementById('slideTimer');
            timerInterval = setInterval(() => {
                if (isPlaying) {
                    currentTimerSeconds--;
                    if (timerSpan) timerSpan.textContent = currentTimerSeconds;
                    if (currentTimerSeconds <= 0) currentTimerSeconds = 8;
                }
            }, 1000);
        }

        function resetTimer() {
            if (!isPlaying) return;
            currentTimerSeconds = 8;
            const timerSpan = document.getElementById('slideTimer');
            if (timerSpan) timerSpan.textContent = currentTimerSeconds;
        }

        document.addEventListener('DOMContentLoaded', function() {
            initRegionChart();

            const slides = Array.from(document.querySelectorAll('.sensor-slide'));
            const dots = Array.from(document.querySelectorAll('.slide-dot'));

            if (slides.length) {
                dots.forEach((dot, idx) => {
                    dot.addEventListener('click', () => {
                        currentIndex = idx;
                        showSlide(currentIndex);
                        if (isPlaying) resetTimer();
                    });
                });
                showSlide(0);
                startCarousel();
                startTimerDisplay();
            }
        });
    </script>
@endsection
