<x-layouts.app title="Before vs After">
    <x-page-header eyebrow="Evaluasi dampak" title="Before vs After" subtitle="Perbandingan kondisi sebelum bergabung TUMBUH dengan hasil setelah aksi pertumbuhan." />
    @include('partials.nav-growth')
    @if (! $comparison)
        <x-empty-state icon="hourglass_top" title="Menunggu hasil 30 hari pertama" text="Before vs After muncul setelah ada data bulan berikutnya di Growth Ledger. Selesaikan misi, lalu laporkan KPI bulanan (atau gunakan 'Close 30-Day Cycle' di Demo Panel).">
            <a href="{{ route('missions.index') }}" class="btn-primary">Lanjutkan misi</a><a href="{{ route('demo.index') }}" class="btn-secondary">Demo Panel</a>
        </x-empty-state>
    @else
        @php($c = $comparison)
        @php($rev = collect($c['rows'])->firstWhere('metric', 'revenue'))
        <div class="mb-6 rounded-[var(--radius-card)] border-l-4 border-l-brand bg-white p-5 shadow-[var(--shadow-card)] md:p-6">
            <div class="eyebrow-brand">Fokus siklus berhasil diukur</div>
            <h2 class="mt-1 text-[22px] font-bold">{{ $business->name }}: {{ $rev && $rev['delta']['direction'] === 'up' ? 'omzet naik '.pct($rev['delta']['relative']).' sejak bergabung' : 'dampak siklus tercatat' }}</h2>
            <p class="mt-1 text-[14px] text-slate">{{ $c['baseline']->period->translatedFormat('F Y') }} (baseline) → {{ $c['latest']->period->translatedFormat('F Y') }} · Growth Score {{ $c['score_before'] ?? '—' }} → {{ $c['score_after'] ?? '—' }}</p>
        </div>
        <div class="grid gap-4 lg:grid-cols-[1fr_200px_1fr]">
            <div class="card p-5">
                <div class="flex items-center justify-between"><div><div class="eyebrow">Kondisi awal</div><h3 class="text-[16px] font-semibold">Before joining TUMBUH</h3></div><x-source-badge :source="$c['baseline']->source" /></div>
                <div class="mt-4 space-y-3">
                    @foreach ($c['rows'] as $r)
                        <div class="module p-4"><div class="text-[12px] text-slate">{{ $r['label'] }}</div><div class="metric text-[26px]">{{ $kpi->format($r['metric'], $r['before']) }}</div></div>
                    @endforeach
                </div>
            </div>
            <div class="card flex flex-col items-center justify-center gap-3 p-5 text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand text-white"><x-icon name="trending_up" /></span>
                <div class="eyebrow-brand">Trajektori</div>
                <div class="font-display text-[18px] font-bold">{{ $c['days'] }} hari</div>
                @foreach ($c['rows'] as $r)
                    <span class="pill border border-line text-ink-2">{{ $kpi->formatDelta($r['delta']) }} {{ strtolower(explode(' ', $r['label'])[0]) }}</span>
                @endforeach
            </div>
            <div class="card border-ink p-5">
                <div class="flex items-center justify-between"><div><div class="eyebrow-brand">Hasil terkini</div><h3 class="text-[16px] font-semibold">After 30-day growth action</h3></div><x-source-badge :source="$c['latest']->source" /></div>
                <div class="mt-4 space-y-3">
                    @foreach ($c['rows'] as $r)
                        @php($d = $r['delta'])
                        <div class="module p-4">
                            <div class="text-[12px] text-slate">{{ $r['label'] }}</div>
                            <div class="flex flex-wrap items-baseline gap-2"><span class="metric text-[26px]">{{ $kpi->format($r['metric'], $r['after']) }}</span>
                                <span class="pill {{ $d['direction'] === 'up' ? 'bg-ok-tint text-ok' : ($d['direction'] === 'down' ? 'bg-brand-tint text-brand-deep' : 'bg-muted text-slate') }}"><x-icon :name="$d['direction'] === 'up' ? 'arrow_upward' : ($d['direction'] === 'down' ? 'arrow_downward' : 'remove')" size="13" />{{ $kpi->formatDelta($d) }}</span></div>
                            <div class="mt-1 text-[11px] text-slate">Absolut {{ $d['unit'] === 'rp' ? rupiah_short($d['absolute']) : num($d['absolute'], $d['unit'] === 'rate' ? 1 : 0) }}@if ($d['relative'] !== null) · relatif {{ signed($d['relative'], 1, '%') }}@endif @if ($d['pp'] !== null) · {{ signed($d['pp'], 1, 'pp') }}@endif</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-6 card p-5 md:p-6">
            <x-section-header title="What contributed to your growth?" subtitle="Kontributor tercatat di Growth Ledger setelah baseline" />
            @if ($c['contributions'])
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach ($c['contributions'] as $ct)
                        <div class="module p-4"><div class="flex items-center justify-between"><span class="font-semibold">✓ {{ $ct['label'] }}</span><x-source-badge :source="$ct['source']" /></div><p class="mt-1 text-[12px] text-slate">{{ $ct['detail'] }}</p></div>
                    @endforeach
                </div>
            @else
                <p class="text-[13px] text-slate">Belum ada kontributor tercatat.</p>
            @endif
        </div>
        <div class="mt-6 rounded-[14px] border border-line bg-canvas p-4 text-[12px] text-slate">
            <b class="text-ink">Panduan label sumber data:</b> Self-reported (input pemilik) · Integrated (Demo) (dihitung dari catatan platform, integrasi disimulasikan) · Verified (terverifikasi pihak ketiga — belum ada di prototipe) · Estimated (perkiraan sistem) · Simulated (hasil simulasi demo). Perubahan tidak otomatis berarti kausalitas; kontributor adalah aktivitas yang tercatat pada periode yang sama.
        </div>
    @endif
</x-layouts.app>
