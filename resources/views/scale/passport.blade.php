<x-layouts.app title="Growth Passport">
    <x-page-header eyebrow="Paspor pertumbuhan" title="Growth Passport" subtitle="Level dicapai lewat kriteria performa — bukan jumlah pelatihan. Setiap level harus memenuhi semua kriteria level sebelumnya." />
    @include('partials.nav-scale')
    @php($keys = array_keys($evaluation))
    @php($cur = array_search($level, $keys))
    <div class="mb-6 card flex flex-wrap items-center gap-5 p-6">
        <span class="flex h-16 w-16 items-center justify-center rounded-[18px] bg-brand text-white"><x-icon name="badge" size="32" fill /></span>
        <div><div class="eyebrow">Level saat ini</div><div class="font-display text-[28px] font-extrabold uppercase">{{ $evaluation[$level]['label'] }}</div>
            @if (isset($keys[$cur + 1]))<div class="text-[13px] text-slate">Berikutnya: <b>{{ $evaluation[$keys[$cur + 1]]['label'] }}</b> — {{ collect($evaluation[$keys[$cur + 1]]['criteria'])->where('met', false)->count() }} kriteria tersisa</div>@endif
        </div>
    </div>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($evaluation as $k => $lvl)
            @php($i = $loop->index)
            <div class="card p-5 {{ $i === $cur ? 'border-brand shadow-[var(--shadow-spotlight)]' : ($i > $cur ? 'opacity-90' : '') }}">
                <div class="flex items-center justify-between"><span class="text-[11px] font-semibold text-slate">LEVEL {{ $i + 1 }}</span>@if ($i < $cur || $i === $cur)<span class="pill bg-ok-tint text-ok"><x-icon name="check_circle" size="13" />{{ $i === $cur ? 'Level Anda' : 'Tercapai' }}</span>@else<span class="pill bg-muted text-slate"><x-icon name="lock" size="13" />Terkunci</span>@endif</div>
                <div class="mt-1 font-display text-[18px] font-extrabold uppercase">{{ $lvl['label'] }}</div>
                <ul class="mt-3 space-y-2 text-[13px]">
                    @foreach ($lvl['criteria'] as $c)
                        <li class="flex gap-2"><x-icon :name="$c['met'] ? 'check_circle' : 'radio_button_unchecked'" size="18" class="{{ $c['met'] ? 'text-ok' : 'text-slate-2' }}" :fill="$c['met']" /><span><span class="{{ $c['met'] ? '' : 'text-slate' }}">{{ $c['label'] }}</span><span class="block text-[11px] text-slate">{{ $c['detail'] }}</span></span></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</x-layouts.app>
