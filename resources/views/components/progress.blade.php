@props(['value' => 0, 'tone' => 'ink', 'height' => 'h-1.5'])
@php($c = ['ink' => 'bg-ink', 'brand' => 'bg-brand', 'ok' => 'bg-ok', 'warn' => 'bg-[#d97706]', 'info' => 'bg-info', 'gold' => 'bg-gold'][$tone] ?? 'bg-ink')
<div {{ $attributes->merge(['class' => "w-full overflow-hidden rounded-full bg-muted {$height}"]) }} role="progressbar" aria-valuenow="{{ (int) $value }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full {{ $c }}" style="width: {{ max(0, min(100, (float) $value)) }}%"></div>
</div>
