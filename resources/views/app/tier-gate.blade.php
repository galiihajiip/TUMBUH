<x-layouts.app title="Fitur terkunci">
    @php($business = current_business())
    <div class="mx-auto max-w-2xl">
        <div class="card overflow-hidden">
            <div class="bg-canvas px-6 py-8 text-center md:px-10">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-slate shadow-[var(--shadow-card)]"><x-icon name="lock" size="28" /></span>
                <div class="eyebrow-brand mt-4">Status fitur terkunci</div>
                <h1 class="mt-1 text-[24px] font-bold">{{ $featureLabel }}</h1>
                <p class="mx-auto mt-2 max-w-md text-[14px] text-slate">Fitur ini tersedia mulai tier <b>{{ ucfirst($required) }} — {{ config("tumbuh.tiers.{$required}.theme") }}</b> ({{ config("tumbuh.tiers.{$required}.goal") }}). Tier Anda saat ini: <b>{{ $business?->tierLabel() }}</b>.</p>
                <div class="mt-4 flex justify-center gap-2"><x-tier-badge :tier="$business?->tier ?? 'bronze'" /><x-icon name="arrow_forward" class="text-slate-2" /><x-tier-badge :tier="$required" /></div>
            </div>
            <div class="grid gap-4 p-6 md:grid-cols-2 md:p-8">
                <div>
                    <div class="font-semibold">Cara membuka</div>
                    <ul class="mt-2 space-y-2 text-[13px] text-slate">
                        <li class="flex gap-2"><x-icon name="emoji_events" size="18" class="text-gold" />Raih Top 20% leaderboard — reward "Free {{ ucfirst($required) }} 1 bulan" mengikuti hasil, bukan aktivitas.</li>
                        <li class="flex gap-2"><x-icon name="upgrade" size="18" class="text-brand" />Upgrade tier (Silver: berbayar · Gold: premium).</li>
                    </ul>
                </div>
                <div class="flex flex-col justify-end gap-2">
                    <a href="{{ route('rewards.tiers') }}" class="btn-primary">Bandingkan tier</a>
                    <a href="{{ route('rewards.index') }}" class="btn-secondary">Lihat leaderboard & reward</a>
                </div>
            </div>
            <div class="border-t border-line px-6 py-3 text-[12px] text-slate md:px-8">Akses diperiksa di server (HTTP 403) — bukan sekadar tombol yang disembunyikan. Akun demo dapat mencoba tier lain lewat <a class="link" href="{{ route('demo.index') }}">Demo Panel</a>.</div>
        </div>
    </div>
</x-layouts.app>
