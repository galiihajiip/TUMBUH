<x-layouts.app title="Scale">
    <x-page-header eyebrow="Scale" title="Where should you expand next?" subtitle="Saat bisnis mencapai Scaling, pertanyaannya bukan lagi pelanggan pertama — tetapi pasar dan lokasi berikutnya." />
    @include('partials.nav-scale')
    @php($levels = config('tumbuh.passport_levels'))
    @php($idx = array_search($business->passport_level, array_keys($levels)))
    <div class="card mb-6 p-6">
        <div class="flex items-center justify-between"><h3 class="text-[16px] font-semibold">Growth Passport</h3><a href="{{ route('scale.passport') }}" class="text-[13px] font-semibold text-brand-deep">Kriteria →</a></div>
        <div class="mt-5 grid grid-cols-3 gap-2 md:grid-cols-6">
            @foreach ($levels as $k => $l)
                <div class="rounded-[12px] border p-3 text-center {{ $loop->index < $idx ? 'border-ink bg-ink text-white' : ($loop->index === $idx ? 'border-brand bg-brand text-white shadow-[var(--shadow-spotlight)]' : 'border-line text-slate') }}">
                    <x-icon :name="$loop->index <= $idx ? 'check_circle' : 'radio_button_unchecked'" size="18" />
                    <div class="mt-1 text-[12px] font-bold uppercase tracking-[0.04em]">{{ $l }}</div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card p-6">
            <div class="eyebrow">Expansion Readiness</div>
            <div class="metric mt-1 text-[44px]">{{ $readiness['score'] }}<span class="text-[16px] text-slate">/100</span></div>
            <div class="text-[13px] font-semibold {{ $readiness['score'] >= 80 ? 'text-ok' : 'text-[#b45309]' }}">{{ $readiness['verdict'] }}</div>
            <a href="{{ $tiers->allows($business, 'expansion.basic') ? route('scale.readiness') : route('rewards.tiers') }}" class="btn-secondary btn-sm mt-4">{{ $tiers->allows($business, 'expansion.basic') ? 'Lihat detail' : 'Silver diperlukan' }}</a>
        </div>
        <div class="card p-6">
            <div class="eyebrow">MSIGHT · Market intelligence</div>
            <p class="mt-2 text-[14px]">Data → insight → keputusan: di mana pelanggan potensial Anda?</p>
            <a href="{{ $tiers->allows($business, 'msight') ? route('scale.msight') : route('rewards.tiers') }}" class="btn-secondary btn-sm mt-4">{{ $tiers->allows($business, 'msight') ? 'Buka MSIGHT' : 'Gold diperlukan' }}</a>
        </div>
        <div class="card p-6 {{ $dce['eligible'] ? 'border-brand shadow-[var(--shadow-spotlight)]' : '' }}">
            <div class="eyebrow">DCE eligibility</div>
            <div class="metric mt-1 text-[44px]">{{ $dce['score'] }}<span class="text-[16px] text-slate">/100</span></div>
            <div class="text-[13px] font-semibold">{{ $dce['eligible'] ? 'You may be ready for DCE.' : 'Belum memenuhi indikator (≥ 75)' }}</div>
            <a href="{{ route('scale.dce') }}" class="btn-primary btn-sm mt-4">Check DCE eligibility</a>
        </div>
    </div>
    <x-sim-note class="mt-5" text="MSIGHT dan SiteSense di prototipe ini menggunakan data simulasi (Demo Insight). Bukan hasil aktual layanan tersebut." />
</x-layouts.app>
