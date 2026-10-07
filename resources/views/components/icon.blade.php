@props(['name', 'fill' => false, 'size' => null])
<span {{ $attributes->merge(['class' => 'material-symbols-outlined'.($fill ? ' icon-fill' : '')]) }} @if ($size) style="font-size: {{ $size }}px" @endif aria-hidden="true">{{ $name }}</span>
