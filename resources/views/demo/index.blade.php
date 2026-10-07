@php($layout = $business ? 'layouts.app' : 'layouts.public')
<x-dynamic-component :component="$layout" title="Demo Panel">
    <div class="{{ $business ? '' : 'mx-auto max-w-[1100px] px-4 py-10 md:px-8' }}">
        <x-page-header eyebrow="Demo Environment" title="Demo Panel" subtitle="Untuk presentasi kompetisi. Setiap aksi menjalankan service yang sama dengan produk — sebab-akibat terlihat nyata, data tetap simulasi." />
        <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
            <div class="card p-6">
                <h3 class="text-[16px] font-semibold">Pilih akun demo</h3>
                <p class="text-[12px] text-slate">Password: <code class="rounded bg-muted px-1">{{ config('tumbuh.demo.password') }}</code> · OTP demo: <code class="rounded bg-muted px-1">{{ config('tumbuh.demo.otp') }}</code></p>
                <div class="mt-4 space-y-2">
                    @foreach ($accounts as $key => $a)
                        <div class="flex items-center gap-3 rounded-[12px] border p-3 {{ $business && $business->demo_key === $key ? 'border-brand bg-brand-soft' : 'border-line' }}">
                            <div class="min-w-0 flex-1"><div class="flex items-center gap-2 text-[14px] font-semibold">{{ $a['business'] }}<x-tier-badge :tier="$a['tier']" /></div><div class="text-[12px] text-slate">{{ $a['name'] }} · {{ $a['story'] }}</div></div>
                            <form method="POST" action="{{ route('demo.login', $key) }}">@csrf<button class="btn-primary btn-sm">Masuk</button></form>
                            <form method="POST" action="{{ route('demo.reset', $key) }}" onsubmit="return confirm('Reset skenario {{ $a['business'] }} ke kondisi awal?')">@csrf<button class="btn-ghost btn-sm" title="Reset scenario"><x-icon name="restart_alt" size="18" /></button></form>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 rounded-[12px] bg-canvas p-4 text-[13px]">
                    <div class="font-semibold">Alur demo 5–7 menit (Chacha Coffee)</div>
                    <ol class="mt-2 list-decimal space-y-1 pl-5 text-slate">
                        <li>Overview → Growth Score 64, bottleneck Customer Acquisition</li>
                        <li>AI Assistant → MyAds + Kuncie, rencana 30 hari</li>
                        <li>Activation Center → lanjutkan MyAds (verifikasi → top up)</li>
                        <li><b>Launch Campaign</b> → reach, leads, konversi (Simulated)</li>
                        <li><b>Complete Mission</b> ×2 → <b>Close 30-Day Cycle</b></li>
                        <li>Growth Ledger · Before vs After · AI Review</li>
                        <li>Retention menjadi bottleneck berikutnya → rencana baru (loop)</li>
                    </ol>
                    <div class="mt-3 text-slate">Opsional: Sari Bakery (Finance + Inventory) · Nusantara Fashion (MSIGHT, SiteSense, DCE).</div>
                </div>
            </div>
            <div class="space-y-6">
                @if ($business)
                    <div class="card p-6">
                        <div class="flex items-center justify-between"><h3 class="text-[16px] font-semibold">Aksi demo · {{ $business->name }}</h3><x-tier-badge :tier="$business->tier" /></div>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach ($actions as $k => $a)
                                <form method="POST" action="{{ route('demo.action', $k) }}">@csrf
                                    <button class="module flex h-full w-full items-start gap-3 p-3 text-left transition hover:border-ink {{ $k === 'close_cycle' ? 'border-brand bg-brand-soft' : '' }}">
                                        <x-icon :name="$a['icon']" class="text-brand" /><span><span class="block text-[13px] font-semibold">{{ $a['label'] }}</span><span class="block text-[11px] text-slate">{{ $a['help'] }}</span></span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                    @if ($business->user?->is_demo)
                        <form method="POST" action="{{ route('demo.tier') }}" class="card p-6">@csrf
                            <h3 class="text-[16px] font-semibold">Tier preview (Demo Mode)</h3>
                            <p class="text-[12px] text-slate">Mengubah tier akun demo untuk menunjukkan aturan akses. Pembatasan tetap ditegakkan di server.</p>
                            <div class="mt-3 flex gap-2">@foreach (['bronze', 'silver', 'gold'] as $t)<button name="tier" value="{{ $t }}" class="btn-sm {{ $business->tier === $t ? 'btn-dark' : 'btn-secondary' }}">{{ ucfirst($t) }}</button>@endforeach</div>
                        </form>
                    @endif
                @else
                    <x-empty-state icon="login" title="Masuk ke akun demo" text="Aksi demo (Complete Mission, Launch Campaign, Close Cycle, dll.) tersedia setelah masuk." />
                @endif
            </div>
        </div>
    </div>
</x-dynamic-component>
