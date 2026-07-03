{{-- resources/views/admin/devices/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard - Manajemen Perangkat')

@section('style')
    <style>
        /* Custom styles for modal animation */
        .modal-overlay {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible !important;
        }

        .modal-container {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .modal-overlay.show .modal-container {
            transform: scale(1);
            opacity: 1;
        }

        /* Custom scrollbar */
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Loading spinner */
        .loading-spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
    <main class="bg-white min-h-screen px-6 md:p-10 md:pt-16 relative">
        <div
            class="absolute top-0 left-0 right-0 bg-orange-500/40 text-white p-2 text-center flex text-xl justify-center items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
            @if (Session::has('firebase_user') && Session::get('firebase_user')['role'] === 'admin')
                    <span class="font-semibold tracking-wider">Admin Side</span>
                @else
                <span class="font-semibold tracking-wider">Operator Side</span>
            @endif
        </div>

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <i class="fas fa-microchip text-4xl text-blue-500"></i>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-800">Manajemen Perangkat</h1>
            </div>
            <p class="text-slate-500 text-base md:text-lg">Kelola semua perangkat monitoring AQUAVISKA dan IoT Climate</p>
        </div>

        <!-- Filter & Action Bar -->
        <div
            class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 p-5 bg-white rounded-xl border border-slate-200 shadow-sm">
            <!-- Filter Tabs -->
            <div class="flex gap-2 p-1.5 bg-slate-50 rounded-xl border border-slate-200">
                <button onclick="filterDevices('all')" id="filterAll"
                    class="filter-btn active px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 bg-blue-500 text-white shadow-sm">
                    <i class="fas fa-th-large mr-2"></i>Semua
                </button>
                <button onclick="filterDevices('aquaviska')" id="filterAquaviska"
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 text-slate-600 hover:bg-slate-100">
                    <i class="fas fa-water mr-2 text-blue-400"></i>AQUAVISKA
                </button>
                <button onclick="filterDevices('iot')" id="filterIot"
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 text-slate-600 hover:bg-slate-100">
                    <i class="fas fa-cloud-sun mr-2 text-amber-500"></i>IoT Climate
                </button>
            </div>

            <!-- Search & Add Button -->
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="searchInput" onkeyup="searchDevices()" placeholder="Cari perangkat..."
                        class="pl-9 pr-4 py-2 w-full sm:w-64 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                </div>
                <button onclick="openAddModal()"
                    class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-5 py-2 rounded-xl font-medium flex items-center justify-center gap-2 hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-plus"></i>
                    Tambah Perangkat
                </button>
            </div>
        </div>

        <!-- Devices Table -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left py-4 px-5 text-sm font-semibold text-slate-600">ID</th>
                            <th class="text-left py-4 px-5 text-sm font-semibold text-slate-600">Nama Perangkat</th>
                            <th class="text-left py-4 px-5 text-sm font-semibold text-slate-600">Lokasi</th>
                            <th class="text-left py-4 px-5 text-sm font-semibold text-slate-600">Tipe</th>
                            <th class="text-left py-4 px-5 text-sm font-semibold text-slate-600">Status</th>
                            <th class="text-left py-4 px-5 text-sm font-semibold text-slate-600">Sensor</th>
                            <th class="text-left py-4 px-5 text-sm font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="devicesTableBody">
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </div>
            <!-- Empty State -->
            <div id="emptyState" class="hidden text-center py-16">
                <i class="fas fa-microchip text-6xl text-slate-300 mb-4"></i>
                <p class="text-slate-500">Tidak ada perangkat yang ditemukan</p>
            </div>
        </div>
    </main>

    <!-- MODAL TAMBAH/EDIT PERANGKAT -->
    <div id="deviceModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 opacity-0 invisible transition-all duration-300">
        <div
            class="bg-white rounded-2xl w-[95%] max-w-lg max-h-[90vh] overflow-hidden shadow-2xl transform scale-95 transition-all duration-300">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i id="modalIcon" class="fas fa-plus-circle text-blue-500"></i>
                    <span id="modalTitle">Tambah Perangkat Baru</span>
                </h2>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                <form id="deviceForm" onsubmit="return false;">
                    <input type="hidden" id="deviceId">
                    <input type="hidden" id="oldDeviceNode">
                    <input type="hidden" id="oldDeviceType">

                    <!-- Tipe Perangkat & Node -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-layer-group text-blue-400 mr-1"></i> Tipe Perangkat <span class="text-red-500">*</span>
                            </label>
                            <select id="deviceType" required onchange="updateSensors()"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                <option value="">Pilih Tipe</option>
                                <option value="AQUAVISKA">💧 AQUAVISKA (Monitoring Air)</option>
                                <option value="IOT Climate">☁️ IoT Climate (Monitoring Udara)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-database text-blue-400 mr-1"></i> Pilih Node Firebase <span class="text-red-500">*</span>
                            </label>
                            <select id="firebaseNode" required onchange="fillDataFromFirebase(this.value)"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                <option value="">Pilih Tipe Perangkat Dulu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Nama Perangkat -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fas fa-tag text-blue-400 mr-1"></i> Nama Perangkat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="deviceName" required
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition"
                            placeholder="Contoh: Sensor Air Cisadane">
                    </div>

                    <!-- Lokasi -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fas fa-map-marker-alt text-blue-400 mr-1"></i> Lokasi <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input type="text" id="locAddress" required placeholder="Alamat (cth: Rowo Jombor)"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                            <input type="text" id="locCity" required placeholder="Kota (cth: Klaten)"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                            <input type="text" id="locProvince" required placeholder="Provinsi (cth: Jawa Tengah)"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Sensor yang Tersedia -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fas fa-microchip text-blue-400 mr-1"></i> Sensor yang Tersedia
                        </label>
                        <div id="sensorContainer" class="grid grid-cols-2 gap-2 p-3 bg-slate-50 rounded-xl min-h-[50px] text-sm text-slate-500">
                            Pilih tipe perangkat terlebih dahulu untuk melihat sensor yang tersedia.
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fas fa-align-left text-blue-400 mr-1"></i> Deskripsi (Opsional)
                        </label>
                        <textarea id="deviceDescription" rows="3"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                            placeholder="Deskripsi singkat tentang perangkat..."></textarea>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 p-6 border-t border-slate-200 bg-slate-50">
                <button onclick="closeModal()"
                    class="px-5 py-2 border border-slate-300 rounded-xl text-slate-600 font-medium hover:bg-slate-100 transition">
                    Batal
                </button>
                <button onclick="saveDevice()"
                    class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-medium hover:from-blue-600 hover:to-blue-700 transition shadow-md">
                    <i class="fas fa-save mr-1"></i> Simpan Perangkat
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div id="deleteModal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 opacity-0 invisible transition-all duration-300">
        <div class="bg-white rounded-2xl w-[90%] max-w-md shadow-2xl transform scale-95 transition-all duration-300">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash-alt text-2xl text-red-500"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Konfirmasi Hapus</h3>
                <p class="text-slate-500 mb-1">Apakah Anda yakin ingin menghapus perangkat</p>
                <p class="font-semibold text-slate-700" id="deleteDeviceName"></p>
                <p class="text-sm text-red-500 mt-3">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="flex gap-3 p-6 pt-0">
                <button onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2 border border-slate-300 rounded-xl text-slate-600 font-medium hover:bg-slate-100 transition">
                    Batal
                </button>
                <button onclick="confirmDeleteDevice()"
                    class="flex-1 px-4 py-2 bg-red-500 text-white rounded-xl font-medium hover:bg-red-600 transition">
                    Hapus
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const waterQualityDevices = @json($waterQualityDevices ?? []);
        const weatherStationDevices = @json($weatherStationDevices ?? []);

        let devices = [];
        
        Object.keys(waterQualityDevices).forEach(key => {
            if (!waterQualityDevices[key] || typeof waterQualityDevices[key] !== 'object') return;
            const d = waterQualityDevices[key];
            devices.push({
                id: key,
                node: key,
                name: d.device_name || '',
                serial_number: d.device_code || '',
                type: 'AQUAVISKA',
                location: [d.location?.address, d.location?.city, d.location?.province].filter(Boolean).join(', '),
                location_data: d.location || {},
                status: d.status || 'offline',
                description: d.description || '',
                sensors: ['pH Meter', 'Suhu Air', 'TDS', 'Kekeruhan', 'DO/dissolved oxygen'],
                is_preview: d.is_preview === true || d.is_preview === "true",
                is_deleted: d.is_deleted === true || d.is_deleted === "true",
                original_data: d
            });
        });

        Object.keys(weatherStationDevices).forEach(key => {
            if (!weatherStationDevices[key] || typeof weatherStationDevices[key] !== 'object') return;
            const d = weatherStationDevices[key];
            devices.push({
                id: key,
                node: key,
                name: d.device_name || '',
                serial_number: d.device_code || '',
                type: 'IOT Climate',
                location: [d.location?.address, d.location?.city, d.location?.province].filter(Boolean).join(', '),
                location_data: d.location || {},
                status: d.status || 'offline',
                description: d.description || '',
                sensors: ['PM10', 'Suhu Udara', 'Kelembaban', 'CO2', 'PM2.5'],
                is_preview: d.is_preview === true || d.is_preview === "true",
                is_deleted: d.is_deleted === true || d.is_deleted === "true",
                original_data: d
            });
        });

        let currentFilter = 'all';
        let currentSearch = '';
        let deleteId = null;

        const sensorData = {
            'AQUAVISKA': ['pH Meter', 'Suhu Air', 'TDS', 'Kekeruhan', 'DO/dissolved oxygen'],
            'IOT Climate': ['PM10', 'Suhu Udara', 'Kelembaban', 'CO2', 'PM2.5']
        };

        function populateFirebaseNodes(type, selectedNode = null) {
            const select = document.getElementById('firebaseNode');
            select.innerHTML = '<option value="">Pilih Node Firebase</option>';
            
            let dataObj = null;
            if (type === 'AQUAVISKA') {
                dataObj = waterQualityDevices;
            } else if (type === 'IOT Climate' || type === 'IoT Climate') {
                dataObj = weatherStationDevices;
            }

            if (dataObj) {
                for (const key in dataObj) {
                    if (dataObj.hasOwnProperty(key) && typeof dataObj[key] === 'object') {
                        const node = dataObj[key];
                        // Hanya tampilkan node yang belum di-approve (is_preview != true) ATAU node yang sedang di-edit
                        const isApproved = node.is_preview === true || node.is_preview === "true";
                        const isDeleted = node.is_deleted === true || node.is_deleted === "true";
                        
                        if (!isApproved || isDeleted || key === selectedNode) {
                            select.innerHTML += `<option value="${key}">${key}</option>`;
                        }
                    }
                }
            }
        }

        function fillDataFromFirebase(nodeKey) {
            const type = document.getElementById('deviceType').value;
            let dataObj = null;
            if (type === 'AQUAVISKA') {
                dataObj = waterQualityDevices;
            } else if (type === 'IOT Climate' || type === 'IoT Climate') {
                dataObj = weatherStationDevices;
            }

            if (!nodeKey || !dataObj || !dataObj[nodeKey]) {
                document.getElementById('deviceName').value = '';
                document.getElementById('locAddress').value = '';
                document.getElementById('locCity').value = '';
                document.getElementById('locProvince').value = '';
                return;
            }

            const node = dataObj[nodeKey];
            if (node.device_name) {
                document.getElementById('deviceName').value = node.device_name || '';
            }
            if (node.location) {
                document.getElementById('locAddress').value = node.location.address || '';
                document.getElementById('locCity').value = node.location.city || '';
                document.getElementById('locProvince').value = node.location.province || '';
            }
        }

        function updateSensors(selectedNode = null) {
            const type = document.getElementById('deviceType').value;
            const container = document.getElementById('sensorContainer');

            populateFirebaseNodes(type, selectedNode);

            if (!type) {
                container.innerHTML = 'Pilih tipe perangkat terlebih dahulu untuk melihat sensor yang tersedia.';
                return;
            }

            let mappedType = '';
            if (type === 'AQUAVISKA') mappedType = 'AQUAVISKA';
            else if (type === 'IOT Climate' || type === 'IoT Climate') mappedType = 'IOT Climate';
            
            const availableSensors = sensorData[mappedType] || [];
            container.innerHTML = `<ul class="list-disc pl-5 text-sm text-slate-600 space-y-1">` + 
                availableSensors.map(sensor => `<li>${sensor}</li>`).join('') + 
                `</ul>`;
        }

        function renderDevices() {
            let filtered = devices.filter(d => d.is_preview === true && d.is_deleted !== true);

            // Filter by type
            if (currentFilter !== 'all') {
                filtered = filtered.filter(d =>
                    currentFilter === 'aquaviska' ? d.type === 'AQUAVISKA' : d.type === 'IOT Climate'
                );
            }

            // Filter by search
            if (currentSearch) {
                const keyword = currentSearch.toLowerCase();
                filtered = filtered.filter(d =>
                    d.name.toLowerCase().includes(keyword) ||
                    d.location.toLowerCase().includes(keyword) ||
                    d.serial_number.toLowerCase().includes(keyword)
                );
            }

            const tbody = document.getElementById('devicesTableBody');
            const emptyState = document.getElementById('emptyState');

            if (filtered.length === 0) {
                tbody.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');

            tbody.innerHTML = filtered.map(device => `
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                <td class="py-3.5 px-5 text-sm text-slate-500">${device.node}</td>
                <td class="py-3.5 px-5">
                    <div class="font-medium text-slate-800">${escapeHtml(device.name)}</div>
                    <div class="text-xs text-slate-400">${escapeHtml(device.serial_number)}</div>
                </td>
                <td class="py-3.5 px-5 text-sm text-slate-600">${escapeHtml(device.location)}</td>
                <td class="py-3.5 px-5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${device.type === 'AQUAVISKA' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'}">
                        <i class="fas ${device.type === 'AQUAVISKA' ? 'fa-water' : 'fa-cloud-sun'} text-xs"></i>
                        ${device.type}
                    </span>
                </td>
                <td class="py-3.5 px-5">
                    <select onchange="updateDeviceStatus('${device.node}', '${device.type}', this.value)" class="text-xs font-medium rounded-full px-2.5 py-1 focus:outline-none cursor-pointer border-none focus:ring-0
                        ${device.status === 'online' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                        <option value="online" ${device.status === 'online' ? 'selected' : ''}>🟢 Online</option>
                        <option value="offline" ${device.status === 'offline' ? 'selected' : ''}>🔴 Offline</option>
                    </select>
                </td>
                <td class="py-3.5 px-5">
                    <div class="flex flex-wrap gap-1.5">
                        ${device.sensors.slice(0, 2).map(s => `<span class="px-2 py-0.5 bg-slate-100 rounded-md text-xs text-slate-600">${escapeHtml(s)}</span>`).join('')}
                        ${device.sensors.length > 2 ? `<span class="px-2 py-0.5 bg-slate-100 rounded-md text-xs text-slate-500">+${device.sensors.length - 2}</span>` : ''}
                    </div>
                </td>
                <td class="py-3.5 px-5">
                    <div class="flex gap-2">
                        <button onclick="openEditModal('${device.id}')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="openDeleteModal('${device.id}')" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        }

        // Update device status from table dropdown
        function updateDeviceStatus(node, type, newStatus) {
            const index = devices.findIndex(d => d.node === node && d.type === type);
            if (index !== -1) {
                const oldStatus = devices[index].status;
                devices[index].status = newStatus;
                renderDevices(); // optimistically update view

                fetch('{{ route("admin.update.firebase.status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ node: node, type: type, status: newStatus })
                }).then(res => res.json())
                  .then(data => {
                      if (!data.success) {
                          alert('Gagal mengupdate status');
                          devices[index].status = oldStatus;
                          renderDevices();
                      }
                  }).catch(err => {
                      console.error(err);
                      alert('Gagal mengupdate status');
                      devices[index].status = oldStatus;
                      renderDevices();
                  });
            }
        }

        // Escape HTML untuk keamanan
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Filter devices
        function filterDevices(type) {
            currentFilter = type;

            // Update active button style
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white', 'shadow-sm');
                btn.classList.add('text-slate-600');
            });

            if (type === 'all') {
                document.getElementById('filterAll').classList.add('bg-blue-500', 'text-white', 'shadow-sm');
                document.getElementById('filterAll').classList.remove('text-slate-600');
            } else if (type === 'aquaviska') {
                document.getElementById('filterAquaviska').classList.add('bg-blue-500', 'text-white', 'shadow-sm');
                document.getElementById('filterAquaviska').classList.remove('text-slate-600');
            } else {
                document.getElementById('filterIot').classList.add('bg-blue-500', 'text-white', 'shadow-sm');
                document.getElementById('filterIot').classList.remove('text-slate-600');
            }

            renderDevices();
        }

        // Search devices
        function searchDevices() {
            currentSearch = document.getElementById('searchInput').value;
            renderDevices();
        }

        // Open Add Modal
        function openAddModal() {
            document.getElementById('modalIcon').className = 'fas fa-plus-circle text-blue-500';
            document.getElementById('modalTitle').innerText = 'Tambah Perangkat Baru';
            document.getElementById('deviceId').value = '';
            document.getElementById('oldDeviceNode').value = '';
            document.getElementById('oldDeviceType').value = '';
            
            document.getElementById('deviceType').value = '';
            updateSensors();

            document.getElementById('deviceName').value = '';
            document.getElementById('firebaseNode').value = '';
            
            document.getElementById('locAddress').value = '';
            document.getElementById('locCity').value = '';
            document.getElementById('locProvince').value = '';
            document.getElementById('deviceDescription').value = '';
            
            openModal();
        }

        // Open Edit Modal
        function openEditModal(id) {
            const device = devices.find(d => d.id === id);
            if (!device) return;

            document.getElementById('modalIcon').className = 'fas fa-edit text-blue-500';
            document.getElementById('modalTitle').innerText = 'Edit Perangkat';
            document.getElementById('deviceId').value = device.id;
            document.getElementById('oldDeviceNode').value = device.node;
            document.getElementById('oldDeviceType').value = device.type;
            
            document.getElementById('deviceType').value = device.type;
            updateSensors(device.node);

            document.getElementById('firebaseNode').value = device.node;
            document.getElementById('deviceName').value = device.name;
            
            document.getElementById('locAddress').value = device.location_data?.address || '';
            document.getElementById('locCity').value = device.location_data?.city || '';
            document.getElementById('locProvince').value = device.location_data?.province || '';
            
            document.getElementById('deviceDescription').value = device.description || '';

            openModal();
        }

        // Open modal
        function openModal() {
            const modal = document.getElementById('deviceModal');
            modal.classList.remove('opacity-0', 'invisible');
            modal.classList.add('opacity-100', 'visible');
            document.body.style.overflow = 'hidden';
        }

        // Close modal
        function closeModal() {
            const modal = document.getElementById('deviceModal');
            modal.classList.add('opacity-0', 'invisible');
            modal.classList.remove('opacity-100', 'visible');
            document.body.style.overflow = '';
        }

        // Save device
        function saveDevice() {
            const id = document.getElementById('deviceId').value;
            const oldNode = document.getElementById('oldDeviceNode').value;
            const oldType = document.getElementById('oldDeviceType').value;
            
            const node = document.getElementById('firebaseNode').value;
            const name = document.getElementById('deviceName').value;
            const type = document.getElementById('deviceType').value;
            const description = document.getElementById('deviceDescription').value;
            
            const address = document.getElementById('locAddress').value;
            const city = document.getElementById('locCity').value;
            const province = document.getElementById('locProvince').value;

            // Validation
            if (!node || !name || !type || !address || !city || !province) {
                alert('Mohon lengkapi semua field yang diperlukan (Nama, Tipe, Node, Lokasi)!');
                return;
            }

            const deviceStatus = id ? (devices.find(d => d.id === id)?.status || 'offline') : 'offline';
            
            const payload = {
                old_node: oldNode,
                old_type: oldType,
                node: node,
                device_name: name,
                type: type,
                description: description,
                status: deviceStatus,
                location: {
                    address: address,
                    city: city,
                    province: province,
                    latitude: id ? (devices.find(d => d.id === id)?.location_data?.latitude || 0) : 0,
                    longitude: id ? (devices.find(d => d.id === id)?.location_data?.longitude || 0) : 0
                }
            };
            
            // Tampilkan state loading (opsional)
            const saveBtn = document.querySelector('button[onclick="saveDevice()"]');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
            saveBtn.disabled = true;

            fetch('{{ route("admin.save.firebase.device") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Perangkat berhasil disimpan! Silakan muat ulang halaman untuk melihat perubahan jika perlu.');
                    location.reload(); // Refresh to get fresh data from firebase
                } else {
                    alert('Gagal menyimpan perangkat ke Firebase.');
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan data.');
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        }

        // Open delete modal
        function openDeleteModal(id) {
            const device = devices.find(d => d.id === id);
            if (device) {
                deleteId = id;
                document.getElementById('deleteDeviceName').innerText = device.name;
                const modal = document.getElementById('deleteModal');
                modal.classList.remove('opacity-0', 'invisible');
                modal.classList.add('opacity-100', 'visible');
                document.body.style.overflow = 'hidden';
            }
        }

        // Close delete modal
        function closeDeleteModal() {
            deleteId = null;
            const modal = document.getElementById('deleteModal');
            modal.classList.add('opacity-0', 'invisible');
            modal.classList.remove('opacity-100', 'visible');
            document.body.style.overflow = '';
        }

        function confirmDeleteDevice() {
            if (deleteId) {
                const device = devices.find(d => d.id === deleteId);
                if (!device) return;

                const deleteBtn = document.querySelector('button[onclick="confirmDeleteDevice()"]');
                const originalText = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
                deleteBtn.disabled = true;

                fetch('{{ route("admin.delete.firebase.device") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ node: device.node, type: device.type })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Perangkat berhasil dihapus!');
                        location.reload();
                    } else {
                        alert('Gagal menghapus perangkat.');
                        deleteBtn.innerHTML = originalText;
                        deleteBtn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat menghapus data.');
                    deleteBtn.innerHTML = originalText;
                    deleteBtn.disabled = false;
                });
            }
        }

        // Initial render
        renderDevices();

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDeleteModal();
            }
        });

        // Close modal on overlay click
        document.getElementById('deviceModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });
    </script>
@endsection
