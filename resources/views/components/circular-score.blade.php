@props([
    'score' => 0,
])

@php
    $s = max(0, min(100, (int) $score));
    $r = 52;
    $c = 2 * pi() * $r;
    $offset = $c * (1 - $s / 100);
    $color = $s >= 70 ? '#10B981' : ($s >= 45 ? '#FFA723' : '#EF4444');
@endphp

<div {{ $attributes->merge(['class' => 'relative flex h-40 w-40 items-center justify-center']) }}>
    <svg class="h-full w-full -rotate-90 transform" viewBox="0 0 120 120" aria-hidden="true">
        <circle cx="60" cy="60" r="{{ $r }}" stroke="rgba(255,255,255,0.12)" stroke-width="10" fill="none" />
        <circle
            cx="60"
            cy="60"
            r="{{ $r }}"
            stroke="{{ $color }}"
            stroke-width="10"
            fill="none"
            stroke-linecap="round"
            stroke-dasharray="{{ $c }}"
            stroke-dashoffset="{{ $offset }}"
            class="drop-shadow-[0_0_12px_rgba(140,193,233,0.55)]"
        />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
        <span class="text-3xl font-extrabold text-white">{{ $s }}</span>
        <span class="text-[10px] font-semibold uppercase tracking-widest text-white/60">Skor</span>
    </div>
</div>