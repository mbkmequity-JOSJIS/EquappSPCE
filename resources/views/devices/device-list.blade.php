@extends('layouts.app')
@section('title', 'IOT Device')

@section('style')
    <style>
        /* Custom styles untuk card hover effect */
        .location-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .location-card:hover {
            transform: translateY(-8px);
        }

        .card-image-wrap {
            position: relative;
            overflow: hidden;
        }

        .card-image-wrap::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.3) 100%);
            pointer-events: none;
        }

        .card-device-badge {
            transition: all 0.3s ease;
        }

        .location-card:hover .card-device-badge {
            transform: scale(1.05);
        }

        .card-score-badge {
            transition: all 0.3s ease;
        }

        .location-card:hover .card-score-badge {
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        /* Skeleton loading animation */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .skeleton {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-size: 1000px 100%;
        }

        /* Grid layout */
        .location-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .location-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
    </style>
@endsection

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="max-w-7xl mx-auto mb-10">
            <div class="bg-white rounded-2xl  overflow-hidden">
                <div class="mb-6 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <a href="{{ route('devices') }}"
                        class="inline-flex items-center gap-2 r px-4 py-2 font-medium text-green-500 transition  hover:text-emerald-700">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Menu Device
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="capitalize tracking-wider">{{ $device }}</span>
                </div>

                <div
                    class="relative bg-gradient-to-r {{ $device == 'aquaviska' ? 'from-sky-500/20 to-sky-700' : 'from-orange-500/20 to-orange-700' }} px-6 py-8 sm:px-10 sm:py-12">
                    <!-- Background pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute inset-0"
                            style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 1px); background-size: 24px 24px;">
                        </div>
                    </div>

                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div
                                        class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                        <i
                                            class="fas fa-{{ $device == 'aquaviska' ? 'water' : 'cloud-sun' }} text-2xl text-white"></i>
                                    </div>
                                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-white tracking-tight">
                                        {{ strtoupper($device) }}
                                    </h1>
                                </div>
                                <p class="text-white/90 text-base sm:text-lg max-w-2xl">
                                    Explore the real-time status and locations of our {{ ucfirst($device) }} monitoring
                                    devices deployed across various sites.
                                </p>
                            </div>

                            <!-- Stats Badge -->
                            <div
                                class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-3 text-center sm:text-right flex flex-col items-center  gap-1">
                                <div class="text-3xl font-bold text-white">{{ count($dataDevices ?? []) }}</div>
                                <div class="text-white/80 text-sm">Active Devices</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Info Bar -->
                <div class="bg-white border-b border-slate-100 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-chart-line text-emerald-500"></i>
                            <span>Real-time Monitoring</span>
                        </div>
                        <div class="w-px h-4 bg-slate-200"></div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-cloud-upload-alt text-sky-500"></i>
                            <span>Live Data Updates</span>
                        </div>
                        <div class="w-px h-4 bg-slate-200 hidden sm:block"></div>
                        <div class="flex items-center gap-2 hidden sm:flex">
                            <i class="fas fa-shield-alt text-green-500"></i>
                            <span>Secure Connection</span>
                        </div>
                    </div>

                    <!-- Last Updated -->
                    <div class="text-xs text-slate-400 flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Last updated: {{ now()->format('H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices Grid Section -->
        <div class="max-w-7xl mx-auto">
            @if (isset($dataDevices) && count($dataDevices) > 0)
                <div class="location-grid">
                    @foreach ($dataDevices as $index => $data)
                        <a href="{{ route('device.detail', ['device' => $device, 'id' => $data['device_code'] ?? '']) }}"
                            class="location-card group bg-white rounded-xl overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 block">

                            <!-- Card Image / Header Section -->
                            <div
                                class="card-image-wrap relative h-48 bg-gradient-to-br {{ $device == 'aquaviska' ? 'from-sky-400 to-sky-600' : 'from-orange-400 to-orange-600' }}">
                                @if (isset($data['img_url']))
                                    <img src="{{ asset('storage/img_loc/' . $data['img_url']) }}"
                                        alt="{{ $data['device_name'] ?? 'Device Image' }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <!-- Device Icon Background -->
                                    <div class="absolute inset-0 flex items-center justify-center opacity-20">
                                        <i
                                            class="fas fa-{{ $device == 'aquaviska' ? 'water' : 'cloud-sun' }} text-8xl text-white"></i>
                                    </div>
                                @endif

                                <!-- Device Type Badge -->
                                <div class="absolute top-4 left-4 z-10">
                                    <div
                                        class="card-device-badge inline-flex items-center gap-2 px-3 py-1.5 bg-white/95 backdrop-blur-sm rounded-lg text-xs font-semibold uppercase shadow-md {{ $device == 'aquaviska' ? 'text-sky-600' : 'text-orange-600' }}">
                                        <i class="fas fa-{{ $device == 'aquaviska' ? 'water' : 'cloud-sun' }} text-xs"></i>
                                        {{ strtoupper($device) }}
                                    </div>
                                </div>

                                <!-- Score Badge -->
                                <div class="absolute top-4 right-4 z-10">
                                    <div class="w-14 h-14 rounded-md flex flex-col items-center justify-center shadow-lg {{ $data['status'] === 'normal' ? 'bg-green-500/50' : ($data['status'] === 'waspada' ? 'bg-yellow-500/50' : 'bg-red-500/20') }}"
                                        style="background: ">
                                        <span
                                            class="text-white font-bold text-lg">{{ $data['condition_score'] ?? 0 }}</span>
                                        <span class="text-white/80 text-[10px] font-medium">SCORE</span>
                                    </div>
                                </div>

                                <!-- Device Code Overlay -->
                                <div class="absolute bottom-3 left-4 right-4">
                                    <div class="bg-black/50 backdrop-blur-sm rounded-lg px-3 py-1.5 inline-block">
                                        <code
                                            class="text-white/90 text-xs font-mono">{{ $data['device_code'] ?? 'N/A' }}</code>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body p-5">
                                <!-- Device Name -->
                                <h3
                                    class="card-title text-lg font-bold text-slate-800 mb-2 group-hover:{{ $device == 'aquaviska' ? 'text-sky-600' : 'text-orange-600' }} transition-colors line-clamp-1">
                                    {{ $data['device_name'] ?? 'Unknown Device' }}
                                </h3>

                                <!-- Location -->
                                <div class=" flex flex-wrap items-center mb-3 gap-2 text-slate-500 text-sm">
                                    <i class="fas fa-map-marker-alt text-slate-400 mt-0.5  flex-shrink-0"></i>
                                    <span class="line-clamp-2">
                                        {{ $data['location']['address'] ?? 'Address not available' }},
                                        {{ $data['location']['city'] ?? 'City not available' }}
                                    </span>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-slate-100 my-3"></div>

                                <!-- Status & Info Row -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        @if(isset($data['status']) && strtolower($data['status']) === 'online')
                                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                                            <span class="quality-badge text-xs font-medium px-2.5 py-1 rounded-full text-green-700 bg-green-100">
                                                Live
                                            </span>
                                        @else
                                            <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                            <span class="quality-badge text-xs font-medium px-2.5 py-1 rounded-full text-red-700 bg-red-100">
                                                Offline
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1 text-slate-400 text-xs">
                                        <i class="fas fa-microchip"></i>
                                        <span>{{ isset($data['status']) ? ucfirst($data['status']) : 'Offline' }}</span>
                                    </div>
                                </div>

                                <!-- Sensor Preview (Optional) -->
                                @if (isset($data['latest_data']) && count($data['latest_data']) > 0)
                                    <div class="mt-3 pt-3 border-t border-slate-100">
                                        <div class="flex items-center gap-2 text-xs text-slate-500">
                                            <i class="fas fa-chart-simple"></i>
                                            <span class="font-medium">Latest Reading:</span>
                                            @php
                                                $latestKeys = array_keys(array_slice($data['latest_data'], 0, 2));
                                            @endphp
                                            @foreach ($latestKeys as $key)
                                                <span class="bg-slate-100 px-2 py-0.5 rounded">
                                                    {{ ucfirst($key) }}: {{ $data['latest_data'][$key] ?? '--' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Footer -->
                            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs text-slate-500 flex items-center gap-1">
                                    <i class="far fa-clock"></i>
                                    Live Monitoring
                                </span>
                                <span
                                    class="text-{{ $device == 'aquaviska' ? 'sky' : 'orange' }}-500 text-sm font-medium flex items-center gap-1 group-hover:gap-2 transition-all">
                                    View Details
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="empty-state bg-white rounded-2xl shadow-md">
                    <div class="w-24 h-24 mx-auto mb-4 bg-slate-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-microchip text-4xl text-slate-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-700 mb-2">No Devices Found</h3>
                    <p class="text-slate-500">There are no {{ ucfirst($device) }} devices registered at the moment.</p>
                </div>
            @endif
        </div>

        <!-- Footer Note -->
        <div class="max-w-7xl mx-auto mt-8 text-center">
            <p class="text-xs text-slate-400 flex items-center justify-center gap-2">
                <i class="fas fa-circle text-[6px] text-slate-400"></i>
                Last sync: {{ now()->format('d/m/Y H:i:s') }}
            </p>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Optional: Add any interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading animation for images (if any)
            const cards = document.querySelectorAll('.location-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.05}s`;
            });

            // Auto-refresh data every 30 seconds (optional)
            let autoRefresh = setInterval(function() {
                // You can implement auto-refresh logic here
                console.log('Auto-refreshing device list...');
            }, 30000);

            // Cleanup interval on page unload
            window.addEventListener('beforeunload', function() {
                clearInterval(autoRefresh);
            });
        });
    </script>
@endsection
