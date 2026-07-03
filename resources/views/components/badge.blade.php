@props([
    'type' => 'normal',
])

@php
    $map = [
        'normal' => ['label' => 'Normal', 'class' => 'bg-emerald-500/20 text-emerald-100 border-emerald-300/40'],
        'waspada' => ['label' => 'Waspada', 'class' => 'bg-[#FFA723]/20 text-amber-50 border-[#FFA723]/50 shadow-[0_0_12px_rgba(255,167,35,0.35)]'],
        'bahaya' => ['label' => 'Bahaya', 'class' => 'bg-red-500/20 text-red-100 border-red-400/50 shadow-[0_0_14px_rgba(239,68,68,0.45)]'],
        'offline' => ['label' => 'Tidak Merespons', 'class' => 'bg-red-600/25 text-red-50 border-red-400/60'],
    ];
    $t = strtolower((string) $type);
    $cfg = $map[$t] ?? $map['normal'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full border px-3 py-0.5 text-xs font-semibold tracking-wide backdrop-blur-sm '.$cfg['class']]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-80"></span>
    {{ $cfg['label'] }}
</span>