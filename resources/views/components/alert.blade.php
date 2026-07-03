@props([
    'variant' => 'info',
])

@php
    $styles = [
        'info' => 'border-cyan-300/30 bg-cyan-400/10 text-cyan-50',
        'warn' => 'border-[#FFA723]/40 bg-[#FFA723]/10 text-amber-50',
        'danger' => 'border-red-400/40 bg-red-500/10 text-red-50',
    ];
    $v = $styles[$variant] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['role' => 'status', 'class' => 'flex gap-3 rounded-2xl border p-4 backdrop-blur-md '.$v]) }}>
    <span class="text-xl leading-none" aria-hidden="true">??</span>
    <div class="text-sm leading-relaxed">
        {{ $slot }}
    </div>
</div>