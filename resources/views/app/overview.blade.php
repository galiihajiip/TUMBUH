<x-layouts.app title="Overview">
    @php
        $hour = now()->hour;
        $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        $dims = config('tumbuh.dimensions');
        $kpi = app(\App\Services\KpiService::class);
    @endphp

    {{-- Greeting --}}
    <div class="card mb-6 flex flex-wrap items-center justify-between gap-4 p-5 md:p-6">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-[24px] font-bold">{{ $greet }}, {{ auth()->user()->firstName() }} 👋</h1>
                <x-tier-badge :tier="$business->tier" :theme="true" />
                <span class="pill border border-line bg-white text-slate">Passport: {{ config("tumbuh.passport_levels.{$business->passport_level}") }}</span>
            </div>
            @if ($score)
                <p class="mt-1 text-[14px] text-ink-2">Fokus usaha Anda saat ini: <b class="uppercase tracking-[0.02em] text-brand-deep">{{ $recommendation?->bottleneck === 'expansion' ? 'Expansion Readiness' : $dims[$score->bottleneck]['label'] }}</b></p>
            @endif
            <p class="mt-1 flex flex-wrap items-center gap-2 text-[12px] text-slate"><x-icon name="schedule" size="14" />Data terakhir: {{ $latestKpi?->period->translatedFormat('F Y') ?? '—' }} · Growth cycle {{ $business->growth_cycle }} @if ($latestKpi)<x-source-badge :source="$latestKpi->source" />@endif</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('growth.ledger') }}" class="btn-secondary btn-sm"><x-icon name="receipt_long" size="16" />Growth Ledger</a>
            <a href="{{ route('assistant') }}" class="btn-primary btn-sm"><x-icon name="auto_awesome" size="16" />AI Assistant</a>
        </div>
    </div>

    @unless ($score)
        <x-empty-state icon="troubleshoot" title="Mulai Growth Diagnostic Anda" text="Jawab ±20 pertanyaan singkat untuk menemukan bottleneck terbesar bisnis Anda dan mendapatkan Growth Score.">
            <a href="{{ route('diagnostic.start') }}" class="btn-primary">Mulai diagnostic <x-icon name="arrow_forward" size="18" /></a>
        </x-empty-state>
    @else
        {{-- Bottleneck spotlight --}}
        @if ($recommendation)
            <div class="mb-6 rounded-[var(--radius-card)] border border-brand/30 bg-brand-soft p-5 shadow-[var(--shadow-spotlight)] md:p-6">
                <div class="flex flex-col items-start gap-4 md:flex-row">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[14px] bg-brand text-white"><x-icon :name="$recommendation->bottleneck === 'expansion' ? 'rocket_launch' : 'warning'" fill /></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2"><span class="eyebrow-brand">{{ $recommendation->bottleneck === 'expansion' ? 'Peluang terdeteksi' : 'Hambatan utama bisnis terdeteksi' }}</span><x-status-badge :status="$score->priority" /></div>
                        <h2 class="mt-1 text-[19px] font-semibold leading-7">{{ $recommendation->headline }}</h2>
                        <p class="mt-1 text-[14px] text-ink-2"><b>Analisis AI:</b> {{ $recommendation->whats_wrong }}</p>
                        <p class="mt-1 text-[14px] text-ink-2"><b>Aksi berikutnya:</b> {{ $recommendation->what_to_do }}</p>
                        <x-why :evidence="$recommendation->evidence" />
                    </div>
                    <a href="{{ route('plan.show') }}" class="btn-primary shrink-0">Lihat Aksi Rekomendasi AI <x-icon name="arrow_forward" size="18" /></a>
                </div>
            </div>
        @endif

        <div class="mb-6 grid gap-6 xl:grid-cols-[360px_1fr]">
            {{-- Growth index --}}
            <div class="card p-5 md:p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-[16px] font-semibold">Indeks Pertumbuhan</h3>
                    <x-tier-badge :tier="$business->tier" />
                </div>
                <p class="text-[12px] text-slate">Skor komprehensif kesehatan pertumbuhan UMKM</p>
                <div class="mt-4 flex items-center justify-between rounded-[14px] bg-canvas p-4">
                    <div>
                        <div class="eyebrow">Growth Score</div>
                        <div class="metric text-[44px] leading-[48px]">{{ $score->overall }}<span class="text-[16px] font-medium text-slate">/100</span></div>
                        @if ($previousScore)
                            @php($d = $score->overall - $previousScore->overall)
                            <div class="text-[12px] font-semibold {{ $d >= 0 ? 'text-ok' : 'text-brand-deep' }}"><x-icon :name="$d >= 0 ? 'trending_up' : 'trending_down'" size="14" /> {{ signed($d) }} poin (dari {{ $previousScore->overall }})</div>
                        @endif
                    </div>
                    <x-score-ring :value="$score->overall" :size="96" :stroke="9" label="" />
                </div>
                <div class="mt-4 space-y-3">
                    @foreach ($score->dimensions as $k => $v)
                        @php($isB = $k === $score->bottleneck)
                        <div>
                            <div class="mb-1 flex justify-between text-[12px] {{ $isB ? 'font-semibold text-brand-deep' : 'text-ink-2' }}">
                                <span>{{ $dims[$k]['label'] }} @if ($isB)<x-icon name="warning" size="13" fill />@endif</span><span class="tabular">{{ $v }}/100</span>
                            </div>
                            <x-progress :value="$v" :tone="$isB ? 'brand' : 'ink'" />
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('growth.score') }}" class="mt-4 flex items-center justify-between rounded-[10px] border border-line px-3 py-2 text-[12px] hover:bg-canvas">
                    <span class="text-slate">Kenapa skor saya segini?</span><span class="font-semibold text-brand-deep">Lihat breakdown →</span>
                </a>
            </div>

            {{-- KPI --}}
            <div>
                <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                    <div><h3 class="text-[16px] font-semibold">Kinerja Inti Usaha</h3><p class="text-[12px] text-slate">Baseline → saat ini → target. Sumber data ditandai di setiap angka.</p></div>
                    <span class="pill border border-line bg-white text-slate">Perbandingan: baseline & target</span>
                </div>
                @if ($kpiRows)
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($kpiRows as $r)
                            <x-kpi-card :label="$r['label']" :value="$kpi->format($r['metric'], $r['current'])" :before="$kpi->format($r['metric'], $r['before'])" :target="$r['target'] !== null ? $kpi->format($r['metric'], $r['target']) : null"
                                :delta="$r['delta']['direction'] === 'flat' ? 'Belum berubah dari baseline' : $kpi->formatDelta($r['delta']).' vs baseline'" :direction="$r['delta']['direction']" :progress="$r['progress']" :source="$r['source']"
                                :alert="($score->bottleneck === 'retention' && $r['metric'] === 'repeat_rate') || ($score->bottleneck === 'acquisition' && $r['metric'] === 'new_customers') || ($score->bottleneck === 'conversion' && $r['metric'] === 'conversion_rate')" />
                        @endforeach
                    </div>
                @else
                    <x-empty-state icon="query_stats" title="Belum ada KPI" text="KPI baseline dibuat saat Anda mengonfirmasi data diagnostic." />
                @endif
            </div>
        </div>

        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            {{-- This week's mission --}}
            <div class="card p-5 md:p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-[16px] font-semibold"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand text-white"><x-icon name="bolt" size="18" fill /></span>Misi Pekan Ini</h3>
                    @if ($mission?->due_on)<span class="pill border border-line text-slate">Batas: {{ $mission->due_on->translatedFormat('d M') }}</span>@endif
                </div>
                @if ($mission)
                    <div class="rounded-[14px] border border-line bg-canvas p-4">
                        <div class="eyebrow-brand">Minggu {{ $mission->week }} · {{ $mission->planItem?->phase }} @if ($mission->solution_key)· Solusi: {{ $mission->solution?->name ?? \App\Services\RecommendationService::NATIVE[$mission->solution_key]['name'] ?? $mission->solution_key }}@endif</div>
                        <div class="mt-1 text-[17px] font-semibold">{{ $mission->title }}</div>
                        <p class="mt-1 text-[13px] text-slate">{{ $mission->description }}</p>
                        @if ($mission->kpi_label)<div class="mt-3 inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-[12px]"><span class="text-slate">KPI:</span><b>{{ $mission->kpi_label }}</b>@if ($mission->kpi_target)<span class="text-slate">target {{ num($mission->kpi_target) }}</span>@endif</div>@endif
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-[12px] text-slate">+{{ $mission->points }} poin achievement</span>
                        <a href="{{ route('missions.show', $mission) }}" class="btn-primary btn-sm">Check-in misi <x-icon name="arrow_forward" size="16" /></a>
                    </div>
                @else
                    <x-empty-state icon="task_alt" title="Tidak ada misi aktif" text="Rencana 30 hari selesai. Tutup siklus untuk mengukur dampak dan mendapatkan rencana berikutnya." class="border-0 shadow-none" />
                @endif
            </div>

            {{-- Mission list --}}
            <div class="card p-5 md:p-6">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-[16px] font-semibold">Daftar Misi Aktif</h3>
                    <span class="text-[12px] font-semibold text-brand-deep">{{ $missions->filter->isDone()->count() }} dari {{ $missions->count() }} selesai</span>
                </div>
                <p class="mb-4 text-[12px] text-slate">Langkah taktis rencana 30 hari siklus {{ $business->growth_cycle }}</p>
                <div class="space-y-2">
                    @forelse ($missions as $m)
                        <a href="{{ route('missions.show', $m) }}" class="flex items-center gap-3 rounded-[12px] border px-3 py-2.5 transition hover:bg-canvas {{ $m->status === 'active' ? 'border-brand/40 bg-brand-soft' : 'border-line' }}">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $m->isDone() ? 'bg-ink text-white' : ($m->status === 'active' ? 'bg-brand text-white' : 'bg-muted text-slate') }}">
                                <x-icon :name="$m->isDone() ? 'check' : ($m->status === 'active' ? 'play_arrow' : 'schedule')" size="16" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[13px] font-semibold {{ $m->isDone() ? 'text-slate line-through' : '' }}">{{ $m->title }}</span>
                                <span class="block text-[11px] text-slate">Minggu {{ $m->week }} · {{ $m->planItem?->phase }}</span>
                            </span>
                            <x-status-badge :status="$m->status === 'active' ? 'running' : $m->status" :label="null" />
                        </a>
                    @empty
                        <p class="text-[13px] text-slate">Belum ada misi.</p>
                    @endforelse
                </div>
                <a href="{{ route('missions.index') }}" class="mt-4 inline-flex text-[13px] font-semibold text-brand-deep">Kelola semua misi →</a>
            </div>
        </div>

        {{-- Ecosystem --}}
        <div class="card p-5 md:p-6">
            <x-section-header title="Ekosistem Solusi Telkomsel" subtitle="Status aktivasi kapabilitas yang terhubung dengan bisnis Anda">
                <x-slot:actions><a href="{{ route('solutions.activation') }}" class="text-[13px] font-semibold text-brand-deep">Activation Center →</a></x-slot:actions>
            </x-section-header>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($activations as $a)
                    <a href="{{ route('solutions.show', $a->solution) }}" class="module p-4 hover:border-line-strong">
                        <div class="flex items-center justify-between"><x-icon :name="$a->solution->icon" class="text-brand" /><x-status-badge :status="$a->status" /></div>
                        <div class="mt-3 text-[14px] font-semibold">{{ $a->solution->name }}</div>
                        <div class="text-[12px] text-slate">{{ $a->solution->role }}</div>
                        <div class="mt-2 text-[11px] text-slate">{{ \App\Services\SolutionService::CONNECTION_LABELS[$a->connection] ?? $a->connection }}</div>
                    </a>
                @empty
                    <p class="text-[13px] text-slate">Belum ada solusi aktif. <a class="link" href="{{ route('solutions.index') }}">Lihat rekomendasi</a>.</p>
                @endforelse
            </div>
            <x-sim-note class="mt-4" />
        </div>
    @endunless
</x-layouts.app>
