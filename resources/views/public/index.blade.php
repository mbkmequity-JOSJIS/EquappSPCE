@extends('layouts.public')

@section('title', 'Beranda � '.config('app.name'))
@section('heading', 'Beranda')

@section('content')
    @include('landing.hero')

    <section class="mt-12 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white">Ringkasan Lokasi</h2>
                    <p class="mt-1 text-sm text-white/65">Kartu lokasi memuat nama, tipe alat, dan status terkini.</p>
                </div>
            </div>

            <div
                class="mt-6 grid gap-5 sm:grid-cols-2"
                x-data="{ cardsReady: false }"
                x-init="setTimeout(() => cardsReady = true, 500)"
            >
                <template x-if="!cardsReady">
                    <div class="contents">
                        <x-skeleton-card />
                        <x-skeleton-card class="hidden sm:block" />
                    </div>
                </template>
                <template x-if="cardsReady">
                    <div class="contents">
                        @foreach ($locations as $loc)
                            <x-location-card
                                :id="$loc['id']"
                                :name="$loc['name']"
                                :type="$loc['type']"
                                :status="$loc['status']"
                            />
                        @endforeach
                    </div>
                </template>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-white/15 bg-white/10 p-6 shadow-glass backdrop-blur-xl">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-white/70">Statistik Singkat</h3>
                <dl class="mt-4 space-y-4 text-sm">
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-white/65">Perangkat aktif</dt>
                        <dd class="text-lg font-bold text-white">{{ $totalDevices }}</dd>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-white/65">Lokasi terdaftar</dt>
                        <dd class="text-lg font-bold text-white">{{ $totalLocations }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        <dt class="text-white/65">Beroperasi sejak</dt>
                        <dd class="mt-1 font-semibold text-sky-100">23 Oktober 2025</dd>
                    </div>
                </dl>
            </div>

            @include('partials.bmkg-widget')
        </div>
    </section>

    <section class="mt-14 rounded-3xl border border-white/15 bg-white/10 p-8 text-white/85 shadow-glass backdrop-blur-xl">
        <h2 class="text-lg font-bold text-white">Peran alat di lapangan</h2>
        <p class="mt-3 max-w-3xl text-sm leading-relaxed">
            AQUAVISKA memantau parameter kualitas air (pH, TDS, kekeruhan, suhu permukaan), sementara IOT Climate
            mencatat variabel udara sekitar sensor. Data dikirim ke EQUAPP untuk divisualisasikan dan dinilai otomatis.
        </p>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-white/15 bg-[#0055A0]/25 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-sky-200/80">AQUAVISKA</p>
                <p class="mt-2 text-sm text-white/80">Kesiapan air baku, embung, dan saluran irigasi.</p>
            </div>
            <div class="rounded-2xl border border-white/15 bg-[#438BC4]/20 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-sky-100/90">IOT Climate</p>
                <p class="mt-2 text-sm text-white/80">Profil mikroklimat untuk peringatan dini kelembaban & suhu ekstrem.</p>
            </div>
        </div>
    </section>
@endsection
