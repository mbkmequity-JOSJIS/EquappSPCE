@extends('layouts.public')

@section('title', 'Profil Pengembang � '.config('app.name'))
@section('heading', 'Profil Pengembang')

@section('content')
    <section class="rounded-3xl border border-white/15 bg-white/10 p-8 shadow-glass backdrop-blur-xl">
        <h2 class="text-xl font-bold text-white">Profil Tim</h2>
        <p class="mt-2 max-w-2xl text-sm text-white/70">Foto, nama, peran, dan kontak ringkas sesuai struktur dokumen fungsional.</p>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($team as $member)
                <div class="rounded-2xl border border-white/15 bg-white/5 p-5 backdrop-blur-md">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#8CC1E9] to-[#0055A0] text-lg font-extrabold text-[#122848]">
                        {{ $member['initials'] }}
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-white">{{ $member['name'] }}</h3>
                    <p class="mt-1 text-sm text-sky-100/90">{{ $member['role'] }}</p>
                    <p class="mt-3 text-xs text-white/55">Instagram</p>
                    <p class="text-sm font-medium text-white/85">{{ $member['instagram'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-10 rounded-3xl border border-white/15 bg-white/10 p-8 shadow-glass backdrop-blur-xl">
        <h2 class="text-xl font-bold text-white">Profil Program MBKM</h2>
        <div class="mt-4 space-y-4 text-sm leading-relaxed text-white/80">
            <p>{{ $program['title'] }}</p>
            <p>{{ $program['body'] }}</p>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <dt class="text-xs uppercase tracking-wider text-white/50">Institusi / mitra</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $program['institution'] }}</dd>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <dt class="text-xs uppercase tracking-wider text-white/50">Periode pelaksanaan</dt>
                    <dd class="mt-1 font-semibold text-white">{{ $program['period'] }}</dd>
                </div>
            </dl>
        </div>
    </section>
@endsection
