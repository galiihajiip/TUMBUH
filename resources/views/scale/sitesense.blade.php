<x-layouts.app title="SiteSense">
    <x-page-header eyebrow="SiteSense · Location intelligence (Simulated)" title="Expansion recommendation" subtitle="Masukkan kriteria ekspansi; mesin skor lokal membandingkan lokasi kandidat dari dataset simulasi." />
    @include('partials.nav-scale')
    <div class="grid gap-6 lg:grid-cols-[380px_1fr]">
        <form method="POST" action="{{ route('scale.sitesense.run') }}" class="card space-y-4 p-6">@csrf
            <x-field name="current_location" label="Lokasi saat ini" :value="$latest->inputs['current_location'] ?? $business->locationLabel()" required />
            <x-field name="business_type" label="Jenis bisnis" :value="$latest->inputs['business_type'] ?? $business->sub_category" required />
            <x-select name="target_customer" label="Target pelanggan" :options="$targets" :value="$latest->inputs['target_customer'] ?? 'young_pro'" />
            <x-field name="budget" label="Budget ekspansi" type="number" prefix="Rp" :value="$latest->inputs['budget'] ?? 60000000" required />
            <x-field name="preferred_area" label="Area preferensi" :value="$latest->inputs['preferred_area'] ?? ''" placeholder="mis. Barat" />
            <div class="grid grid-cols-2 gap-3"><x-field name="store_size" label="Luas toko (m²)" type="number" :value="$latest->inputs['store_size'] ?? 40" required /><x-select name="competition_tolerance" label="Toleransi kompetitor" :options="['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi']" :value="$latest->inputs['competition_tolerance'] ?? 'medium'" /></div>
            <x-field name="desired_market" label="Pasar yang diinginkan" :value="$latest->inputs['desired_market'] ?? ''" />
            <button class="btn-primary w-full">Analisis lokasi</button>
        </form>
        <div>
            @if ($latest)
                @php($cands = $latest->result['candidates'])
                <div class="space-y-4">
                    @foreach ($cands as $i => $c)
                        <div class="card p-5 {{ $i === 0 ? 'border-brand shadow-[var(--shadow-spotlight)]' : '' }}">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div><div class="eyebrow{{ $i === 0 ? '-brand' : '' }}">{{ $i === 0 ? 'Recommended location' : 'Alternatif #'.($i + 1) }}</div><h3 class="text-[18px] font-semibold">{{ $c['name'] }}</h3><div class="text-[12px] text-slate">Estimasi sewa {{ rupiah_short($c['rent']) }}/bln</div></div>
                                <div class="text-right"><div class="text-[11px] text-slate">Score</div><div class="metric text-[32px]">{{ $c['score'] }}</div></div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-5">
                                @foreach ($c['labels'] as $l => $v)<div class="module p-2 text-center"><div class="text-[10px] text-slate">{{ $l }}</div><div class="text-[12px] font-bold {{ ($l === 'Competition' ? $v === 'LOW' : $v === 'HIGH') ? 'text-ok' : ($v === 'MEDIUM' ? 'text-[#b45309]' : 'text-brand-deep') }}">{{ $v }}</div></div>@endforeach
                            </div>
                            @if ($i === 0)<ul class="mt-3 space-y-1 text-[13px]">@foreach ($c['rationale'] as $r)<li class="flex gap-2"><x-icon name="check" size="16" class="text-ok" />{{ $r }}</li>@endforeach</ul>@endif
                        </div>
                    @endforeach
                </div>
                <x-sim-note class="mt-4" :text="$latest->result['engine'].' · dihitung '.$latest->created_at->diffForHumans().'. Bukan hasil SiteSense aktual.'" />
            @else
                <x-empty-state icon="location_on" title="Belum ada analisis" text="Isi kriteria di samping untuk mendapatkan rekomendasi lokasi." />
            @endif
        </div>
    </div>
</x-layouts.app>
