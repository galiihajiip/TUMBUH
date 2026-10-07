<x-layouts.app title="Solutions">
    <x-page-header eyebrow="Telkomsel solution orchestration" title="Based on your problem, these are the solutions that can help." subtitle="Bukan katalog semua produk — urutan mengikuti bottleneck Anda. Solusi yang sudah Anda gunakan ditandai sebagai optimasi." />
    @include('partials.nav-solutions')
    <div class="mb-5 flex flex-wrap items-center gap-2 text-[13px]">
        <span class="text-slate">Fokus:</span>
        @foreach (array_merge(array_keys(config('tumbuh.dimensions')), ['expansion']) as $f)
            <a href="{{ route('solutions.index', ['focus' => $f]) }}" class="rounded-full border px-3 py-1 {{ $focus === $f ? 'border-ink bg-ink text-white' : 'border-line hover:border-ink' }}">{{ $f === 'expansion' ? 'Expansion' : config("tumbuh.dimensions.{$f}.label") }}</a>
        @endforeach
    </div>
    @if ($focus)
        <div class="mb-3 text-[13px] text-slate">Your bottleneck: <b class="text-brand-deep">{{ $focus === 'expansion' ? 'Expansion Readiness' : config("tumbuh.dimensions.{$focus}.label") }}</b></div>
        <div class="mb-8 grid gap-4 md:grid-cols-2">
            @foreach ($recommended as $i => $key)
                @php($s = $catalog[$key] ?? null)
                @php($n = $native[$key] ?? null)
                @php($a = $activations[$key] ?? null)
                <div class="card flex flex-col p-5 {{ $i === 0 ? 'border-brand shadow-[var(--shadow-spotlight)]' : '' }}">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[12px] bg-brand-tint text-brand"><x-icon :name="$s->icon ?? $n['icon']" /></span>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2"><span class="text-[12px] font-bold text-slate">{{ $i + 1 }}.</span><h3 class="text-[17px] font-semibold">{{ $s->name ?? $n['name'] }}</h3>
                                @if ($alreadyUses($key))<span class="pill bg-muted text-slate">Sudah digunakan · optimasi</span>@endif
                                @if ($a)<x-status-badge :status="$a->status" />@endif
                            </div>
                            <div class="text-[12px] text-slate">{{ $s->role ?? $n['role'] }}</div>
                            <p class="mt-2 text-[14px]">{{ $s->tagline ?? ($key === 'growth_finance' ? 'Ubah aktivitas bisnis menjadi laporan keuangan terukur.' : 'Pastikan inventaris membantu, bukan menghambat pertumbuhan.') }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-4">
                        <span class="pill border border-line text-slate">{{ $s ? $s->pricingText() : 'Kapabilitas native TUMBUH' }}</span>
                        <a href="{{ $s ? route('solutions.show', $s) : route($n['route']) }}" class="btn-secondary btn-sm">{{ $s ? 'Lihat detail' : 'Buka' }} <x-icon name="arrow_forward" size="16" /></a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <x-section-header title="Semua kapabilitas yang diorkestrasi" subtitle="Learn · Enable · Market · Operate · Scale" />
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($catalog as $s)
            <a href="{{ route('solutions.show', $s) }}" class="card p-4 hover:shadow-[var(--shadow-hover)]">
                <div class="flex items-center justify-between"><x-icon :name="$s->icon" class="text-brand" /><x-status-badge :status="$activations[$s->key]->status" /></div>
                <div class="mt-3 font-semibold">{{ $s->name }}</div>
                <div class="text-[12px] text-slate">{{ $s->role }}</div>
                <div class="mt-2 text-[11px] text-slate">Aktivasi mulai: <b>{{ ucfirst($s->min_tier) }}</b></div>
            </a>
        @endforeach
    </div>
    <x-sim-note class="mt-5" text="Akses tier ke suatu capability tidak berarti penggunaan produknya gratis. Biaya & persyaratan mengikuti ketentuan setiap layanan; seluruh aktivasi di prototipe ini disimulasikan." />
</x-layouts.app>
