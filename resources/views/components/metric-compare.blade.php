@props(['label', 'before', 'after', 'delta', 'direction' => 'flat', 'source' => null])
<div {{ $attributes->merge(['class' => 'grid grid-cols-[1fr_auto_1fr] items-center gap-3 border-b border-line py-3 last:border-0']) }}>
    <div>
        <div class="text-[12px] text-slate">{{ $label }}</div>
        <div class="font-display text-[18px] font-bold text-slate-2 tabular">{{ $before }}</div>
    </div>
    <x-icon name="arrow_forward" class="text-slate-2" />
    <div class="text-right">
        <div class="font-display text-[18px] font-bold tabular">{{ $after }}</div>
        <div class="text-[12px] font-semibold {{ $direction === 'up' ? 'text-ok' : ($direction === 'down' ? 'text-brand-deep' : 'text-slate') }}">{{ $delta }}</div>
    </div>
</div>
