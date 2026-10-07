<x-layouts.app :title="$solution->name">
    <x-page-header :eyebrow="$solution->role" :title="strtoupper($solution->name)" :subtitle="$solution->description">
        <x-slot:actions><x-status-badge :status="$activation->status" /><span class="pill border border-line text-slate">{{ \App\Services\SolutionService::CONNECTION_LABELS[$activation->connection] ?? $activation->connection }}</span></x-slot:actions>
    </x-page-header>
    @include('partials.nav-solutions')
    <div class="grid gap-6 lg:grid-cols-[1.3fr_1fr]">
        <div class="space-y-6">
            <div class="card p-6">
                <div class="eyebrow-brand">Recommended because</div>
                <p class="mt-1 text-[16px] font-semibold">{{ $recommendedFor ? $recommendedFor->whats_wrong : 'Tidak termasuk rekomendasi utama untuk bottleneck Anda saat ini.' }}</p>
                @if ($recommendedFor)<x-why :evidence="$recommendedFor->evidence" />@endif
                @if ($alreadyUses)<p class="mt-3 rounded-[10px] bg-canvas p-3 text-[13px] text-slate">Anda sudah menggunakan {{ $solution->name }} — rekomendasi bersifat <b>optimasi/upgrade</b>, bukan pembelian baru.</p>@endif
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div><div class="text-[13px] font-semibold">What it helps</div><ul class="mt-2 space-y-1.5 text-[13px]">@foreach ($solution->helps as $h)<li class="flex gap-2"><x-icon name="check" size="16" class="text-ok" />{{ $h }}</li>@endforeach</ul></div>
                    <div><div class="text-[13px] font-semibold">How to measure</div><ul class="mt-2 space-y-1.5 text-[13px]">@foreach ($solution->metrics as $m)<li class="flex gap-2"><x-icon name="monitoring" size="16" class="text-slate" />{{ $m }}</li>@endforeach</ul></div>
                </div>
                <div class="mt-5 rounded-[12px] border border-line p-4"><div class="text-[12px] text-slate">Expected objective</div><div class="font-semibold">{{ $solution->objective }}</div></div>
            </div>
            <div class="card p-6">
                <h3 class="text-[16px] font-semibold">Tier eligibility</h3>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    @foreach (['bronze', 'silver', 'gold'] as $t)
                        <div class="module p-3 {{ $business->tier === $t ? 'border-ink ring-[3px] ring-brand-tint' : '' }}"><x-tier-badge :tier="$t" /><div class="mt-2 text-[13px]">{{ $solution->tier_access[$t] ?? '—' }}</div></div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="card p-6">
                <div class="flex items-center justify-between"><h3 class="text-[16px] font-semibold">Aktivasi</h3><span class="pill bg-brand-tint text-brand-deep">{{ $solution->pricingText() }}</span></div>
                @if ($solution->price_note)<p class="mt-1 text-[12px] text-slate">{{ $solution->price_note }}</p>@endif
                @unless ($eligibility['eligible'])
                    <div class="mt-4 rounded-[12px] border border-[#fde68a] bg-warn-tint p-3 text-[13px] text-warn"><b>Eligibility check:</b> {{ implode(' ', $eligibility['reasons']) }}</div>
                @endunless
                <x-stepper class="mt-5" :steps="$steps" :current="$activation->step" />
                <div class="mt-6">
                    @if ($activation->status === 'active')
                        <div class="rounded-[12px] bg-ok-tint p-3 text-[13px] text-[#065f46]"><x-icon name="check_circle" size="16" fill /> {{ $solution->name }} aktif ({{ \App\Services\SolutionService::CONNECTION_LABELS[$activation->connection] ?? $activation->connection }}).</div>
                        @if ($solution->key === 'myads' && app(\App\Services\TierService::class)->allows($business, 'campaigns'))<a href="{{ route('campaigns.index') }}" class="btn-secondary mt-3 w-full">Monitor kampanye</a>@endif
                    @elseif (! $canActivate)
                        <div class="rounded-[12px] bg-muted p-3 text-[13px] text-slate"><x-icon name="lock" size="16" /> Aktivasi tersedia mulai tier <b>{{ ucfirst($solution->min_tier) }}</b>. Di tier Anda: {{ $solution->tier_access[$business->tier] ?? 'rekomendasi' }}.</div>
                        <a href="{{ route('rewards.tiers') }}" class="btn-secondary mt-3 w-full">Bandingkan tier</a>
                    @elseif ($solution->key === 'dce')
                        <a href="{{ route('scale.dce') }}" class="btn-primary w-full">Buka DCE Pathway</a>
                    @else
                        <form method="POST" action="{{ route('solutions.advance', $solution) }}">@csrf
                            <button class="btn-primary w-full">{{ $activation->step === 0 ? 'Activate '.$solution->name : 'Lanjutkan: '.($steps[$activation->step] ?? 'langkah berikutnya') }} <x-icon name="arrow_forward" size="18" /></button>
                        </form>
                    @endif
                </div>
                <x-sim-note class="mt-4" text="Prototype Integration — langkah aktivasi adalah state machine lokal. Tidak ada akun, verifikasi, atau pembayaran nyata." />
            </div>
        </div>
    </div>
</x-layouts.app>
