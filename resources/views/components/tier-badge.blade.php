@props(['tier' => 'bronze', 'theme' => false])
@php($c = ['bronze' => 'bg-bronze-tint text-bronze border-[#efd9c4]', 'silver' => 'bg-silver-tint text-silver border-[#dde3ec]', 'gold' => 'bg-gold-tint text-gold border-[#f3e8c4]'][$tier] ?? '')
<span {{ $attributes->merge(['class' => "pill border uppercase tracking-[0.06em] {$c}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ config("tumbuh.tiers.{$tier}.label") }}@if ($theme) · {{ config("tumbuh.tiers.{$tier}.theme") }}@endif
</span>
