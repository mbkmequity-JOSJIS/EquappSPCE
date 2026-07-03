@props([
    'id',
    'name',
    'type',
    'status',
    'address' => null,
])

<div class="group relative overflow-hidden rounded-3xl border border-white/20 bg-white/10 p-6 shadow-glass backdrop-blur-xl transition hover:border-white/35 hover:bg-white/[0.14]">
    <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-cyan-300/10 blur-3xl"></div>
    <div class="relative flex flex-col gap-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-white">{{ $name }}</h3>
                @if ($address)
                    <p class="mt-1 text-sm text-white/70">{{ $address }}</p>
                @endif
                <p class="mt-2 text-xs uppercase tracking-wider text-white/50">Tipe alat</p>
                <p class="text-sm font-medium text-sky-100">{{ $type }}</p>
            </div>
            <x-badge :type="$status" />
        </div>
        <div class="mt-auto pt-2">
            <a
                href="{{ route('location.detail', $id) }}"
                class="inline-flex w-full items-center justify-center rounded-2xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"
            >
                Lihat Detail
                <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>
