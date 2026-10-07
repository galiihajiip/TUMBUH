@props(['text' => 'Demo Simulation — data dan integrasi pada halaman ini disimulasikan secara lokal untuk prototipe. Bukan data produksi Telkomsel.'])
<p {{ $attributes->merge(['class' => 'flex items-start gap-2 text-[12px] text-slate']) }}><x-icon name="science" size="16" class="text-[#6d28d9]" /><span>{{ $text }}</span></p>
