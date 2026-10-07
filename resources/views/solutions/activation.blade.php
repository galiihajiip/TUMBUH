<x-layouts.app title="Activation Center">
    <x-page-header :eyebrow="$recommendation ? 'Solusi untuk '.($recommendation->bottleneck === 'expansion' ? 'ekspansi' : config('tumbuh.dimensions.'.$recommendation->bottleneck.'.label')).' · Tier '.$business->tierLabel() : 'Activation Center'" title="Pusat Aktivasi Solusi" subtitle="Satu halaman untuk semua aktivasi. Pantau status integrasi ekosistem Telkomsel dan selesaikan aktivasi prioritas." />
    @include('partials.nav-solutions')
    @php($counts = $activations->countBy('status'))
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Layanan aktif" :value="($counts['active'] ?? 0).' / '.$activations->count()" icon="check_circle"><x-progress class="mt-3" tone="ok" :value="($counts['active'] ?? 0) / max(1, $activations->count()) * 100" /></x-stat-card>
        <x-stat-card label="Perlu tindakan" :value="($counts['activation_required'] ?? 0) + ($counts['eligibility_check'] ?? 0)" icon="pending_actions" tone="brand" sub="Activation required · Eligibility check" />
        <x-stat-card label="Mode integrasi" value="Simulated" icon="science" sub="Prototype Integration — bukan produksi" />
    </div>
    <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <div class="space-y-3">
            @foreach ($activations as $a)
                @php($s = $a->solution)
                <div class="card p-5 {{ $focus && $focus->id === $a->id ? 'border-brand shadow-[var(--shadow-spotlight)]' : '' }}">
                    <div class="flex flex-wrap items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[12px] bg-canvas text-brand"><x-icon :name="$s->icon" /></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><h3 class="text-[16px] font-semibold">{{ $s->name }}</h3><x-status-badge :status="$a->status" /></div>
                            <p class="text-[13px] text-slate">{{ $s->tagline }}</p>
                            @php($steps = $service->steps($s))
                            <div class="mt-2 flex items-center gap-2 text-[12px] text-slate"><x-progress class="max-w-[160px]" :value="$a->step / max(1, count($steps)) * 100" :tone="$a->status === 'active' ? 'ok' : 'brand'" /><span>Langkah {{ min($a->step, count($steps)) }}/{{ count($steps) }}@if ($a->status !== 'active' && isset($steps[$a->step])) · berikutnya: {{ $steps[$a->step] }}@endif</span></div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="pill border border-line text-slate">{{ $s->pricingText() }}</span>
                            <a href="{{ route('solutions.show', $s) }}" class="text-[13px] font-semibold text-brand-deep">{{ $a->status === 'active' ? 'Kelola' : 'Aktivasi' }} →</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div>
            @if ($focus)
                @php($fs = $focus->solution)
                <div class="card sticky top-24 border-brand p-6">
                    <div class="flex items-center gap-2"><span class="pill bg-brand text-white">Aktivasi tertunda</span><span class="text-[12px] text-slate">{{ $fs->name }}</span></div>
                    <h3 class="mt-2 text-[20px] font-semibold">{{ $fs->key === 'myads' ? 'Aktivasi Iklan Lokasi Toko' : 'Aktivasi '.$fs->name }}</h3>
                    <div class="mt-4 flex items-center justify-between text-[13px]"><span>Langkah {{ $focus->step + 1 }} dari {{ count($focusSteps) }}</span><b class="text-brand-deep">{{ round($focus->step / count($focusSteps) * 100) }}% selesai</b></div>
                    <x-progress class="mt-2" tone="brand" :value="$focus->step / count($focusSteps) * 100" />
                    <x-stepper class="mt-5" :steps="$focusSteps" :current="$focus->step" />
                    <form method="POST" action="{{ route('solutions.advance', $fs) }}" class="mt-5">@csrf
                        <button class="btn-primary w-full">Lanjutkan: {{ $focusSteps[$focus->step] ?? '' }} <x-icon name="arrow_forward" size="18" /></button>
                    </form>
                    <x-sim-note class="mt-4" text="Alur mengikuti proses produk (mis. MyAds: registrasi → verifikasi → approval → top up → kampanye → monitoring) sebagai state machine lokal." />
                </div>
            @else
                <x-empty-state icon="verified" title="Tidak ada aktivasi tertunda" text="Pilih solusi dari rekomendasi untuk memulai aktivasi." />
            @endif
        </div>
    </div>
</x-layouts.app>
