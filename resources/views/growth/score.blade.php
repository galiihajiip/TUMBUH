<x-layouts.app title="Growth Score">
    @php($dims = config('tumbuh.dimensions'))
    <x-page-header eyebrow="Hasil diagnostik UMKM" title="Your Business Growth Score" :subtitle="'Berdasarkan jawaban diagnostic, baseline KPI terkonfirmasi, dan aktivitas di platform. Diperbarui '.$score->calculated_at->translatedFormat('d M Y, H:i').'.'" />
    @include('partials.nav-growth')

    <div class="grid gap-6 lg:grid-cols-[380px_1fr]">
        <div class="space-y-6">
            <div class="card p-6 text-center">
                <x-score-ring :value="$score->overall" :size="180" :stroke="14" />
                @if ($previous)
                    @php($d = $score->overall - $previous->overall)
                    <div class="mt-3 text-[14px] font-semibold {{ $d >= 0 ? 'text-ok' : 'text-brand-deep' }}">{{ $previous->overall }} → {{ $score->overall }} ({{ signed($d) }})</div>
                @endif
                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-info-tint px-3 py-1 text-[12px] font-semibold text-info"><x-icon name="verified" size="14" />{{ $score->overall >= 80 ? 'Siap Ekspansi (Scaling Ready)' : ($score->overall >= 60 ? 'Potensi Berkembang (Solid Foundation)' : 'Fondasi Awal (Build)') }}</div>
            </div>
            <div class="card p-5">
                <div class="flex items-center justify-between"><span class="pill bg-ok-tint text-ok"><x-icon name="verified" size="13" />Area Paling Kuat</span></div>
                <div class="mt-2 text-[17px] font-semibold">{{ $dims[$score->strongest]['label'] }} · {{ $score->scoreFor($score->strongest) }}/100</div>
                <p class="text-[12px] text-slate">Pertahankan — ini fondasi untuk aksi berikutnya.</p>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Bottleneck --}}
            <div class="rounded-[var(--radius-card)] border border-brand bg-brand-soft p-5 shadow-[var(--shadow-spotlight)] md:p-6">
                <div class="flex flex-wrap items-center gap-2"><span class="pill bg-brand text-white"><x-icon name="warning" size="13" fill />Hambatan utama</span><x-status-badge :status="$score->priority" /><span class="pill border border-line bg-white text-slate">Secondary: {{ $dims[$score->secondary]['label'] ?? '—' }}</span></div>
                <h2 class="mt-2 text-[22px] font-bold">Your biggest growth bottleneck is {{ $dims[$score->bottleneck]['label'] }}.</h2>
                <p class="text-[14px] text-ink-2">Skor {{ $dims[$score->bottleneck]['id'] }}: <b>{{ $score->scoreFor($score->bottleneck) }}/100</b> — terendah dari 8 dimensi.</p>
                @if ($score->bottleneck === 'retention' && $latestKpi)
                    <div class="mt-4 grid grid-cols-3 gap-2 rounded-[12px] bg-white p-3 text-center text-[12px]">
                        <div><div class="text-slate">Repeat customer</div><div class="font-display text-[20px] font-bold text-brand-deep">{{ pct($latestKpi->repeat_rate, 0) }}</div></div>
                        <div><div class="text-slate">Gap</div><div class="font-display text-[20px] font-bold">{{ signed($latestKpi->repeat_rate - $repeatTarget, 0, 'pp') }}</div></div>
                        <div><div class="text-slate">Target benchmark</div><div class="font-display text-[20px] font-bold">{{ pct($repeatTarget, 0) }}</div></div>
                    </div>
                @endif
                @if ($recommendation)<x-why :evidence="$recommendation->evidence" />@endif
            </div>

            {{-- Breakdown --}}
            <div class="card p-5 md:p-6">
                <x-section-header title="Rincian 8 pilar pertumbuhan" subtitle="Skala 0–100 · bobot eksplisit · skor dapat direproduksi" />
                <div class="space-y-4">
                    @foreach ($dims as $k => $d)
                        @php($v = $score->scoreFor($k))
                        @php($chg = $score->changes[$k] ?? null)
                        <div class="{{ $k === $score->bottleneck ? 'rounded-[12px] bg-brand-soft p-3' : '' }}">
                            <div class="mb-1 flex flex-wrap items-center justify-between gap-2 text-[13px]">
                                <span class="font-semibold">{{ $loop->iteration }}. {{ $d['label'] }} <span class="font-normal text-slate">· {{ $d['id'] }} · bobot {{ (int) ($d['weight'] * 100) }}%</span> @if ($k === $score->bottleneck)<span class="pill bg-brand text-white">Bottleneck</span>@endif</span>
                                <span class="flex items-center gap-2">@if ($chg && $chg['delta'])<span class="text-[12px] font-semibold {{ $chg['delta'] > 0 ? 'text-ok' : 'text-brand-deep' }}">{{ signed($chg['delta']) }}</span>@endif<b class="font-display text-[16px] tabular">{{ $v }}</b></span>
                            </div>
                            <x-progress :value="$v" :tone="$k === $score->bottleneck ? 'brand' : ($v >= 70 ? 'ink' : 'warn')" height="h-2" />
                            @if ($chg && $chg['reasons'])<div class="mt-1 text-[11px] text-slate">{{ implode(' · ', $chg['reasons']) }}</div>@endif
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 rounded-[12px] bg-canvas p-3 text-[12px] text-slate"><b class="text-ink">Cara skor dihitung:</b> rata-rata berbobot jawaban per dimensi (retensi & konversi dipadukan dengan baseline KPI), lalu disesuaikan dengan perubahan KPI terukur dan aktivitas — setiap penyesuaian dibatasi dan dicatat alasannya. Overall = Σ skor dimensi × bobot.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('assistant') }}" class="btn-primary">Lihat rekomendasi AI <x-icon name="arrow_forward" size="18" /></a>
                <a href="{{ route('plan.show') }}" class="btn-secondary">Rencana aksi 30 hari</a>
            </div>
        </div>
    </div>
</x-layouts.app>
