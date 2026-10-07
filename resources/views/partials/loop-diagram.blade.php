{{-- Capability orchestration: how TUMBUH closes the loop (master brief §34) --}}
@php
    $node = fn ($t, $s, $icon, $tone = 'white') => compact('t', 's', 'icon', 'tone');
    $layers = [
        ['LEARN', 'Kuncie', 'school', ['kuncie']],
        ['MARKET', 'MyAds · DigiAds · RCS', 'campaign', ['myads', 'digiads', 'rcs']],
        ['OPERATE', 'DigiPOS · Soundbox · Growth Finance · Growth Inventory', 'storefront', ['digipos', 'soundbox']],
    ];
@endphp
<div class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <div class="card p-5 md:p-8">
        <div class="mx-auto flex max-w-[640px] flex-col items-center gap-2 text-center">
            @foreach ([['CUSTOMER', 'UMKM & pelanggannya', 'person'], ['GROWTH DIAGNOSTIC', '8 dimensi · Growth Score · bottleneck', 'troubleshoot'], ['AI ENGINE', 'Next-best-action berbasis data bisnis', 'auto_awesome']] as [$t, $s, $icon])
                <div class="w-full rounded-[14px] border {{ $t === 'AI ENGINE' ? 'border-brand bg-brand-soft shadow-[var(--shadow-spotlight)]' : 'border-line bg-white' }} px-4 py-3">
                    <div class="flex items-center justify-center gap-2 font-display text-[14px] font-bold"><x-icon :name="$icon" size="18" class="text-brand" />{{ $t }}</div>
                    <div class="text-[12px] text-slate">{{ $s }}</div>
                </div>
                <x-icon name="south" class="text-slate-2" />
            @endforeach
            <div class="grid w-full gap-2 sm:grid-cols-3">
                @foreach ($layers as [$t, $s, $icon])
                    <div class="rounded-[14px] border border-line bg-canvas px-3 py-3">
                        <div class="flex items-center justify-center gap-1.5 font-display text-[13px] font-bold"><x-icon :name="$icon" size="16" class="text-brand" />{{ $t }}</div>
                        <div class="mt-1 text-[12px] text-slate">{{ $s }}</div>
                    </div>
                @endforeach
            </div>
            <x-icon name="south" class="text-slate-2" />
            @foreach ([['MEASUREMENT', 'KPI · Growth Ledger · Before vs After', 'monitoring'], ['AI REVIEW', 'Apa yang berhasil, apa yang belum', 'rate_review'], ['NEXT BEST ACTION', 'Rekomendasi & misi berikutnya berubah', 'alt_route'], ['GROW', 'Growth Passport · Leaderboard · Reward', 'trending_up'], ['MSIGHT · SiteSense · DCE', 'Intelijen pasar, lokasi & akselerasi', 'rocket_launch'], ['SCALE', 'Pasar baru, siklus baru', 'public']] as [$t, $s, $icon])
                <div class="w-full rounded-[14px] border border-line bg-white px-4 py-3">
                    <div class="flex items-center justify-center gap-2 font-display text-[14px] font-bold"><x-icon :name="$icon" size="18" class="text-brand" />{{ $t }}</div>
                    <div class="text-[12px] text-slate">{{ $s }}</div>
                </div>
                @if (! $loop->last)<x-icon name="south" class="text-slate-2" />@endif
            @endforeach
            <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-brand px-4 py-2 text-[13px] font-semibold text-white"><x-icon name="all_inclusive" size="18" />Kembali ke Growth Diagnostic — Growth Loop</div>
        </div>
    </div>
    <div class="space-y-4">
        <div class="card p-5">
            <div class="eyebrow-brand mb-2">Logika sistem</div>
            <ol class="space-y-3 text-[13px]">
                <li><b>Kondisi 1.</b> Akuisisi rendah → rekomendasi MyAds + Kuncie.</li>
                <li><b>Kondisi 2.</b> Reach ↑ tapi conversion tetap → "Conversion masih bottleneck", aksi berubah ke optimasi penawaran.</li>
                <li><b>Kondisi 3.</b> Conversion & omzet ↑ tapi repeat ↓ → bottleneck berikutnya retensi, rencana baru.</li>
                <li><b>Kondisi 4.</b> Semua ↑ → Growth Readiness HIGH → MSIGHT / SiteSense / DCE.</li>
            </ol>
        </div>
        <div class="card p-5">
            <div class="eyebrow-brand mb-2">5 lapisan</div>
            <ul class="space-y-2 text-[13px]">
                <li><b>Understand</b> — Diagnostic, Score, AI</li>
                <li><b>Enable</b> — Kuncie, UMKM Plus</li>
                <li><b>Grow</b> — MyAds, DigiAds, RCS</li>
                <li><b>Operate & Measure</b> — DigiPOS, Soundbox, Finance, Inventory</li>
                <li><b>Scale</b> — MSIGHT, SiteSense, DCE</li>
            </ul>
            <p class="mt-3 text-[12px] text-slate">Growth Finance & Growth Inventory adalah kapabilitas baru native platform — bukan produk Telkomsel yang sudah ada.</p>
        </div>
    </div>
</div>
