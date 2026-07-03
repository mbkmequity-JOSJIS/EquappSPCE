@extends('layouts.app')
@section('title', 'Dashboard Module')
@vite(['resources/css/app.css', 'resources/js/app.js'])

@section('style')
    <style>
        /* Custom Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        .animate-slide-left {
            animation: slideInLeft 0.6s ease forwards;
        }

        .animate-slide-right {
            animation: slideInRight 0.6s ease forwards;
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.15);
        }

        /* Status Badge Styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .status-badge.good {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .status-badge.medium {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .status-badge.bad {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Table Row Hover */
        .indicator-row {
            transition: all 0.2s ease;
        }

        .indicator-row:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        /* Sticky Header untuk Tabel */
        .table-sticky th {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        /* Custom Scrollbar */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }

        /* Floating Animation untuk Hero */
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .float-animation {
            animation: float 4s ease-in-out infinite;
        }
    </style>
@endsection

@section('content')
    <main class="content">
        <!-- Feature Section / Hero -->
        <section id="feature" class="active relative min-h-screen overflow-hidden">
            <!-- Background Image dengan Overlay Gradient -->
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-900/50 via-slate-800/40 to-slate-900/500 z-10 l"></div>
                <div class="h-full w-full bg-cover bg-center bg-no-repeat scale-110 blur-[1px] " 
                     style="background-image: url({{ asset('images/full-team.png') }});">
                </div>
            </div>
            

            <!-- Hero Content -->
            <div class="relative z-30 flex items-center justify-center min-h-screen px-4 py-12">
                <div class="max-w-5xl mx-auto text-center animate-fade-up bg-white/10 backdrop-blur-sm rounded-3xl p-10">
                    
                    <h1 class="text-6xl text-white md:text-7xl lg:text-9xl font-black tracking-widest mb-4">
                        <span class="">EQU</span>App
                    </h1>
                    
                    <div class="inline-block mb-6 px-4 py-1 bg-sky-500/20 rounded-full">
                        <p class="text-sky-300 font-semibold tracking-wide">Environmental Quality Application</p>
                    </div>
                    
                    <p class="text-base md:text-lg text-slate-200 max-w-2xl mx-auto leading-relaxed mb-8">
                        Platform monitoring IoT berbasis web untuk pelacakan kualitas lingkungan 
                        secara real-time, khususnya kualitas air dan udara.
                    </p>
                    
                    <div class="flex flex-wrap gap-4 justify-center">
                        <button onclick="scrollToIndikator()" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-sky-500/40 cursor-pointer hover:bg-sky-600/40 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-chart-line"></i>
                            Lihat Indikator
                        </button>
                        <button onclick="window.location.href='/modul/devices/aquaviska'" 
                                class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 backdrop-blur-sm cursor-pointer hover:bg-white/20 text-white font-semibold rounded-xl transition-all duration-300 border border-white/20">
                            <i class="fas fa-microchip"></i>
                            Lihat Perangkat
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Scroll Indicator -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-30 animate-bounce cursor-pointer">
                <button onclick="scrollToIndikator()" class="text-white/60 hover:text-white/90 transition">
                    <i class="fas fa-chevron-down text-2xl"></i>
                </button>
            </div>
        </section>

        <!-- Indikator Section -->
        <section id="indikator" class="min-h-screen py-12 px-4 md:px-6 lg:px-8 bg-gradient-to-br from-slate-50 to-slate-100">
            <div class="max-w-7xl mx-auto">
                <!-- Header Section -->
                <div class=" mb-10 animate-fade-up">
                    <div class="inline-flex items-center gap-2 bg-sky-100 rounded-full px-4 py-2 mb-4">
                        <i class="fas fa-gauge-high text-sky-600"></i>
                        <span class="text-sky-700 font-semibold text-sm">REFERENSI STANDAR</span>
                    </div>
                    <h2 class="  text-3xl md:text-4xl font-bold text-slate-800 mb-3">
                        Daftar 
                        <span class=" text-sky-500 ">Indikator Sensor</span>
                    </h2>
                    <p class="text-slate-500 max-w-2xl ">
                        Semua indikator ditulis dengan satuan yang umum digunakan dan penjelasan sederhana 
                        agar mudah dimengerti oleh pengguna dashboard.
                    </p>
                </div>

                <!-- Tabel Indicators -->
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-slide-right">
                    <!-- Tabs Navigation -->
                    <div class="flex border-b border-slate-200 bg-slate-50">
                        <button onclick="showTable('aquaviska')" id="tabAquaviska" 
                                class="tab-btn active flex-1 px-6 py-4 text-center font-semibold transition-all duration-300 border-b-2 border-emerald-500 text-emerald-600 bg-white rounded-t-lg">
                            <i class="fas fa-water mr-2"></i>
                            AQUA VISKA
                            <span class="text-xs text-slate-400 ml-1">Kualitas Air</span>
                        </button>
                        <button onclick="showTable('climeet')" id="tabClimeet" 
                                class="tab-btn flex-1 px-6 py-4 text-center font-semibold transition-all duration-300 border-b-2 border-transparent text-slate-500 hover:text-orange-600">
                            <i class="fas fa-cloud-sun mr-2"></i>
                            IOT CLIMATE
                            <span class="text-xs text-slate-400 ml-1">Kualitas Udara & Iklim</span>
                        </button>
                    </div>

                    <!-- Tabel AQUAVISKA -->
                    <div id="tableAquaviska" class="table-container overflow-x-auto p-4">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-cyan-50 to-white border-b-2 border-cyan-200">
                                <tr>
                                    <th class="text-left py-4 px-4 text-sm font-bold text-cyan-700">Indikator</th>
                                    <th class="text-left py-4 px-4 text-sm font-bold text-cyan-700">Satuan</th>
                                    <th class="text-left py-4 px-4 text-sm font-bold text-cyan-700">Keterangan</th>
                                    <th class="text-left py-4 px-4 text-sm font-bold text-cyan-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-thermometer-half text-cyan-500 mr-2"></i>
                                        Suhu Air
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">°C</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Temperatur air permukaan yang diukur di lokasi.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal 25-30°C</span>
                                            <span class="status-badge medium">Waspada 20-25°C / 30-33°C</span>
                                            <span class="status-badge bad">Bahaya &lt;20°C / &gt;33°C</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-flask text-cyan-500 mr-2"></i>
                                        pH
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">skala 0–14</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Tingkat keasaman atau kebasaan air tanpa satuan.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal 6.5-8.5</span>
                                            <span class="status-badge medium">Waspada 5.5-6.5 / 8.5-9.0</span>
                                            <span class="status-badge bad">Bahaya &lt;5.5 / &gt;9.0</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-eye text-cyan-500 mr-2"></i>
                                        Kekeruhan (Turbidity)
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">NTU</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Seberapa keruh air; nilai lebih tinggi berarti air lebih keruh.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal &lt;25 NTU</span>
                                            <span class="status-badge medium">Waspada 25-50 NTU</span>
                                            <span class="status-badge bad">Bahaya &gt;50 NTU</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-wind text-cyan-500 mr-2"></i>
                                        Dissolved Oxygen (DO)
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">mg/L</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Jumlah oksigen terlarut yang tersedia di dalam air.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal &gt;5 mg/L</span>
                                            <span class="status-badge medium">Waspada 3-5 mg/L</span>
                                            <span class="status-badge bad">Bahaya &lt;3 mg/L</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-tint text-cyan-500 mr-2"></i>
                                        Total Dissolved Solids (TDS)
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">ppm</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Kadar mineral dan zat terlarut dalam air.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal &lt;500 ppm</span>
                                            <span class="status-badge medium">Waspada 500-1000 ppm</span>
                                            <span class="status-badge bad">Bahaya &gt;1000 ppm</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabel IOT CLIMATE -->
                    <div id="tableClimeet" class="table-container overflow-x-auto p-4 hidden">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-orange-50 to-white border-b-2 border-orange-200">
                                <tr>
                                    <th class="text-left py-4 px-4 text-sm font-bold text-orange-700">Indikator</th>
                                    <th class="text-left py-4 px-4 text-sm font-bold text-orange-700">Satuan</th>
                                    <th class="text-left py-4 px-4 text-sm font-bold text-orange-700">Keterangan</th>
                                    <th class="text-left py-4 px-4 text-sm font-bold text-orange-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-thermometer-half text-orange-500 mr-2"></i>
                                        Suhu Udara
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">°C</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Temperatur udara di sekitar lokasi sensor.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal 20-30°C</span>
                                            <span class="status-badge medium">Waspada 25-30°C</span>
                                            <span class="status-badge bad">Bahaya &lt;20°C / &gt;33°C</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-tint text-orange-500 mr-2"></i>
                                        Kelembapan
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">% RH</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Persentase uap air di udara.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal 40-70%</span>
                                            <span class="status-badge medium">Waspada 30-40% / 70-80%</span>
                                            <span class="status-badge bad">Bahaya &lt;30% / &gt;80%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-smog text-orange-500 mr-2"></i>
                                        TVOC
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">mg/m³</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Kadar senyawa organik volatil di udara.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal &lt;0.5 mg/m³</span>
                                            <span class="status-badge medium">Waspada 0.5-1.0 mg/m³</span>
                                            <span class="status-badge bad">Bahaya &gt;1.0 mg/m³</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-cloud text-orange-500 mr-2"></i>
                                        CO₂
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">ppm</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Kadar karbon dioksida di udara.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal &lt;450 ppm</span>
                                            <span class="status-badge medium">Waspada 450-1000 ppm</span>
                                            <span class="status-badge bad">Bahaya &gt;1000 ppm</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-sun text-orange-500 mr-2"></i>
                                        UV Index
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">skala</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Intensitas sinar ultraviolet yang mencapai permukaan.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal 0-5</span>
                                            <span class="status-badge medium">Waspada 6-8</span>
                                            <span class="status-badge bad">Bahaya 9-11+</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-wind text-orange-500 mr-2"></i>
                                        Kecepatan Angin
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">m/s</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Kecepatan angin di sekitar area sensor.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal 0-5 m/s</span>
                                            <span class="status-badge medium">Waspada 5-10 m/s</span>
                                            <span class="status-badge bad">Bahaya &gt;10 m/s</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="indicator-row border-b border-slate-100">
                                    <td class="py-4 px-4 font-semibold text-slate-700">
                                        <i class="fas fa-cloud-rain text-orange-500 mr-2"></i>
                                        Curah Hujan
                                    </td>
                                    <td class="py-4 px-4 text-slate-600">mm</td>
                                    <td class="py-4 px-4 text-slate-500 text-sm">Jumlah hujan yang tercatat dalam periode tertentu.</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="status-badge good">Normal 0-20 mm</span>
                                            <span class="status-badge medium">Waspada 20-50 mm</span>
                                            <span class="status-badge bad">Bahaya &gt;50 mm</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Note -->
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs text-slate-500 flex items-center gap-2">
                                <i class="fas fa-info-circle text-emerald-500"></i>
                                Status berdasarkan standar baku mutu lingkungan yang berlaku
                            </p>
                            <div class="flex gap-3 text-xs">
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Normal</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Waspada</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500"></span> Bahaya</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>
@endsection

@section('script')
    <script>
        // Smooth scroll ke indikator section
        function scrollToIndikator() {
            const indikatorSection = document.getElementById('indikator');
            if (indikatorSection) {
                indikatorSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Show/Hide Table berdasarkan tab
        function showTable(type) {
            const tableAqua = document.getElementById('tableAquaviska');
            const tableClimeet = document.getElementById('tableClimeet');
            const tabAqua = document.getElementById('tabAquaviska');
            const tabClimeet = document.getElementById('tabClimeet');
            
            if (type === 'aquaviska') {
                tableAqua.classList.remove('hidden');
                tableClimeet.classList.add('hidden');
                tabAqua.classList.add('border-emerald-500', 'text-emerald-600', 'bg-white');
                tabAqua.classList.remove('border-transparent', 'text-slate-500');
                tabClimeet.classList.remove('border-emerald-500', 'text-emerald-600', 'bg-white');
                tabClimeet.classList.add('border-transparent', 'text-slate-500');
            } else {
                tableClimeet.classList.remove('hidden');
                tableAqua.classList.add('hidden');
                tabClimeet.classList.add('border-orange-500', 'text-orange-600', 'bg-white');
                tabClimeet.classList.remove('border-transparent', 'text-slate-500');
                tabAqua.classList.remove('border-orange-500', 'text-orange-600', 'bg-white');
                tabAqua.classList.add('border-transparent', 'text-slate-500');
            }
        }

        // Initialize dengan AQUAVISKA sebagai default
        document.addEventListener('DOMContentLoaded', function() {
            showTable('aquaviska');
            
            // Add animation delay to rows
            const rows = document.querySelectorAll('.indicator-row');
            rows.forEach((row, index) => {
                row.style.animation = `slideInLeft ${0.3 + index * 0.05}s ease forwards`;
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
            });
            
            // Fix animation after load
            setTimeout(() => {
                rows.forEach(row => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                });
            }, 100);
        });
    </script>
@endsection