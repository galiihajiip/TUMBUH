@props(['inverse' => false, 'size' => 'md', 'sub' => true])
{{-- Replaceable logo slot. Set TUMBUH_LOGO to the team's final SVG/PNG and the wordmark is swapped everywhere. --}}
@php($logo = config('tumbuh.brand.logo'))
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }} data-logo-slot>
    @if ($logo)
        <img src="{{ asset($logo) }}" alt="TUMBUH — Telkomsel untuk UMKM Tumbuh" class="{{ $size === 'lg' ? 'h-10' : 'h-8' }} w-auto">
    @else
        <span class="font-display font-bold tracking-[-0.01em] {{ $size === 'lg' ? 'text-[28px]' : ($size === 'sm' ? 'text-[18px]' : 'text-[22px]') }} {{ $inverse ? 'text-white' : 'text-ink' }}">TUMBUH<span class="text-brand">.</span></span>
        @if ($sub)
            <span class="hidden border-l pl-2.5 text-[10px] font-semibold uppercase leading-[12px] tracking-[0.08em] sm:block {{ $inverse ? 'border-white/30 text-white/70' : 'border-line text-slate' }}">Telkomsel untuk<br>UMKM Tumbuh</span>
        @endif
    @endif
</span>
