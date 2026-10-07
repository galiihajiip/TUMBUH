<x-layouts.app title="Leaderboard & Rewards">
    <x-page-header eyebrow="Rewards" title="Monthly Growth Leaderboard" subtitle="Peringkat berdasarkan Growth Achievement Score — bukan omzet terbesar — agar UMKM kecil bersaing adil.">
        <x-slot:actions><a href="{{ route('rewards.tiers') }}" class="btn-secondary btn-sm">Bronze · Silver · Gold</a></x-slot:actions>
    </x-page-header>
    <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
        <div class="space-y-6">
            <div class="card p-6">
                <div class="eyebrow">Your rank</div>
                <div class="mt-1 flex items-end gap-3"><span class="metric text-[48px] leading-none">#{{ $position['rank'] ?? '—' }}</span><span class="pb-1 text-[13px] text-slate">dari {{ $position['total'] }} UMKM · skor {{ num($position['score'], 1) }}</span></div>
                @if ($position['next'] && $position['gap'] !== null)<p class="mt-2 text-[14px]">You are <b class="text-brand-deep">{{ num($position['gap'], 1) }} points</b> away from {{ $position['next'] }}.</p>@elseif ($position['rank'] === 1)<p class="mt-2 text-[14px] font-semibold text-ok">Anda #1 bulan ini 🏆</p>@endif
                <div class="mt-5 space-y-3">
                    @foreach ($weights as $k => $w)
                        <div><div class="mb-1 flex justify-between text-[12px]"><span>{{ ['target_achievement' => 'Target achievement', 'mission_completion' => 'Mission completion', 'kpi_improvement' => 'KPI improvement', 'data_discipline' => 'Data discipline'][$k] }} <span class="text-slate">· {{ (int) ($w * 100) }}%</span></span><b class="tabular">{{ num($position['components'][$k] ?? 0, 0) }}</b></div><x-progress :value="$position['components'][$k] ?? 0" /></div>
                    @endforeach
                </div>
                <p class="mt-4 text-[11px] text-slate">Achievement = 50% target + 25% misi + 15% perbaikan KPI + 10% disiplin data.</p>
            </div>
            <div class="card p-6">
                <h3 class="text-[16px] font-semibold">Reward Center</h3>
                <p class="text-[12px] text-slate">Reward follows outcome, not activity.</p>
                <div class="mt-4 space-y-2">
                    @foreach ($catalog as $key => $reward)
                        @php($br = $unlocked->firstWhere('reward_id', $reward->id))
                        @php($rule = $rules[$key] ?? null)
                        <div class="flex items-center gap-3 rounded-[12px] border p-3 {{ $br ? 'border-gold/50 bg-gold-tint' : 'border-line' }}">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full {{ $br ? 'bg-gold text-white' : 'bg-muted text-slate' }}"><x-icon :name="$reward->icon" size="18" /></span>
                            <div class="min-w-0 flex-1"><div class="text-[13px] font-semibold">{{ $reward->name }}</div><div class="text-[11px] text-slate">{{ $br ? 'Dibuka: '.$br->reason : $reward->criteria }}</div></div>
                            @if ($br && $br->status === 'unlocked')
                                <form method="POST" action="{{ route('rewards.claim', $br->id) }}">@csrf<button class="btn-primary btn-sm">Klaim</button></form>
                            @elseif ($br)
                                <span class="pill bg-ok-tint text-ok">Diklaim</span>
                            @else
                                <x-icon name="lock" size="16" class="text-slate-2" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-[16px] font-semibold">Badges</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse ($achievements as $a)<span class="pill border border-line bg-white py-1.5 text-ink" title="{{ $a->description }}"><x-icon :name="$a->icon" size="15" class="text-gold" />{{ $a->title }}</span>@empty<span class="text-[13px] text-slate">Belum ada badge.</span>@endforelse
                </div>
            </div>
        </div>
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-line px-5 py-4"><h3 class="text-[16px] font-semibold">🏆 {{ now()->translatedFormat('F Y') }}</h3><span class="pill bg-[#f5f3ff] text-[#6d28d9]">Peer cohort: ilustratif</span></div>
            <ol class="divide-y divide-line">
                @foreach ($board->take(15) as $row)
                    @php($me = $row['business']->id === $business->id)
                    <li class="flex items-center gap-3 px-5 py-2.5 {{ $me ? 'bg-brand-soft' : '' }}">
                        <span class="w-7 text-center font-display text-[15px] font-bold {{ $row['rank'] <= 3 ? 'text-gold' : 'text-slate' }}">{{ $row['rank'] }}</span>
                        <span class="flex-1 text-[13px] {{ $me ? 'font-bold' : '' }}">{{ $row['business']->name }} @if ($me)<span class="pill bg-brand text-white">Anda</span>@endif<span class="block text-[11px] font-normal text-slate">{{ $row['business']->category }} · {{ $row['business']->city }}</span></span>
                        <x-tier-badge :tier="$row['business']->tier" />
                        <span class="w-12 text-right font-display text-[15px] font-bold tabular">{{ num($row['score'], 0) }}</span>
                    </li>
                @endforeach
                @if (($position['rank'] ?? 0) > 15)
                    <li class="px-5 py-2 text-center text-slate">⋯</li>
                    <li class="flex items-center gap-3 bg-brand-soft px-5 py-2.5"><span class="w-7 text-center font-display text-[15px] font-bold">{{ $position['rank'] }}</span><span class="flex-1 text-[13px] font-bold">{{ $business->name }} <span class="pill bg-brand text-white">Anda</span></span><x-tier-badge :tier="$business->tier" /><span class="w-12 text-right font-display text-[15px] font-bold">{{ num($position['score'], 0) }}</span></li>
                @endif
            </ol>
        </div>
    </div>
</x-layouts.app>
