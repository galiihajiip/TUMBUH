<x-layouts.app :title="'Misi minggu '.$mission->week">
    <x-page-header :eyebrow="'Week '.$mission->week.' check-in · '.($mission->planItem?->phase ?? '')" :title="$mission->title" :subtitle="$mission->description">
        <x-slot:actions><x-status-badge :status="$mission->status === 'active' ? 'running' : $mission->status" :label="null" /></x-slot:actions>
    </x-page-header>
    <div class="grid gap-6 lg:grid-cols-[1.3fr_1fr]">
        <div>
            @if (in_array($mission->status, ['active']))
                <form method="POST" action="{{ route('missions.check-in', $mission) }}" enctype="multipart/form-data" class="card p-6" x-data="{ c: '{{ old('completion', 'yes') }}' }">@csrf
                    <h2 class="text-[18px] font-semibold">Week {{ $mission->week }} check-in</h2>
                    <div class="mt-4"><span class="label">Did you complete your mission?</span>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['yes' => 'Yes', 'partial' => 'Partially', 'no' => 'No'] as $v => $l)
                                <label class="module flex cursor-pointer items-center gap-2 p-3 text-[14px]" :class="c === '{{ $v }}' && 'border-ink ring-[3px] ring-brand-tint'"><input type="radio" name="completion" value="{{ $v }}" x-model="c" class="accent-[#ec1c24]">{{ $l }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-field name="kpi_value" type="number" step="any" :label="$mission->kpi_label ?? 'Nilai KPI'" :help="$mission->kpi_target ? 'Target: '.num($mission->kpi_target) : null" />
                        <div><label class="label" for="evidence">Bukti (opsional)</label><input id="evidence" type="file" name="evidence" accept="image/*,application/pdf" class="input pt-2 text-[13px]"><p class="mt-1 text-[12px] text-slate">JPG/PNG/PDF maks 4MB, disimpan privat.</p></div>
                    </div>
                    <div class="mt-4"><label class="label" for="notes">What happened?</label><textarea id="notes" name="notes" rows="3" class="input">{{ old('notes') }}</textarea></div>
                    <div class="mt-4"><label class="label" for="problem">What is your biggest challenge?</label><textarea id="problem" name="problem" rows="2" class="input" placeholder="Kendala yang dihadapi (opsional)">{{ old('problem') }}</textarea></div>
                    <button class="btn-primary mt-6 w-full">Submit check-in</button>
                </form>
            @elseif ($mission->status === 'upcoming')
                <x-empty-state icon="schedule" title="Misi ini belum dimulai" text="Selesaikan misi minggu sebelumnya untuk membuka misi ini." />
            @endif
            @if ($mission->submissions->isNotEmpty())
                <div class="card mt-6 p-5">
                    <h3 class="text-[16px] font-semibold">Riwayat check-in</h3>
                    @foreach ($mission->submissions as $s)
                        <div class="mt-3 rounded-[12px] border border-line p-3 text-[13px]">
                            <div class="flex justify-between"><b>{{ ['yes' => 'Selesai', 'partial' => 'Sebagian', 'no' => 'Belum selesai'][$s->completion] }}</b><span class="text-slate">{{ $s->created_at->translatedFormat('d M Y H:i') }}</span></div>
                            @if ($s->kpi_value !== null)<div>KPI: <b>{{ num($s->kpi_value, 1) }}</b></div>@endif
                            @if ($s->notes)<div class="text-slate">{{ $s->notes }}</div>@endif
                            @if ($s->problem)<div class="text-brand-deep">Kendala: {{ $s->problem }}</div>@endif
                            @if ($s->evidence_path)<div class="text-slate"><x-icon name="attach_file" size="14" />Bukti terlampir</div>@endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="space-y-6">
            @if ($latestKpi && $mission->kpi_metric && isset($latestKpi->{$mission->kpi_metric}))
                <div class="card p-5">
                    <div class="eyebrow">Konteks KPI</div>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <div><div class="text-[12px] text-slate">Saat ini</div><div class="metric text-[26px]">{{ app(\App\Services\KpiService::class)->format($mission->kpi_metric, $latestKpi->{$mission->kpi_metric}) }}</div></div>
                        <div><div class="text-[12px] text-slate">Target</div><div class="metric text-[26px]">{{ $mission->kpi_target ? num($mission->kpi_target) : '—' }}</div></div>
                    </div>
                </div>
            @endif
            @if ($mission->planItem)
                <div class="card p-5">
                    <div class="eyebrow-brand">Bagian dari rencana</div>
                    <div class="mt-1 font-semibold">{{ $mission->planItem->plan->title }}</div>
                    <ul class="mt-2 list-disc pl-4 text-[13px] text-slate">@foreach ($mission->planItem->actions as $a)<li>{{ $a }}</li>@endforeach</ul>
                    @if ($mission->solution)<a href="{{ route('solutions.show', $mission->solution) }}" class="btn-secondary btn-sm mt-4">Buka {{ $mission->solution->name }}</a>@endif
                </div>
            @endif
            <a href="{{ route('missions.index') }}" class="btn-ghost"><x-icon name="arrow_back" size="18" />Semua misi</a>
        </div>
    </div>
</x-layouts.app>
