<x-layouts.app title="Weekly Growth Mission">
    <x-page-header eyebrow="Misi pertumbuhan mingguan" title="Your Weekly Growth Mission" subtitle="Misi yang actionable — bukan sekadar login. Setiap check-in mencatat KPI, kendala, dan bukti." />
    @include('partials.nav-missions')
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Misi selesai" :value="$done.' / '.$total" icon="task_alt"><x-progress class="mt-3" :value="$total ? $done / $total * 100 : 0" /></x-stat-card>
        <x-stat-card label="Poin achievement" :value="num($points)" icon="stars" sub="Masuk 25% Mission Completion di leaderboard" />
        <x-stat-card label="Rencana aktif" :value="$plan?->title ?? '—'" icon="event_note" :sub="$plan ? 'Siklus '.$plan->cycle : null" />
    </div>
    @if ($current)
        <div class="card mb-6 overflow-hidden border-brand shadow-[var(--shadow-spotlight)]">
            <div class="grid md:grid-cols-[1fr_auto]">
                <div class="p-6">
                    <div class="eyebrow-brand">Minggu {{ $current->week }} · {{ $current->planItem?->phase }} · misi aktif</div>
                    <h2 class="mt-1 text-[22px] font-bold">{{ $current->title }}</h2>
                    <p class="mt-1 text-[14px] text-slate">{{ $current->description }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-[12px]">
                        @if ($current->kpi_label)<span class="pill border border-line">KPI: {{ $current->kpi_label }}@if ($current->kpi_target) · target {{ num($current->kpi_target) }}@endif</span>@endif
                        @if ($current->due_on)<span class="pill border border-line">Batas {{ $current->due_on->translatedFormat('d M Y') }}</span>@endif
                        <span class="pill bg-gold-tint text-gold">+{{ $current->points }} poin</span>
                    </div>
                </div>
                <div class="flex items-center bg-brand-soft p-6"><a href="{{ route('missions.show', $current) }}" class="btn-primary">Submit check-in <x-icon name="arrow_forward" size="18" /></a></div>
            </div>
        </div>
    @endif
    @foreach ($missions as $planId => $group)
        @php($p = \App\Models\ActionPlan::find($planId))
        <div class="card mb-6">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line px-5 py-3.5">
                <div class="font-semibold">{{ $p?->title ?? 'Misi' }} <span class="text-[12px] font-normal text-slate">· siklus {{ $p?->cycle }}</span></div>
                @if ($p)<x-status-badge :status="$p->status === 'active' ? 'running' : 'completed'" :label="null" />@endif
            </div>
            <div class="divide-y divide-line">
                @foreach ($group as $m)
                    <a href="{{ route('missions.show', $m) }}" class="flex items-center gap-4 px-5 py-3.5 hover:bg-canvas">
                        <span class="w-16 shrink-0 text-[12px] font-semibold text-slate">Week {{ $m->week }}</span>
                        <span class="min-w-0 flex-1"><span class="block truncate text-[14px] font-semibold">{{ $m->title }}</span>
                            @if ($s = $m->submissions->first())<span class="block truncate text-[12px] text-slate">Check-in {{ $s->created_at->translatedFormat('d M') }} · {{ ['yes' => 'Selesai', 'partial' => 'Sebagian', 'no' => 'Belum'][$s->completion] }}@if ($s->kpi_value !== null) · KPI {{ num($s->kpi_value) }}@endif @if ($s->problem) · Kendala: {{ $s->problem }}@endif</span>@endif
                        </span>
                        <x-status-badge :status="$m->status === 'active' ? 'running' : $m->status" :label="null" />
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</x-layouts.app>
