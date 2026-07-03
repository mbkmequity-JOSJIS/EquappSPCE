<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel Admin � '.config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 flex-col border-r border-white/10 bg-slate-900/80 p-6 backdrop-blur-xl lg:flex">
            <p class="text-xs font-semibold uppercase tracking-widest text-sky-300/80">EQUAPP</p>
            <p class="mt-1 text-lg font-bold">Panel Admin</p>
            <p class="mt-4 text-xs text-slate-400">Layout siap dikembangkan sesuai dokumen fungsional (Dashboard, Lokasi, Perangkat, dll.).</p>
        </aside>
        <div class="flex-1 p-6 lg:p-10">
            @yield('content')
        </div>
    </div>
</body>
</html>
