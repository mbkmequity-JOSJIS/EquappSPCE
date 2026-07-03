@extends('layouts.app')
@section('title', 'IOT Device')

@section('style')
    <style>
        .device-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .device-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .device-card:hover::before {
            left: 100%;
        }
        
        .device-card:hover {
            transform: translateY(-12px);
        }
        
        .device-icon {
            transition: all 0.5s ease;
        }
        
        .device-card:hover .device-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .floating-bg-icon {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }
        
        .pulse-ring {
            position: absolute;
            border-radius: 50%;
            animation: pulse 2s ease-out infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .device-card {
                min-height: 300px;
            }
            
            .device-card .text-8xl {
                font-size: 2.5rem;
            }
        }
        
        /* Stats counter animation */
        .stat-number {
            transition: all 0.3s ease;
        }
    </style>
@endsection

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="relative mb-12">
                
                <div class="relative text-center lg:text-left">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <h1 class="text-4xl sm:text-5xl lg:text-8xl font-bold text-slate-800 mb-4">
                                <span class="bg-gradient-to-r from-sky-600 to-sky-400 bg-clip-text text-transparent">EQUApp</span>
                                <span class="text-slate-700 lg:text-6xl"> Devices</span>
                            </h1>
                            <p class="text-lg text-slate-600 max-w-2xl">
                                Monitor and manage your environmental monitoring devices in real-time. 
                                Get instant insights about water quality and climate conditions.
                            </p>
                        </div>
                        
                        <!-- Stats Badge -->
                        <div class="flex gap-4 justify-center lg:justify-end">
                            <div class="bg-white rounded-2xl shadow-lg px-6 py-4 text-center min-w-[120px]">
                                <div class="text-2xl font-bold text-sky-600">2</div>
                                <div class="text-xs text-slate-500">Device Types</div>
                            </div>
                            <div class="bg-white rounded-2xl shadow-lg px-6 py-4 text-center min-w-[120px]">
                                <div class="text-2xl font-bold text-emerald-600" id="totalDevices">4</div>
                                <div class="text-xs text-slate-500">Active Devices</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Device Cards Section -->
            <div class="grid lg:grid-cols-2 gap-8 mt-8">
                <!-- CLIMEET Card -->
                <a href="{{ route('device.list', 'climeet') }}" 
                   class="device-card group relative block bg-gradient-to-br from-orange-500 to-orange-700 rounded-3xl shadow-2xl overflow-hidden opacity-50 min-h-[400px] lg:min-h-[500px]">
                    
                    <!-- Floating Background Icon -->
                    <div class="floating-bg-icon absolute -right-20 -bottom-20 opacity-10">
                        <i class="fa-solid fa-cloud-sun text-[20rem]"></i>
                    </div>
                    
                    
                    <!-- Pulse Ring Animation -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        <div class="pulse-ring w-32 h-32 bg-white/10"></div>
                        <div class="pulse-ring w-32 h-32 bg-white/10 animation-delay-1000"></div>
                    </div>
                    
                    <div class="relative h-full flex flex-col items-center justify-center p-8 text-white">
                        <!-- Icon -->
                        <div class="device-icon mb-8">
                            <div class="w-32 h-32 bg-white/10 rounded-3xl flex items-center justify-center backdrop-blur-sm shadow-xl">
                                <i class="fa-solid fa-cloud-sun text-6xl"></i>
                            </div>
                        </div>
                        
                        <!-- Title -->
                        <h2 class="text-5xl sm:text-6xl lg:text-7xl font-black mb-4 tracking-tighter text-center">
                            CLIMEET
                        </h2>
                        
                        <!-- Description -->
                        <p class="text-lg text-white/80 text-center max-w-md mb-6">
                            Climate & Weather Monitoring System
                        </p>
                        
                        <!-- Feature Tags -->
                        <div class="flex flex-wrap justify-center gap-2 mb-8">
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">Temperature</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">Humidity</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">Air Quality</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">UV Index</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">Rainfall</span>
                        </div>
                        
                        <!-- CTA Button -->
                        <div class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 rounded-full backdrop-blur-sm group-hover:bg-white/30 transition-all duration-300">
                            <span class="font-semibold">Explore Devices</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </a>
                
                <!-- AQUAVISKA Card -->
                <a href="{{ route('device.list', 'aquaviska') }}" 
                   class="device-card group relative block bg-gradient-to-br from-sky-500 to-sky-700 rounded-3xl shadow-2xl overflow-hidden min-h-[400px] lg:min-h-[500px] ">
                    
                    <!-- Floating Background Icon -->
                    <div class="floating-bg-icon absolute -right-20 -bottom-20 opacity-10">
                        <i class="fa-solid fa-water text-[20rem]"></i>
                    </div>
                    
                    
                    <!-- Pulse Ring Animation -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        <div class="pulse-ring w-32 h-32 bg-white/10"></div>
                        <div class="pulse-ring w-32 h-32 bg-white/10 animation-delay-1000"></div>
                    </div>
                    
                    <div class="relative h-full flex flex-col items-center justify-center p-8 text-white">
                        <!-- Icon -->
                        <div class="device-icon mb-8">
                            <div class="w-32 h-32 bg-white/10 rounded-3xl flex items-center justify-center backdrop-blur-sm shadow-xl">
                                <i class="fa-solid fa-water text-6xl"></i>
                            </div>
                        </div>
                        
                        <!-- Title -->
                        <h2 class="text-5xl sm:text-6xl lg:text-7xl font-black mb-4 tracking-tighter text-center">
                            AQUAVISKA
                        </h2>
                        
                        <!-- Description -->
                        <p class="text-lg text-white/80 text-center max-w-md mb-6">
                            Water Quality Monitoring System
                        </p>
                        
                        <!-- Feature Tags -->
                        <div class="flex flex-wrap justify-center gap-2 mb-8">
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">pH Level</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">TDS</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">Turbidity</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">Dissolved O₂</span>
                            <span class="px-3 py-1 bg-white/20 rounded-full text-xs backdrop-blur-sm">Temperature   </span>
                        </div>
                        
                        <!-- CTA Button -->
                        <div class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 rounded-full backdrop-blur-sm group-hover:bg-white/30 transition-all duration-300">
                            <span class="font-semibold">Explore Devices</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </a>
            </div>
            
            
            <!-- Footer Note -->
            <div class="mt-8 text-center">
                <p class="text-xs text-slate-400 flex items-center justify-center gap-2">
                    <i class="fas fa-shield-alt text-green-500"></i>
                    Secure IoT Platform
                    <i class="fas fa-circle text-[6px] text-slate-400"></i>
                    <i class="fas fa-cloud-upload-alt text-sky-500"></i>
                    Cloud Sync Enabled
                </p>
            </div>
        </div>
    </div>

    <style>
        .animation-delay-1000 {
            animation-delay: 1s;
        }
    </style>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate stats counter
        const animateNumber = (element, target) => {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 20);
        };
        
        // Simulate total devices count (you can replace with actual API call)
        const totalDevicesElement = document.getElementById('totalDevices');
        if (totalDevicesElement) {
            // You can fetch actual device count from API here
            // For demo, using random number between 5-25
            // const mockDeviceCount = Math.floor(Math.random() * 20) + 5;
            const mockDeviceCount = 4;
            animateNumber(totalDevicesElement, mockDeviceCount);
        }
        
        // Add intersection observer for card animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe device cards
        document.querySelectorAll('.device-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease-out';
            observer.observe(card);
        });
    });
</script>
@endsection