<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="min-h-screen bg-[#122848] font-sans text-white antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-32 top-20 h-96 w-96 rounded-full bg-[#0055A0] opacity-60 blur-3xl"></div>
        <div class="absolute right-0 top-1/3 h-[28rem] w-[28rem] -translate-y-1/2 rounded-full bg-[#438BC4] opacity-45 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#8CC1E9] opacity-35 blur-3xl"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-[#122848]/90 via-[#0055A0]/55 to-[#AAA6A0]/25"></div>
    </div>

    <div class="relative z-10 flex min-h-screen">
        <aside
            class="group/sidebar fixed left-0 top-0 z-40 flex h-screen w-16 flex-col border-r border-white/10 bg-[#122848]/55 py-6 backdrop-blur-xl transition-[width] duration-300 ease-out hover:w-56"
            aria-label="Navigasi utama"
        >
            <div class="flex h-full flex-col px-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3 overflow-hidden rounded-2xl border border-white/15 bg-white/10 px-2 py-2 backdrop-blur-md">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#8CC1E9] to-[#0055A0] text-sm font-extrabold text-[#122848]">EQ</span>
                    <span class="min-w-0 opacity-0 transition-opacity duration-200 group-hover/sidebar:opacity-100">
                        <span class="block truncate text-sm font-extrabold tracking-tight">EQUAPP</span>
                        <span class="block truncate text-[10px] text-white/60">Environmental Monitor</span>
                    </span>
                </a>

                <nav class="mt-8 flex flex-1 flex-col gap-1 text-sm font-medium text-white/80">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 overflow-hidden rounded-xl px-2 py-2 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('home') ? 'bg-white/15 text-white' : '' }}">
                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5V21a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1v-10.5z"/></svg>
                        <span class="truncate opacity-0 transition-opacity duration-200 group-hover/sidebar:opacity-100">Beranda</span>
                    </a>
                    <a href="{{ route('locations.index') }}" class="flex items-center gap-3 overflow-hidden rounded-xl px-2 py-2 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('locations.index', 'location.detail') ? 'bg-white/15 text-white' : '' }}">
                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.418 0-8-1.79-8-4V7c0-2.21 3.582-4 8-4s8 1.79 8 4v10c0 2.21-3.582 4-8 4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c4.418 0 8-1.79 8-4"/></svg>
                        <span class="truncate opacity-0 transition-opacity duration-200 group-hover/sidebar:opacity-100">Lokasi &amp; Perangkat</span>
                    </a>
                    <a href="{{ route('developer.profile') }}" class="flex items-center gap-3 overflow-hidden rounded-xl px-2 py-2 transition hover:bg-white/10 hover:text-white {{ request()->routeIs('developer.profile') ? 'bg-white/15 text-white' : '' }}">
                        <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21v-1a7 7 0 0114 0v1"/></svg>
                        <span class="truncate opacity-0 transition-opacity duration-200 group-hover/sidebar:opacity-100">Profil Pengembang</span>
                    </a>
                </nav>

                <a
                    href="https://wa.me/{{ config('equapp.admin_whatsapp') }}?text={{ rawurlencode('Halo Admin EQUAPP, saya ingin melaporkan masalah dari portal publik.') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-auto flex items-center gap-3 overflow-hidden rounded-2xl border border-emerald-300/30 bg-emerald-400/15 px-2 py-2 text-emerald-50 transition hover:bg-emerald-400/25"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/30 text-lg">??</span>
                    <span class="truncate text-xs font-semibold opacity-0 transition-opacity duration-200 group-hover/sidebar:opacity-100">Laporkan Masalah</span>
                </a>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col pl-16">
            <header class="sticky top-0 z-30 border-b border-white/10 bg-[#122848]/40 px-4 py-4 backdrop-blur-lg sm:px-8">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/50">EQUAPP</p>
                        <h1 class="text-lg font-bold text-white sm:text-xl">@yield('heading')</h1>
                    </div>
                    <a
                        href="https://wa.me/{{ config('equapp.admin_whatsapp') }}?text={{ rawurlencode('Halo Admin EQUAPP, saya ingin melaporkan masalah.') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="hidden rounded-full border border-white/25 bg-white/10 px-4 py-2 text-xs font-semibold text-white shadow-neon transition hover:bg-white/20 sm:inline-flex"
                    >
                        Laporkan ke WhatsApp
                    </a>
                </div>
            </header>

            <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-10 sm:px-8">
                @yield('content')
            </main>

            <footer class="border-t border-white/10 bg-black/20 px-4 py-6 text-center text-xs text-white/50 sm:px-8">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }} � Integrated Environmental Monitoring.</p>
            </footer>
        </div>
    </div>

    <a
        href="https://wa.me/{{ config('equapp.admin_whatsapp') }}?text={{ rawurlencode('Halo Admin EQUAPP, saya ingin melaporkan masalah dari portal publik.') }}"
        target="_blank"
        rel="noopener noreferrer"
        class="fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full border border-emerald-300/40 bg-emerald-500/90 text-2xl text-white shadow-lg shadow-emerald-500/40 transition hover:scale-105 sm:hidden"
        aria-label="Laporkan masalah via WhatsApp"
    >
        ??
    </a>

    @stack('scripts')
</body>
</html>
