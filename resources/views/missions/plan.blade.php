<x-layouts.app title="30-Day Action Plan">
    @php($native = \App\Services\RecommendationService::NATIVE)
    <x-page-header eyebrow="Personalized action plan" :title="$plan ? 'Your 30-Day Growth Plan' : 'Rencana aksi belum tersedia'" :subtitle="$plan ? $plan->title.' — '.$plan->goal : 'Rencana dibuat otomatis setelah Growth Diagnostic.'" />
    @include('partials.nav-missions')
    @if (! $plan)
        <x-empty-state icon="event_note" title="Belum ada rencana" text="Selesaikan Growth Diagnostic untuk menghasilkan rencana 30 hari."><a href="{{ route('diagnostic.start') }}" class="btn-primary">Mulai diagnostic</a></x-empty-state>
    @else
        <div class="mb-6 grid gap-4 md:grid-cols-4">
            <x-stat-card label="Fokus bottleneck" :value="$plan->bottleneck === 'expansion' ? 'Expansion' : config('tumbuh.dimensions.'.$plan->bottleneck.'.label')" icon="target" tone="brand" />
            <x-stat-card label="KPI utama" :value="\App\Services\KpiService::METRICS[$plan->kpi_metric]['label'] ?? 'Growth Score'" icon="query_stats" />
            <x-stat-card label="Baseline → target" :value="$plan->kpi_baseline !== null ? app(\App\Services\KpiService::class)->format($plan->kpi_metric, $plan->kpi_baseline).' → '.app(\App\Services\KpiService::class)->format($plan->kpi_metric, $plan->kpi_target) : '—'" icon="flag" />
            <x-stat-card label="Siklus" :value="'#'.$plan->cycle.' · mulai '.$plan->starts_on->translatedFormat('d M')" icon="event_repeat" :sub="ucfirst($plan->status)" />
        </div>
        @unless ($personalized)
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-[14px] border border-line bg-canvas px-4 py-3 text-[13px]"><span><x-icon name="info" size="16" /> Bronze: rencana dasar. Target KPI personal & aktivasi solusi langsung tersedia di Silver.</span><a href="{{ route('rewards.tiers') }}" class="btn-secondary btn-sm">Lihat Silver</a></div>
        @endunless
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($plan->items as $item)
                @php($s = $item->solution_key ? (\App\Models\TelkomselSolution::where('key', $item->solution_key)->first()) : null)
                @php($n = $native[$item->solution_key] ?? null)
                <div class="card flex flex-col p-5 {{ $item->status === 'active' ? 'border-brand shadow-[var(--shadow-spotlight)]' : '' }}">
                    <div class="flex items-center justify-between"><span class="eyebrow-brand">Week {{ $item->week }} — {{ $item->phase }}</span><x-status-badge :status="$item->status === 'done' ? 'completed' : ($item->status === 'active' ? 'running' : 'upcoming')" /></div>
                    <h3 class="mt-2 text-[17px] font-semibold uppercase leading-6">{{ $item->title }}</h3>
                    <dl class="mt-3 space-y-2 text-[13px]">
                        <div><dt class="text-[11px] text-slate">Goal</dt><dd>{{ $item->goal }}</dd></div>
                        <div><dt class="text-[11px] text-slate">Action</dt><dd><ul class="list-disc pl-4">@foreach ($item->actions as $a)<li>{{ $a }}</li>@endforeach</ul></dd></div>
                        <div><dt class="text-[11px] text-slate">Solution</dt><dd class="font-semibold">{{ $s?->name ?? $n['name'] ?? 'Self-execution' }}</dd></div>
                        <div><dt class="text-[11px] text-slate">Target</dt><dd class="font-semibold">{{ $personalized ? $item->kpi_target : 'Tersedia di Silver' }}</dd></div>
                    </dl>
                    <div class="mt-auto pt-4">
                        @if ($item->mission)
                            <a href="{{ route('missions.show', $item->mission) }}" class="{{ $item->status === 'active' ? 'btn-primary' : 'btn-secondary' }} btn-sm w-full">{{ $item->status === 'done' ? 'Lihat check-in' : ($item->status === 'active' ? 'Kerjakan misi' : 'Lihat misi') }}</a>
                        @endif
                        @if ($s && $item->status === 'active')
                            <a href="{{ route('solutions.show', $s) }}" class="btn-ghost btn-sm mt-2 w-full">Activate {{ $s->name }}</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @if ($plan->recommendation)
            <x-ai-insight class="mt-6" title="Mengapa rencana ini">
                <p class="text-[14px]">{{ $plan->recommendation->whats_wrong }} {{ $plan->recommendation->why_it_matters }}</p>
                <x-why :evidence="$plan->recommendation->evidence" />
            </x-ai-insight>
        @endif
    @endif
</x-layouts.app>
