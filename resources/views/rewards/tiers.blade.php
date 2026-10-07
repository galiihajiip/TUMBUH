<x-layouts.app title="Tier">
    <x-page-header eyebrow="Perbandingan tingkat pertumbuhan" title="Bronze · Silver · Gold" subtitle="Tier mengikuti kebutuhan dan kematangan bisnis. Akses tier ke suatu capability tidak selalu berarti penggunaan produknya gratis." />
    @php($rows = [
        ['Growth Diagnostic', 'Basic', 'Advanced', 'Advanced + integrated'], ['AI Assistant', 'Basic advice', 'Personalized', 'Predictive strategy'],
        ['Weekly Mission', '✓', '✓ Personalized', '✓ Personalized'], ['Action Plan', 'Basic', '✓ Personalized', '✓ Personalized'],
        ['Kuncie', 'Free learning', 'Personalized learning', 'Premium / mentor'], ['UMKM Plus', 'Recommend', 'Activate', 'Optimize'],
        ['MyAds', 'Recommend', 'Activate', 'Optimize'], ['DigiAds', '—', 'Eligibility', 'Advanced targeting'], ['DigiPOS', 'Basic activity', 'Sales tracking', 'Integrated measurement'],
        ['Soundbox', 'Eligibility', 'Activate', 'Transaction integration*'], ['RCS', '—', 'Recommend', 'Campaign'],
        ['Growth Finance', 'Manual', 'Smart / OCR + P&L', 'Advanced analytics'], ['Growth Inventory', 'Basic', 'Automated + COGS', 'Predictive'],
        ['Growth Ledger', 'Basic (3 bulan)', 'Full', 'Advanced'], ['Before vs After', '—', '✓', '✓'], ['AI Performance Review', 'Basic', '✓', '✓ Advanced'],
        ['MSIGHT', '—', 'Eligibility', 'Advanced insight'], ['SiteSense', '—', 'Readiness', 'Expansion intelligence'], ['DCE', 'Discover', 'Apply / eligibility', 'Priority pathway**'],
        ['Expansion Readiness', '—', 'Basic', '✓ Advanced'], ['Growth Passport · Leaderboard · Reward', '✓', '✓', '✓'], ['Data Governance', '✓', '✓', '✓'],
    ])
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[760px] text-[13px]">
            <thead>
                <tr class="border-b border-line">
                    <th class="px-5 py-4 text-left text-[12px] font-semibold text-slate">Capability</th>
                    @foreach (config('tumbuh.tiers') as $k => $t)
                        <th class="px-5 py-4 text-left {{ $business->tier === $k ? 'bg-brand-soft' : '' }}"><x-tier-badge :tier="$k" :theme="true" /><div class="mt-1 text-[12px] font-normal text-slate">{{ $t['goal'] }} · {{ $t['price'] }}</div>@if ($business->tier === $k)<div class="mt-1 text-[11px] font-semibold text-brand-deep">Tier Anda</div>@endif</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>@foreach ($rows as [$cap, $b, $s, $g])<tr class="border-b border-line last:border-0"><td class="px-5 py-2.5 font-semibold">{{ $cap }}</td><td class="px-5 py-2.5 {{ $business->tier === 'bronze' ? 'bg-brand-soft/60' : '' }}">{{ $b }}</td><td class="px-5 py-2.5 {{ $business->tier === 'silver' ? 'bg-brand-soft/60' : '' }}">{{ $s }}</td><td class="px-5 py-2.5 {{ $business->tier === 'gold' ? 'bg-brand-soft/60' : '' }}">{{ $g }}</td></tr>@endforeach</tbody>
        </table>
    </div>
    <p class="mt-4 text-[12px] text-slate">* Bergantung pada integrasi partner/acquirer yang tersedia dan persetujuan pengguna. ** Gold tidak menjamin diterima di DCE — DCE tetap berbasis seleksi. Biaya penggunaan produk (mis. saldo MyAds) terpisah dari langganan TUMBUH.</p>
</x-layouts.app>
