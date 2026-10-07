<x-layouts.app title="Expansion Readiness">
    <x-page-header eyebrow="Kesiapan ekspansi bisnis" title="Expansion Readiness" subtitle="Skor deterministik dari Growth Score, tren omzet, retensi, keuangan, kapasitas, dan kematangan digital." />
    @include('partials.nav-scale')
    <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
        <div class="card p-6">
            <div class="flex items-center gap-5"><x-score-ring :value="$readiness['score']" :size="140" :stroke="12" /><div><div class="eyebrow">Expansion Readiness</div><div class="text-[18px] font-semibold">{{ $readiness['verdict'] }}</div><div class="text-[12px] text-slate">Tren omzet 3 bulan: {{ signed($readiness['revenue_growth'], 1, '%') }}</div></div></div>
            <div class="mt-6 space-y-3">
                @foreach ($readiness['components'] as $c)
                    <div><div class="mb-1 flex justify-between text-[13px]"><span>{{ $c['label'] }} <span class="text-slate">· {{ (int) ($c['weight'] * 100) }}%</span></span><b>{{ $c['value'] }}</b></div><x-progress :value="$c['value']" :tone="$c['value'] >= 70 ? 'ink' : 'warn'" /></div>
                @endforeach
            </div>
        </div>
        <div class="space-y-6">
            <form method="POST" action="{{ route('scale.readiness.assess') }}" class="card p-6">@csrf
                <h3 class="text-[16px] font-semibold">Input expansion criteria</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-select name="capacity" label="Kapasitas operasional" :options="['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi']" :value="$inputs['capacity'] ?? 'medium'" />
                    <x-field name="budget" label="Budget ekspansi" type="number" prefix="Rp" :value="$inputs['budget'] ?? 50000000" required />
                    <x-field name="target_area" label="Area yang diincar" :value="$inputs['target_area'] ?? ''" placeholder="mis. Surabaya Barat" />
                    <x-field name="customer_concentration" label="Konsentrasi pelanggan saat ini" :value="$inputs['customer_concentration'] ?? $business->locationLabel()" />
                </div>
                <button class="btn-primary mt-5">Hitung ulang readiness</button>
            </form>
            <div class="card p-6">
                <h3 class="text-[16px] font-semibold">Langkah berikutnya</h3>
                @if ($advanced)
                    <div class="mt-3 grid gap-3 sm:grid-cols-2"><a href="{{ route('scale.msight') }}" class="module p-4 hover:border-ink"><x-icon name="query_stats" class="text-brand" /><div class="mt-2 font-semibold">MSIGHT</div><div class="text-[12px] text-slate">Validasi konsentrasi pelanggan</div></a><a href="{{ route('scale.sitesense') }}" class="module p-4 hover:border-ink"><x-icon name="location_on" class="text-brand" /><div class="mt-2 font-semibold">SiteSense</div><div class="text-[12px] text-slate">Rekomendasi lokasi</div></a></div>
                @else
                    <p class="mt-2 text-[13px] text-slate">Expansion recommendation (MSIGHT & SiteSense) tersedia di Gold. Di Silver: readiness assessment & DCE eligibility.</p><a href="{{ route('scale.dce') }}" class="btn-secondary btn-sm mt-3">Cek DCE</a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
