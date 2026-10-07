<x-layouts.app title="MSIGHT">
    <x-page-header eyebrow="MSIGHT · Advanced business intelligence (Demo Insight)" title="Market Insight" subtitle="Business question → data requirement → insight → decision. Bukan sekadar 'berikut data pasar'." />
    @include('partials.nav-scale')
    <form method="POST" action="{{ route('scale.msight.run') }}" class="card mb-6 p-6">@csrf
        <div class="label">Pertanyaan bisnis Anda</div>
        <div class="grid gap-2 md:grid-cols-4">
            @foreach ($questions as $k => $q)<button name="question" value="{{ $k }}" class="module p-4 text-left text-[14px] font-semibold hover:border-ink">{{ $q }}</button>@endforeach
        </div>
    </form>
    @forelse ($history as $h)
        @php($r = $h->result)
        <div class="card mb-4 overflow-hidden {{ $loop->first ? 'border-brand' : '' }}">
            <div class="grid md:grid-cols-[1.2fr_1fr]">
                <div class="p-6">
                    <div class="flex items-center gap-2"><span class="pill bg-[#f5f3ff] text-[#6d28d9]">Demo Insight · Simulated Data</span><span class="text-[12px] text-slate">{{ $h->created_at->diffForHumans() }}</span></div>
                    <div class="mt-2 text-[13px] text-slate">“{{ $r['question'] }}”</div>
                    <div class="mt-3 eyebrow">Insight</div><p class="text-[17px] font-semibold">{{ $r['insight'] }}</p>
                    <div class="mt-3 rounded-[12px] bg-ink p-4 text-white"><div class="text-[11px] font-semibold uppercase tracking-[0.08em] text-white/60">Decision · TUMBUH Growth Loop</div><div class="mt-1 text-[14px] font-semibold">{{ $r['decision'] }}</div></div>
                    @if ($loop->first)<a href="{{ route('scale.sitesense') }}" class="btn-secondary btn-sm mt-4">Validasi lokasi di SiteSense →</a>@endif
                </div>
                <div class="border-t border-line bg-canvas p-6 md:border-l md:border-t-0">
                    <div class="eyebrow mb-2">Area profile (simulasi)</div>
                    @foreach ($r['areas'] as $a)
                        <div class="mb-3"><div class="flex justify-between text-[13px]"><b>{{ $a['area'] }}</b><span class="text-slate">{{ signed($a['growth'], 1, '%') }} YoY</span></div><div class="text-[11px] text-slate">{{ $a['segment'] }} · puncak {{ $a['peak'] }}</div><x-progress class="mt-1" :value="$a['density']" /></div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <x-empty-state icon="query_stats" title="Ajukan pertanyaan bisnis" text="Pilih salah satu pertanyaan di atas untuk menghasilkan Demo Insight." />
    @endforelse
</x-layouts.app>
