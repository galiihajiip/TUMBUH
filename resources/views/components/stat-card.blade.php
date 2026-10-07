@props(['label', 'value', 'sub' => null, 'icon' => null, 'tone' => 'ink'])
<div {{ $attributes->merge(['class' => 'card p-5']) }}>
    <div class="flex items-center justify-between">
        <span class="text-[13px] font-medium text-slate">{{ $label }}</span>
        @if ($icon)<x-icon :name="$icon" class="{{ $tone === 'brand' ? 'text-brand' : 'text-slate-2' }}" />@endif
    </div>
    <div class="metric mt-2 text-[26px] leading-8 {{ $tone === 'brand' ? 'text-brand-deep' : '' }}">{{ $value }}</div>
    @if ($sub)<div class="mt-1 text-[12px] text-slate">{{ $sub }}</div>@endif
    {{ $slot }}
</div>
