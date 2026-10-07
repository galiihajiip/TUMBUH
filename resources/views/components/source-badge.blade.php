@props(['source' => 'self_reported'])
@php
    $label = config("tumbuh.source_labels.{$source}.label", ucfirst(str_replace('_', ' ', $source)));
    $style = config("tumbuh.source_labels.{$source}.style", 'dashed');
    $cls = [
        'dashed' => 'bg-[#f8fafc] text-slate border border-dashed border-line-strong',
        'info' => 'bg-info-tint text-info border border-[#dbeafe]',
        'ok' => 'bg-ok-tint text-ok border border-[#a7f3d0]',
        'warn' => 'bg-warn-tint text-warn border border-[#fde68a]',
        'sim' => 'bg-[#f5f3ff] text-[#6d28d9] border border-[#ddd6fe]',
    ][$style];
@endphp
<span {{ $attributes->merge(['class' => "pill {$cls}"]) }} title="Sumber data: {{ $label }}">
    @if ($style === 'info')<span class="h-1.5 w-1.5 rounded-full bg-ok"></span>@endif
    {{ $label }}
</span>
