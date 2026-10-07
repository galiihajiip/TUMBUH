@props(['value' => 0, 'size' => 120, 'stroke' => 10, 'label' => '/100'])
@php($r = ($size - $stroke) / 2)
@php($c = 2 * M_PI * $r)
<div {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center']) }} style="width: {{ $size }}px; height: {{ $size }}px">
    <svg width="{{ $size }}" height="{{ $size }}" class="-rotate-90" aria-hidden="true">
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="#eef2f6" stroke-width="{{ $stroke }}" />
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="#d81b27" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $c * (1 - max(0, min(100, $value)) / 100) }}" />
    </svg>
    <div class="absolute text-center leading-none">
        <div class="metric" style="font-size: {{ round($size * 0.3) }}px">{{ $value }}</div>
        <div class="mt-1 text-[11px] font-medium text-slate">{{ $label }}</div>
    </div>
</div>
