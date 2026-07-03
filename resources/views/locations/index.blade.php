@extends('layouts.app')
@section('title', 'Device Location Map')

@section('style')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    
    <style>
        /* Map Container */
        .map-container {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
    #deviceMap {
            height: 500px;
            width: 100%;
            z-index: 1;
        }
        
        /* Custom Marker Styles */
        .custom-marker {
            background: transparent;
            border: none;
        }
        
        .marker-pin {
            width: 40px;
            height: 40px;
            border-radius: 50% 50% 50% 0;
            background: #ef4444;
            position: absolute;
            transform: rotate(-45deg);
            left: 50%;
            top: 50%;
            margin: -20px 0 0 -20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .marker-pin::after {
            content: '';
            width: 24px;
            height: 24px;
            margin: 8px 0 0 8px;
            background: white;
            position: absolute;
            border-radius: 50%;
        }
        
        .marker-pin.aquaviska {
            background: #0ea5e9;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
        }
        
        .marker-pin.climeet {
            background: #f97316;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }
        
        .marker-pin:hover {
            transform: rotate(-45deg) scale(1.1);
        }
        
        /* Custom Popup */
        .custom-popup .leaflet-popup-content-wrapper {
            border-radius: 16px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .custom-popup .leaflet-popup-content {
            margin: 0;
            min-width: 260px;
        }
        
        .custom-popup .leaflet-popup-tip {
            border-top-color: white;
        }
        
        /* Loading Overlay */
        /* .map-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 24px;
            backdrop-filter: blur(4px);
        } */
        
        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 3px solid #e2e8f0;
            border-top-color: #0ea5e9;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Device Card */
        .device-card-map {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .device-card-map:hover {
            transform: translateX(8px);
            border-left-width: 4px;
        }
        
        .device-card-map.active {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left-color: #0ea5e9;
        }
        
        /* Heatmap Legend */
        .legend {
            background: white;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-size: 12px;
            line-height: 1.5;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: inline-block;
            margin-right: 8px;
        }
        
        /* Cluster Group Styles */
        .marker-cluster {
            background-clip: padding-box;
            border-radius: 20px;
        }
        
        .marker-cluster div {
            width: 30px;
            height: 30px;
            margin-left: 5px;
            margin-top: 5px;
            text-align: center;
            border-radius: 15px;
            font-weight: bold;
            font-size: 12px;
            line-height: 30px;
        }
        
        .marker-cluster-small {
            background-color: rgba(14, 165, 233, 0.6);
        }
        
        .marker-cluster-small div {
            background-color: rgba(14, 165, 233, 0.8);
            color: white;
        }
        
        .marker-cluster-medium {
            background-color: rgba(249, 115, 22, 0.6);
        }
        
        .marker-cluster-medium div {
            background-color: rgba(249, 115, 22, 0.8);
            color: white;
        }
        
        .marker-cluster-large {
            background-color: rgba(239, 68, 68, 0.6);
        }
        
        .marker-cluster-large div {
            background-color: rgba(239, 68, 68, 0.8);
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            #deviceMap {
                height: 400px;
            }
            
            .device-card-map:hover {
                transform: none;
            }
        }
    </style>
@endsection

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-col  sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-center sm:text-left ">
                        <div class="text-3xl sm:text-7xl mb-4 font-bold text-slate-800"><i class="fas fa-map-marked-alt"></i>
                            Device Location <span class="text-sky-500">Map</span>
                        </div>
                        <p class="text-slate-500 mt-2">Monitor semua perangkat IoT dan lokasi pemasangannya.</p>
                    </div>
                    
                    <!-- Stats -->
                    <div class="flex gap-3">
                        <div class="bg-white rounded-2xl px-4 py-2 shadow-md text-center">
                            <div class="text-2xl font-bold text-sky-600" id="totalAqua">0</div>
                            <div class="text-xs text-slate-500">AQUAVISKA</div>
                        </div>
                        <div class="bg-white rounded-2xl px-4 py-2 shadow-md text-center">
                            <div class="text-2xl font-bold text-orange-500" id="totalClimeet">0</div>
                            <div class="text-xs text-slate-500">CLIMEET</div>
                        </div>
                        <div class="bg-white rounded-2xl px-4 py-2 shadow-md text-center">
                            <div class="text-2xl font-bold text-emerald-500" id="totalActive">0</div>
                            <div class="text-xs text-slate-500">Active</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Map and Sidebar Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Map Section -->
                <div class="lg:col-span-2">
                    <div class="map-container relative">
                        <div id="deviceMap"></div>
                        <div id="mapLoading" class="map-loading hidden">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                    
                    <!-- Map Controls -->
                    <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
                        <div class="flex gap-2">
                            <button onclick="zoomToFit()" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition shadow-sm">
                                <i class="fas fa-expand-alt mr-2"></i>Zoom to Fit
                            </button>
                            <button onclick="resetMap()" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition shadow-sm">
                                <i class="fas fa-home mr-2"></i>Reset View
                            </button>
                        </div>
                        <div class="legend bg-white/90 backdrop-blur-sm rounded-xl shadow-md px-4 py-2 text-xs">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-sky-500"></span> AQUAVISKA</span>
                                <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-orange-500"></span> CLIMEET</span>
                                <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span> Active</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Device List Sidebar -->
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                        <div class="flex items-center justify-between">
                            <h2 class="font-semibold text-slate-800">
                                <i class="fas fa-microchip text-sky-500 mr-2"></i>
                                Daftar Perangkat
                            </h2>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" id="searchDevice" placeholder="Cari device..." 
                                       class="pl-9 pr-4 py-1.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-400 w-40 sm:w-48">
                            </div>
                        </div>
                    </div>
                    
                    <div class="h-[450px] overflow-y-auto divide-y divide-slate-100" id="deviceList">
                        <!-- Device list akan diisi oleh JavaScript -->
                        <div class="p-8 text-center text-slate-400">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p class="text-sm">Memuat data device...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Info Panel -->
            <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-water text-sky-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Total Monitoring Points</p>
                            <p class="text-xl font-bold text-slate-800" id="totalPoints">0</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-line text-emerald-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Average Health Score</p>
                            <p class="text-xl font-bold text-slate-800" id="avgScore">0</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-amber-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Active Alerts</p>
                            <p class="text-xl font-bold text-slate-800" id="totalAlerts">0</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-globe text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Cities Covered</p>
                            <p class="text-xl font-bold text-slate-800" id="totalCities">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <!-- Leaflet Marker Cluster (opsional) -->
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">

    <script>
        // Data devices (simulasi - nanti bisa diganti dengan data dari controller)
        let devicesData = [
            {
                id: 1,
                device_code: 'AQV-001',
                device_name: 'Sensor Air Cisadane',
                type: 'aquaviska',
                status: 'active',
                condition_score: 85,
                location: {
                    name: 'Cisadane',
                    address: 'Jalan Cisadane, Tangerang',
                    city: 'Tangerang',
                    latitude: -6.2088,
                    longitude: 106.8456,
                    province: 'Banten'
                },
                latest_data: { ph: 7.2, tds: 120, temperature: 28 }
            },
            {
                id: 2,
                device_code: 'AQV-002',
                device_name: 'Sensor Air Bengawan Solo',
                type: 'aquaviska',
                status: 'warning',
                condition_score: 62,
                location: {
                    name: 'Bengawan Solo',
                    address: 'Surakarta',
                    city: 'Surakarta',
                    latitude: -7.557,
                    longitude: 110.844,
                    province: 'Jawa Tengah'
                },
                latest_data: { ph: 6.8, tds: 350, temperature: 29 }
            },
            {
                id: 3,
                device_code: 'CLM-001',
                device_name: 'Sensor Udara Jakarta Pusat',
                type: 'climeet',
                status: 'active',
                condition_score: 78,
                location: {
                    name: 'Jakarta Pusat',
                    address: 'Gambir, Jakarta Pusat',
                    city: 'Jakarta',
                    latitude: -6.1754,
                    longitude: 106.8272,
                    province: 'DKI Jakarta'
                },
                latest_data: { pm25: 45, pm10: 78, temperature: 30 }
            },
            {
                id: 4,
                device_code: 'CLM-002',
                device_name: 'Sensor Udara Bandung',
                type: 'climeet',
                status: 'critical',
                condition_score: 45,
                location: {
                    name: 'Bandung',
                    address: 'Jl. Merdeka, Bandung',
                    city: 'Bandung',
                    latitude: -6.9175,
                    longitude: 107.6191,
                    province: 'Jawa Barat'
                },
                latest_data: { pm25: 95, pm10: 120, temperature: 27 }
            }
        ];
        
        let map;
        let markers = [];
        let activeMarker = null;
        
        // Inisialisasi Map
        function initMap() {
            map = L.map('deviceMap').setView([-2.5489, 118.0149], 5); // Center Indonesia
            
            // Base tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 19,
                minZoom: 3
            }).addTo(map);
            
            // Add scale bar
            L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(map);
            
            // Add devices to map
            addDevicesToMap();
            
            // Hide loading
            document.getElementById('mapLoading').classList.add('hidden');
        }
        
        // Create custom marker icon
        function createMarkerIcon(type, status) {
            const color = type === 'aquaviska' ? '#0ea5e9' : '#f97316';
            const isActive = status === 'active';
            
            const iconHtml = `
                <div class="marker-pin ${type}" style="background: ${color}; ${isActive ? 'animation: pulse 1.5s infinite;' : ''}">
                    <div class="absolute inset-0 flex items-center justify-center transform rotate-45">
                        <i class="fas fa-${type === 'aquaviska' ? 'water' : 'cloud-sun'} text-white text-sm"></i>
                    </div>
                </div>
                ${isActive ? '<div class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-400 rounded-full animate-ping"></div>' : ''}
            `;
            
            return L.divIcon({
                className: 'custom-marker',
                html: iconHtml,
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });
        }
        
        // Create popup content
        function createPopupContent(device) {
            const statusColor = device.status === 'active' ? 'text-emerald-500' : (device.status === 'warning' ? 'text-amber-500' : 'text-red-500');
            const statusIcon = device.status === 'active' ? 'fa-circle' : (device.status === 'warning' ? 'fa-exclamation-triangle' : 'fa-times-circle');
            
            return `
                <div class="custom-popup-content" style="min-width: 280px;">
                    <div class="bg-gradient-to-r ${device.type === 'aquaviska' ? 'from-sky-500 to-sky-600' : 'from-orange-500 to-orange-600'} px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-${device.type === 'aquaviska' ? 'water' : 'cloud-sun'} text-white"></i>
                                <h3 class="font-bold text-white">${device.device_name}</h3>
                            </div>
                            <span class="text-xs bg-white/20 px-2 py-1 rounded-full text-white">${device.device_code}</span>
                        </div>
                    </div>
                    <div class="p-4 bg-white">
                        <div class="space-y-3">
                            <div class="flex items-start gap-2 text-sm">
                                <i class="fas fa-map-marker-alt text-slate-400 mt-0.5"></i>
                                <div>
                                    <p class="text-slate-700">${device.location.address}, ${device.location.city}</p>
                                    <p class="text-xs text-slate-400">${device.location.province}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-100 pt-2">
                                <div class="flex items-center gap-2">
                                    <i class="fas ${statusIcon} ${statusColor}"></i>
                                    <span class="text-sm capitalize">${device.status}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-500">Health Score</span>
                                    <div class="flex items-center gap-1">
                                        <span class="text-lg font-bold ${device.condition_score >= 70 ? 'text-emerald-500' : (device.condition_score >= 50 ? 'text-amber-500' : 'text-red-500')}">
                                            ${device.condition_score}
                                        </span>
                                        <span class="text-xs text-slate-400">/100</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-slate-100 pt-2">
                                <p class="text-xs text-slate-500 mb-1">Latest Reading:</p>
                                <div class="flex flex-wrap gap-2">
                                    ${Object.entries(device.latest_data || {}).map(([key, val]) => `
                                        <span class="text-xs bg-slate-100 px-2 py-1 rounded-full">
                                            ${key}: ${val}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                            <a href="{{ url('devices') }}/${device.type}/${device.device_code}" 
                               class="block text-center mt-2 px-3 py-2 bg-${device.type === 'aquaviska' ? 'sky' : 'orange'}-50 text-${device.type === 'aquaviska' ? 'sky' : 'orange'}-700 rounded-lg text-sm font-semibold hover:bg-${device.type === 'aquaviska' ? 'sky' : 'orange'}-100 transition">
                                <i class="fas fa-chart-line mr-1"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Add devices to map
        function addDevicesToMap() {
            // Clear existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            
            let totalAqua = 0;
            let totalClimeet = 0;
            let totalActive = 0;
            let totalScore = 0;
            let uniqueCities = new Set();
            
            devicesData.forEach(device => {
                if (device.location.latitude && device.location.longitude) {
                    // Count stats
                    if (device.type === 'aquaviska') totalAqua++;
                    else totalClimeet++;
                    
                    if (device.status === 'active') totalActive++;
                    totalScore += device.condition_score || 0;
                    
                    if (device.location.city) uniqueCities.add(device.location.city);
                    
                    // Create marker
                    const marker = L.marker([device.location.latitude, device.location.longitude], {
                        icon: createMarkerIcon(device.type, device.status)
                    }).bindPopup(createPopupContent(device), {
                        className: 'custom-popup',
                        maxWidth: 320,
                        minWidth: 280
                    });
                    
                    marker.on('click', () => {
                        if (activeMarker) {
                            activeMarker.setIcon(createMarkerIcon(activeMarker.options.deviceType, activeMarker.options.deviceStatus));
                        }
                        activeMarker = marker;
                        highlightDeviceCard(device.id);
                    });
                    
                    marker.options.deviceType = device.type;
                    marker.options.deviceStatus = device.status;
                    marker.options.deviceId = device.id;
                    
                    marker.addTo(map);
                    markers.push(marker);
                }
            });
            
            // Update stats display
            document.getElementById('totalAqua').textContent = totalAqua;
            document.getElementById('totalClimeet').textContent = totalClimeet;
            document.getElementById('totalActive').textContent = totalActive;
            document.getElementById('totalPoints').textContent = markers.length;
            document.getElementById('avgScore').textContent = markers.length ? Math.round(totalScore / markers.length) : 0;
            document.getElementById('totalAlerts').textContent = devicesData.filter(d => d.status !== 'active').length;
            document.getElementById('totalCities').textContent = uniqueCities.size;
            
            // Render device list
            renderDeviceList();
        }
        
        // Render device list sidebar
        function renderDeviceList() {
            const container = document.getElementById('deviceList');
            const searchTerm = document.getElementById('searchDevice')?.value.toLowerCase() || '';
            
            const filteredDevices = devicesData.filter(device => 
                device.device_name.toLowerCase().includes(searchTerm) ||
                device.device_code.toLowerCase().includes(searchTerm) ||
                device.location.city.toLowerCase().includes(searchTerm)
            );
            
            if (filteredDevices.length === 0) {
                container.innerHTML = `
                    <div class="p-8 text-center text-slate-400">
                        <i class="fas fa-search text-2xl mb-2"></i>
                        <p class="text-sm">Tidak ada device ditemukan</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = filteredDevices.map(device => `
                <div class="device-card-map p-4 hover:bg-slate-50 transition-all cursor-pointer border-l-4 border-l-transparent"
                     data-device-id="${device.id}"
                     onclick="focusDevice(${device.id})">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-8 h-8 rounded-lg ${device.type === 'aquaviska' ? 'bg-sky-100' : 'bg-orange-100'} flex items-center justify-center">
                                    <i class="fas fa-${device.type === 'aquaviska' ? 'water' : 'cloud-sun'} ${device.type === 'aquaviska' ? 'text-sky-600' : 'text-orange-600'}"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-800 text-sm">${device.device_name}</h3>
                                    <p class="text-xs text-slate-400">${device.device_code}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${device.location.city}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center gap-1">
                                <span class="text-sm font-bold ${device.condition_score >= 70 ? 'text-emerald-500' : (device.condition_score >= 50 ? 'text-amber-500' : 'text-red-500')}">
                                    ${device.condition_score}
                                </span>
                                <span class="text-xs text-slate-400">/100</span>
                            </div>
                            <div class="mt-1">
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                    ${device.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 
                                      (device.status === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')}">
                                    <i class="fas fa-${device.status === 'active' ? 'circle' : (device.status === 'warning' ? 'exclamation-triangle' : 'times-circle')} text-[8px]"></i>
                                    ${device.status}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Focus on specific device
        function focusDevice(deviceId) {
            const device = devicesData.find(d => d.id === deviceId);
            if (device && device.location.latitude && device.location.longitude) {
                map.setView([device.location.latitude, device.location.longitude], 15);
                
                // Find and open marker popup
                const marker = markers.find(m => m.options.deviceId === deviceId);
                if (marker) {
                    marker.openPopup();
                    
                    // Highlight card
                    highlightDeviceCard(deviceId);
                }
            }
        }
        
        // Highlight device card
        function highlightDeviceCard(deviceId) {
            document.querySelectorAll('.device-card-map').forEach(card => {
                card.classList.remove('active', 'border-l-sky-500', 'bg-gradient-to-r', 'from-sky-50', 'to-transparent');
                if (parseInt(card.dataset.deviceId) === deviceId) {
                    card.classList.add('active', 'border-l-sky-500');
                }
            });
        }
        
        // Zoom to fit all markers
        function zoomToFit() {
            if (markers.length === 0) return;
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.2));
        }
        
        // Reset map view
        function resetMap() {
            map.setView([-2.5489, 118.0149], 5);
        }
        
        // Search device
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            const searchInput = document.getElementById('searchDevice');
            if (searchInput) {
                searchInput.addEventListener('input', renderDeviceList);
            }
        });
    </script>
@endsection