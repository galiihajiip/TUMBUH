<x-layouts.app title="AI Performance Review">
    <x-page-header eyebrow="AI Performance Review" title="Apakah aksi Anda berhasil?" subtitle="Insight deterministik dari metrik tersimpan: apa yang berhasil, apa yang belum, dan aksi berikutnya." />
    @include('partials.nav-growth')
    @php($r = $stored?->review ?? $live)
    @if (! $r)
        <x-empty-state icon="rate_review" title="Belum ada data untuk direview" text="Review membutuhkan minimal satu bulan KPI di Growth Ledger." />
    @else
        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            <x-ai-insight title="AI Review">
                <h2 class="text-[20px] font-semibold leading-7">{{ $r['summary'] }}</h2>
                <p class="mt-1 text-[12px] text-slate">Periode {{ \Illuminate\Support\Carbon::parse($r['period'])->translatedFormat('F Y') }}@if ($r['compared_to']) dibanding {{ \Illuminate\Support\Carbon::parse($r['compared_to'])->translatedFormat('F Y') }}@endif</p>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-[12px] border border-[#a7f3d0] bg-ok-tint p-4">
                        <div class="flex items-center gap-1.5 text-[13px] font-semibold text-[#065f46]"><x-icon name="thumb_up" size="18" />What worked?</div>
                        <ul class="mt-2 space-y-1.5 text-[13px] text-[#065f46]">@foreach ($r['worked'] as $w)<li>• {{ $w }}</li>@endforeach</ul>
                    </div>
                    <div class="rounded-[12px] border border-brand/30 bg-brand-soft p-4">
                        <div class="flex items-center gap-1.5 text-[13px] font-semibold text-brand-deep"><x-icon name="thumb_down" size="18" />What didn't?</div>
                        <ul class="mt-2 space-y-1.5 text-[13px] text-brand-press">@foreach ($full ? $r['didnt'] : array_slice($r['didnt'], 0, 1) as $w)<li>• {{ $w }}</li>@endforeach</ul>
                    </div>
                </div>
                <div class="mt-4 rounded-[12px] bg-ink p-4 text-white">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.08em] text-white/60">Next action</div>
                    <div class="mt-1 text-[15px] font-semibold">{{ $r['next'] }}</div>
                </div>
                @unless ($full)<p class="mt-3 text-[12px] text-slate"><x-icon name="lock" size="14" /> Review lengkap (semua sinyal) tersedia di Silver.</p>@endunless
                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-[12px] text-slate">{{ $stored ? 'Review tersimpan '.$stored->created_at->diffForHumans() : 'Pratinjau langsung — belum disimpan' }}</span>
                        <a href="{{ route('plan.show') }}" class="btn-primary btn-sm">View next action <x-icon name="arrow_forward" size="16" /></a>
                    </div>
                </x-slot:footer>
            </x-ai-insight>

            <div class="space-y-6">
                <div class="card p-5">
                    <h3 class="text-[16px] font-semibold">Growth Score update</h3>
                    @if ($score)
                        <div class="mt-3 flex items-center gap-4">
                            <div class="text-center"><div class="text-[11px] text-slate">Before</div><div class="metric text-[30px] text-slate-2">{{ $previous?->overall ?? '—' }}</div></div>
                            <x-icon name="arrow_forward" class="text-slate" />
                            <div class="text-center"><div class="text-[11px] text-slate">After</div><div class="metric text-[30px]">{{ $score->overall }}</div></div>
                        </div>
                        <div class="mt-4 space-y-1.5 text-[13px]">
                            @foreach (collect($score->changes ?? [])->filter(fn ($c) => $c['delta'] != 0)->sortByDesc(fn ($c) => $c['delta']) as $k => $c)
                                <div class="flex justify-between"><span>{{ config("tumbuh.dimensions.{$k}.label") }}</span><b class="{{ $c['delta'] > 0 ? 'text-ok' : 'text-brand-deep' }}">{{ signed($c['delta']) }}</b></div>
                            @endforeach
                            @if (collect($score->changes ?? [])->every(fn ($c) => $c['delta'] == 0))<p class="text-slate">Tidak ada perubahan dimensi pada snapshot terakhir.</p>@endif
                        </div>
                        <p class="mt-3 text-[12px] text-slate">{{ $score->explanation }}</p>
                    @endif
                </div>
                @if ($recommendation)
                    <div class="card p-5">
                        <div class="eyebrow-brand">Bottleneck berikutnya</div>
                        <div class="mt-1 text-[16px] font-semibold">{{ $recommendation->headline }}</div>
                        <p class="mt-1 text-[13px] text-slate">{{ $recommendation->success_metric }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-layouts.app>
