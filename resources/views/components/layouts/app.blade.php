@props(['title' => null])
@php
    $nav = [
        ['overview', 'Overview', 'Ringkasan', 'space_dashboard', ['overview']],
        ['growth.index', 'My Growth', 'Pertumbuhan', 'trending_up', ['growth.*', 'diagnostic.*', 'assistant']],
        ['missions.index', 'Missions', 'Misi Bisnis', 'flag', ['missions.*', 'plan.*']],
        ['solutions.index', 'Solutions', 'Solusi Telkomsel', 'hub', ['solutions.*', 'campaigns.*']],
        ['operate.index', 'Operate', 'Operasional', 'storefront', ['operate.*', 'finance.*', 'inventory.*']],
        ['analytics.index', 'Analytics', 'Analitik', 'monitoring', ['analytics.*']],
        ['rewards.index', 'Rewards', 'Peringkat & Hadiah', 'emoji_events', ['rewards.*']],
        ['scale.index', 'Scale', 'Ekspansi Usaha', 'rocket_launch', ['scale.*']],
        ['profile.index', 'Profile', 'Data Bisnis', 'person', ['profile.*', 'notifications.*']],
    ];
@endphp
<x-layouts.base :title="$title ?? null">
    @if (config('tumbuh.demo.enabled'))
        <div class="bg-ink px-4 py-1.5 text-center text-[11px] font-medium text-white/80">
            <x-icon name="science" size="14" class="text-[#c4b5fd]" /> Demo Environment — semua integrasi Telkomsel disimulasikan secara lokal (Prototype Integration).
            <a href="{{ route('demo.index') }}" class="ml-2 font-semibold text-white underline underline-offset-2">Demo Panel</a>
        </div>
    @endif
    <div x-data="{ nav: false }" class="flex min-h-[calc(100vh-28px)]">
        {{-- Sidebar --}}
        <aside :class="nav ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 flex w-[260px] flex-col border-r border-line bg-white transition-transform lg:static lg:translate-x-0">
            <div class="flex h-[72px] items-center px-5"><a href="{{ route('overview') }}"><x-logo /></a></div>
            <div class="eyebrow px-6 pb-2 pt-2">Menu Navigasi</div>
            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3">
                @foreach ($nav as [$route, $label, $id, $icon, $patterns])
                    @php($active = request()->routeIs(...$patterns))
                    <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-[12px] px-3 py-2.5 transition {{ $active ? 'bg-brand text-white shadow-[var(--shadow-spotlight)]' : 'text-ink-2 hover:bg-muted' }}">
                        <x-icon :name="$icon" :fill="$active" />
                        <span class="leading-tight"><span class="block text-[14px] font-semibold">{{ $label }}</span><span class="block text-[11px] {{ $active ? 'text-white/80' : 'text-slate' }}">{{ $id }}</span></span>
                    </a>
                @endforeach
            </nav>
            @if ($shellBusiness)
                <a href="{{ route('rewards.tiers') }}" class="m-3 block rounded-[14px] border border-line bg-canvas p-3.5 hover:border-line-strong">
                    <div class="flex items-center justify-between">
                        <x-tier-badge :tier="$shellBusiness->tier" :theme="true" />
                        <x-icon name="chevron_right" size="18" class="text-slate" />
                    </div>
                    <div class="mt-2 text-[12px] text-slate">{{ config("tumbuh.tiers.{$shellBusiness->tier}.goal") }}</div>
                    @if ($shellBusiness->tier_note)<div class="mt-1 text-[11px] text-slate-2">{{ $shellBusiness->tier_note }}@if ($shellBusiness->tier_expires_at) · s/d {{ $shellBusiness->tier_expires_at->translatedFormat('d M') }}@endif</div>@endif
                </a>
            @endif
        </aside>
        <div x-show="nav" x-cloak @click="nav = false" class="fixed inset-0 z-30 bg-ink/30 lg:hidden"></div>

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Topbar --}}
            <header class="sticky top-0 z-20 flex h-[72px] items-center gap-3 border-b border-line bg-white/95 px-4 backdrop-blur md:px-8">
                <button type="button" @click="nav = true" class="btn-ghost h-10 w-10 px-0 lg:hidden" aria-label="Buka menu"><x-icon name="menu" /></button>
                @if ($shellBusiness)
                    <div class="flex min-w-0 items-center gap-2 rounded-full border border-line bg-canvas py-1.5 pl-2 pr-4">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon name="storefront" size="16" /></span>
                        <span class="truncate text-[13px] font-semibold">{{ $shellBusiness->name }}</span>
                        <span class="hidden truncate text-[12px] text-slate md:inline">({{ $shellBusiness->locationLabel() }})</span>
                    </div>
                @endif
                @if ($shellScore)
                    <a href="{{ route('growth.score') }}" class="hidden items-center gap-2 whitespace-nowrap rounded-full px-3 py-1.5 text-[12px] hover:bg-muted xl:flex">
                        <span class="text-slate">Growth Score</span><span class="font-display text-[15px] font-extrabold">{{ $shellScore->overall }}</span>
                        <span class="text-slate">· Fokus</span><span class="font-semibold text-brand-deep">{{ config("tumbuh.dimensions.{$shellScore->bottleneck}.label") }}</span>
                    </a>
                @endif
                <div class="ml-auto flex items-center gap-2">
                    <a href="{{ route('app.how-it-works') }}" class="btn-ghost btn-sm hidden xl:inline-flex"><x-icon name="all_inclusive" size="18" />Growth Loop</a>
                    <a href="{{ route('notifications.index') }}" class="relative flex h-10 w-10 items-center justify-center rounded-full border border-line bg-white hover:bg-muted" aria-label="Notifikasi">
                        <x-icon name="notifications" />
                        @if ($shellUnread)<span class="absolute -right-0.5 -top-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold text-white">{{ $shellUnread }}</span>@endif
                    </a>
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 hover:bg-muted">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand text-[13px] font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="hidden text-left leading-tight sm:block"><span class="block text-[13px] font-semibold">{{ auth()->user()->firstName() }}</span><span class="block text-[11px] text-slate">{{ auth()->user()->roleLabel() }}</span></span>
                            <x-icon name="expand_more" size="18" class="text-slate" />
                        </button>
                        <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-56 rounded-[14px] border border-line bg-white p-1.5 shadow-[var(--shadow-modal)]">
                            <a href="{{ route('profile.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] hover:bg-muted"><x-icon name="person" size="18" />My Business Data</a>
                            <a href="{{ route('demo.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] hover:bg-muted"><x-icon name="science" size="18" />Demo Panel</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-[13px] text-brand-deep hover:bg-brand-tint"><x-icon name="logout" size="18" />Keluar</button></form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[1280px] flex-1 px-4 py-6 md:px-8 md:py-8">
                <x-flash />
                {{ $slot }}
            </main>
            <footer class="border-t border-line px-4 py-5 text-[11px] text-slate md:px-8">
                <div class="mx-auto flex max-w-[1280px] flex-wrap items-center justify-between gap-2">
                    <span><x-logo size="sm" :sub="false" class="mr-2 align-middle" /> TUMBUH — Telkomsel untuk UMKM Tumbuh · Prototipe Business Case Competition</span>
                    <span>Semua angka adalah data simulasi / ilustratif. Tidak ada integrasi produksi Telkomsel.</span>
                </div>
            </footer>
        </div>
    </div>
</x-layouts.base>
