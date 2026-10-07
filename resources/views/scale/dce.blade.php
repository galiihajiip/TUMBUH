<x-layouts.app title="DCE Pathway">
    <x-page-header eyebrow="Jalur akselerator · selection-based" title="DCE Pathway" subtitle="High-touch growth accelerator: mentoring, benchmarking, pitching. Bukan fitur yang dapat dibeli — kelayakan tidak menjamin diterima." />
    @include('partials.nav-scale')
    @php($stageKeys = array_keys($stages))
    @php($current = $application ? array_search($application->stage, $stageKeys) : -1)
    <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
        <div class="card p-6 {{ $eligibility['eligible'] ? 'border-brand shadow-[var(--shadow-spotlight)]' : '' }}">
            <div class="eyebrow">DCE eligibility score</div>
            <div class="metric mt-1 text-[48px]">{{ $eligibility['score'] }}<span class="text-[16px] text-slate">/100</span></div>
            <div class="text-[15px] font-semibold {{ $eligibility['eligible'] ? 'text-ok' : 'text-slate' }}">{{ $eligibility['eligible'] ? 'You may be ready for DCE.' : 'Belum memenuhi indikator awal (≥ 75).' }}</div>
            <div class="mt-5 space-y-3">@foreach ($eligibility['components'] as $c)<div><div class="mb-1 flex justify-between text-[13px]"><span>{{ $c['label'] }}</span><b>{{ $c['value'] }}</b></div><x-progress :value="$c['value']" /></div>@endforeach</div>
            <p class="mt-4 text-[11px] text-slate">40% Growth Score · 25% performa bisnis · 20% kematangan digital · 15% potensi pertumbuhan.</p>
        </div>
        <div class="card p-6">
            <h3 class="text-[16px] font-semibold">Tahapan pathway</h3>
            <x-stepper class="mt-4" :steps="array_values($stages)" :current="$current + 1" />
            <div class="mt-6">
                @if (! $canApply)
                    <div class="rounded-[12px] bg-muted p-4 text-[13px] text-slate">Bronze: <b>Discover</b> — pelajari DCE. Eligibility assessment & aplikasi tersedia mulai Silver.</div>
                @elseif (! $eligibility['eligible'] && ! $application)
                    <div class="rounded-[12px] bg-muted p-4 text-[13px] text-slate">Tingkatkan Growth Score dan pertumbuhan omzet untuk memenuhi indikator awal.</div>
                @elseif ($application && $application->stage === 'mentoring')
                    <div class="rounded-[12px] bg-ok-tint p-4 text-[13px] text-[#065f46]">Tahap mentoring/akselerator (hasil seleksi simulasi).</div>
                @else
                    <form method="POST" action="{{ route('scale.dce.advance') }}" class="space-y-3">@csrf
                        @if (in_array($application?->stage, ['application', 'eligibility']) || ! $application)
                            <div><label class="label" for="pitch">Ringkasan bisnis & ambisi (profil)</label><textarea id="pitch" name="pitch" rows="3" class="input" placeholder="Apa yang ingin Anda capai dalam 12 bulan?">{{ $application->profile['pitch'] ?? '' }}</textarea></div>
                            <x-field name="support_needed" label="Dukungan yang dibutuhkan" :value="$application->profile['support_needed'] ?? ''" />
                        @endif
                        <button class="btn-primary w-full">{{ ['eligibility' => 'Check DCE eligibility', 'application' => 'Submit application', 'profile_submitted' => 'Kirim ke seleksi', 'selection' => 'Simulasikan hasil seleksi'][$application->stage ?? 'eligibility'] ?? 'Lanjut' }}</button>
                    </form>
                @endif
                @if ($priority)<p class="mt-3 text-[12px] text-slate"><x-icon name="star" size="14" class="text-gold" /> Gold: priority pathway & dukungan aplikasi — tetap melalui seleksi.</p>@endif
                <p class="mt-3 text-[12px] text-slate">Prototipe: status seleksi adalah simulasi. Tidak ada aplikasi nyata yang dikirim.</p>
            </div>
        </div>
    </div>
</x-layouts.app>
