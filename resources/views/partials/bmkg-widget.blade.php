<div
    class="rounded-3xl border border-white/15 bg-white/10 p-6 shadow-glass backdrop-blur-xl"
    x-data="bmkgWidget('{{ route('api.bmkg.forecast') }}')"
    x-init="load()"
>
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider text-white/70">Widget Cuaca BMKG</h3>
            <p class="mt-1 text-xs text-white/55">Prakiraan wilayah (integrasi API BMKG melalui server aplikasi)</p>
        </div>
        <span class="rounded-full border border-white/15 bg-white/5 px-2 py-0.5 text-[10px] text-white/60" x-show="demo" x-cloak>Demo</span>
    </div>

    <div class="mt-5 space-y-3" x-show="loading">
        <div class="h-3 w-2/3 animate-pulse rounded bg-white/15"></div>
        <div class="h-3 w-1/2 animate-pulse rounded bg-white/10"></div>
        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="h-16 animate-pulse rounded-2xl bg-white/10"></div>
            <div class="h-16 animate-pulse rounded-2xl bg-white/10"></div>
        </div>
    </div>

    <div class="mt-5 space-y-4 text-sm" x-show="!loading && error" x-cloak>
        <p class="rounded-2xl border border-red-400/30 bg-red-500/10 px-3 py-2 text-red-100" x-text="error"></p>
    </div>

    <div class="mt-5 space-y-4 text-sm" x-show="!loading && !error" x-cloak>
        <p class="text-xs text-white/55">Terbit: <span class="font-medium text-white/80" x-text="issue"></span></p>
        <p class="text-base font-semibold text-white" x-text="area"></p>
        <p class="text-white/75" x-text="weather"></p>
        <dl class="grid grid-cols-2 gap-3 text-xs">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                <dt class="text-white/55">Suhu min�maks</dt>
                <dd class="mt-1 text-sm font-bold text-sky-100"><span x-text="tmin"></span> � <span x-text="tmax"></span> �C</dd>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                <dt class="text-white/55">Kelembaban min�maks</dt>
                <dd class="mt-1 text-sm font-bold text-sky-100"><span x-text="hmin"></span> � <span x-text="hmax"></span> %</dd>
            </div>
        </dl>
    </div>
</div>
