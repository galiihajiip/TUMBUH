<?php

namespace App\Services\Demo;

use App\Models\Business;
use App\Models\Diagnostic;
use App\Models\DiagnosticQuestion;
use App\Models\GrowthScoreSnapshot;
use App\Models\KpiSnapshot;
use App\Models\LeaderboardSnapshot;
use App\Models\TelkomselSolution;
use App\Models\User;
use App\Services\ActionPlanService;
use App\Services\DiagnosticService;
use App\Services\FinancialAnalyticsService;
use App\Services\GrowthScoreService;
use App\Services\LeaderboardService;
use App\Services\MissionService;
use App\Services\NotificationService;
use App\Services\PassportService;
use App\Services\RecommendationService;
use App\Services\RewardService;
use App\Services\SolutionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Builds the four demo scenarios (illustrative data — not real companies)
 * and the peer leaderboard cohort. Used by DatabaseSeeder and by the
 * "Reset scenario" demo action, so every reset is identical.
 */
class DemoScenarioBuilder
{
    public const ACCOUNTS = [
        'aisha' => ['name' => 'Aisha Rahma', 'email' => 'aisha@tumbuh.demo', 'business' => 'Aisha Dessert House', 'tier' => 'bronze', 'story' => 'Early-stage · acquisition bottleneck · manual operations'],
        'chacha' => ['name' => 'Chacha Pratama', 'email' => 'chacha@tumbuh.demo', 'business' => 'Chacha Coffee', 'tier' => 'silver', 'story' => 'Core closed loop · acquisition → retention'],
        'sari' => ['name' => 'Sari Wijaya', 'email' => 'sari@tumbuh.demo', 'business' => 'Sari Bakery', 'tier' => 'silver', 'story' => 'Strong sales · inventory & cost-control bottleneck'],
        'bima' => ['name' => 'Bima Santoso', 'email' => 'bima@tumbuh.demo', 'business' => 'Nusantara Fashion', 'tier' => 'gold', 'story' => 'Scaling · MSIGHT, SiteSense & DCE pathway'],
    ];

    private Carbon $today;

    public function __construct(
        private DiagnosticService $diagnostics,
        private GrowthScoreService $scores,
        private RecommendationService $recommendations,
        private ActionPlanService $plans,
        private MissionService $missions,
        private SolutionService $solutions,
        private PassportService $passport,
        private RewardService $rewards,
        private NotificationService $notifications,
        private FinancialAnalyticsService $finance,
        private LeaderboardService $leaderboard,
    ) {
        $this->today = now()->startOfDay();
    }

    public function buildAll(): void
    {
        foreach (array_keys(self::ACCOUNTS) as $key) {
            $this->build($key);
        }
        $this->peers();
    }

    /** (Re)build one scenario from scratch. Returns the demo user. */
    public function build(string $key): User
    {
        return DB::transaction(function () use ($key) {
            User::where('email', self::ACCOUNTS[$key]['email'])->get()->each->delete();

            return match ($key) {
                'aisha' => $this->aisha(),
                'chacha' => $this->chacha(),
                'sari' => $this->sari(),
                'bima' => $this->bima(),
            };
        });
    }

    // ---------------------------------------------------------------- helpers

    private function user(string $key, string $phone): User
    {
        $a = self::ACCOUNTS[$key];
        $user = User::create([
            'name' => $a['name'], 'email' => $a['email'], 'phone' => $phone, 'role' => 'owner',
            'password' => config('tumbuh.demo.password'), 'terms_accepted_at' => now(), 'is_demo' => true,
        ]);
        $user->forceFill(['email_verified_at' => now(), 'phone_verified_at' => now()])->save();

        foreach (['marketing' => false, 'analytics' => true, 'partner' => true] as $type => $granted) {
            $user->consents()->create(['consent_type' => $type, 'granted' => $granted, 'granted_at' => $granted ? now()->subMonths(2) : null]);
        }

        return $user;
    }

    private function business(User $user, string $key, array $attrs): Business
    {
        return $user->business()->create($attrs + [
            'name' => self::ACCOUNTS[$key]['business'],
            'slug' => $key,
            'tier' => self::ACCOUNTS[$key]['tier'],
            'demo_key' => $key,
            'onboarding_step' => 7,
        ]);
    }

    private function month(int $monthsAgo): Carbon
    {
        return $this->today->copy()->startOfMonth()->subMonthsNoOverflow($monthsAgo);
    }

    private function ledger(Business $b, array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $out[] = KpiSnapshot::create($r + ['business_id' => $b->id]);
        }

        return $out;
    }

    /** Diagnostic with fixed answers, anchored to a KPI snapshot, scored at $at. */
    private function diagnose(Business $b, array $answers, KpiSnapshot $anchor, Carbon $at): GrowthScoreSnapshot
    {
        $diagnostic = $b->diagnostics()->create(['cycle' => 1, 'status' => 'completed', 'answered_at' => $at, 'baseline_confirmed_at' => $at, 'kpi_snapshot_id' => $anchor->id]);
        $questions = DiagnosticQuestion::active()->get()->keyBy('key');
        foreach ($answers as $qKey => $value) {
            $this->diagnostics->saveAnswer($diagnostic, $questions[$qKey], $value);
        }

        $score = $this->scores->fromDiagnostic($b, $diagnostic);
        $score->update(['calculated_at' => $at]);
        $anchor->update(['growth_score' => $score->overall]);
        $b->achievements()->create(['key' => 'first_diagnostic', 'title' => 'Know Your Business', 'description' => 'Menyelesaikan Growth Diagnostic pertama', 'icon' => 'troubleshoot', 'earned_at' => $at]);

        return $score;
    }

    /** Fill historical ledger scores between the diagnostic and the latest month. */
    private function interpolateScores(Business $b): void
    {
        $rows = $b->kpiSnapshots()->get()->values();
        $known = $rows->filter(fn ($r) => $r->growth_score !== null);
        if ($known->count() < 2) {
            return;
        }
        $first = $known->keys()->first();
        $last = $known->keys()->last();
        $from = $rows[$first]->growth_score;
        $to = $rows[$last]->growth_score;
        for ($i = $first + 1; $i < $last; $i++) {
            $rows[$i]->update(['growth_score' => (int) round($from + ($to - $from) * ($i - $first) / ($last - $first))]);
        }
    }

    private function backdate(GrowthScoreSnapshot $s, Carbon $at): GrowthScoreSnapshot
    {
        $s->update(['calculated_at' => $at, 'created_at' => $at]);

        return $s;
    }

    private function activate(Business $b, string $key, int $step, ?Carbon $at = null): void
    {
        $solution = TelkomselSolution::where('key', $key)->firstOrFail();
        $activation = $this->solutions->activation($b, $solution);
        $total = count($this->solutions->steps($solution));
        $done = $step >= $total;
        $activation->update([
            'step' => min($step, $total),
            'status' => $done ? 'active' : ($key === 'myads' || $key === 'rcs' ? 'activation_required' : 'eligibility_check'),
            'connection' => $done ? ($b->usesProduct($key) ? 'self_reported' : 'simulated') : 'pending',
            'activated_at' => $done ? ($at ?? now()) : null,
        ]);
    }

    private function plan(Business $b, GrowthScoreSnapshot $score, int $completedWeeks, Carbon $startedAt): void
    {
        $rec = $this->recommendations->generate($b->fresh(), $score);
        $rec->update(['created_at' => $startedAt]);
        $plan = $this->plans->generate($b->fresh(), $rec);
        $plan->update(['starts_on' => $startedAt->toDateString()]);

        foreach ($b->missions()->orderBy('week')->get() as $m) {
            $m->update(['due_on' => $startedAt->copy()->addDays(7 * $m->week)->toDateString()]);
        }

        for ($w = 1; $w <= $completedWeeks; $w++) {
            $mission = $b->missions()->where('week', $w)->first();
            $this->missions->submit($mission, ['completion' => 'yes', 'kpi_value' => $mission->kpi_target, 'notes' => 'Check-in minggu '.$w]);
            $mission->refresh()->update(['completed_at' => $startedAt->copy()->addDays(7 * $w - 1)]);
        }
    }

    /** Deterministic pseudo-variation in [-1, 1]. */
    private function wobble(int $seed): float
    {
        return (($seed * 7919) % 200) / 100 - 1;
    }

    // -------------------------------------------------------------- scenarios

    /** Demo A — Bronze, early stage, acquisition bottleneck, manual ops. */
    private function aisha(): User
    {
        $user = $this->user('aisha', '081234560001');
        $joined = $this->today->copy()->subDays(9);
        $b = $this->business($user, 'aisha', [
            'category' => 'Food & Beverage', 'sub_category' => 'Dessert', 'start_year' => 2023,
            'description' => 'Dessert box & kue rumahan, dipesan lewat WhatsApp dan dijual di toko kecil depan rumah.',
            'province' => 'Jawa Timur', 'city' => 'Malang', 'district' => 'Lowokwaru', 'address' => 'Jl. Soekarno Hatta No. 12',
            'has_physical_store' => true, 'outlets' => 1, 'employees' => 2, 'revenue_range' => '5_10',
            'avg_monthly_transactions' => 240, 'avg_order_value' => 35_000, 'monthly_customers' => 180,
            'sales_channels' => ['store', 'whatsapp'], 'digital_tools' => ['social_media'], 'telkomsel_products' => ['mytelkomsel'],
            'onboarded_at' => $joined, 'goals_set_at' => $joined,
        ]);
        $b->goals()->createMany([
            ['goal_key' => 'more_customers', 'metric' => 'new_customers', 'baseline_value' => 30, 'target_value' => 45, 'target_period_days' => 60],
            ['goal_key' => 'digital_adoption', 'metric' => null],
        ]);

        [$base] = $this->ledger($b, [[
            'period' => $this->month(1), 'is_baseline' => true, 'revenue' => 8_500_000, 'new_customers' => 30, 'total_customers' => 180,
            'conversion_rate' => 1.5, 'repeat_rate' => 18, 'marketing_spend' => 0, 'leads' => 200, 'digital_txn_share' => 15, 'source' => 'self_reported',
        ]]);

        $score = $this->diagnose($b, [
            'acq_new_customers' => '20_50', 'acq_channels' => ['store', 'recommendation'], 'acq_advertising' => 'never', 'acq_budget' => 'zero',
            'conv_rate' => '2_3', 'conv_tracking' => 'chat', 'ret_repeat' => '10_20', 'ret_database' => 'contacts', 'ret_engagement' => 'sometimes',
            'prod_digital_txn' => 'lt30', 'prod_sales_system' => 'book', 'prod_manual' => 'most', 'dp_presence' => 'wa', 'dm_ads_history' => 'never',
            'dm_measure' => 'no', 'fin_records' => 'notes', 'fin_margin' => 'estimate', 'inv_tracking' => 'manual', 'inv_stockout' => 'sometimes',
            'gr_outlet' => 'no', 'gr_new_market' => 'online',
        ], $base, $joined->copy()->addHour());
        $this->plan($b, $score, 0, $joined->copy()->addDays(1));
        $this->activate($b, 'kuncie', 1);

        // Manual catalogue & a handful of records ("mostly manual").
        foreach ([
            ['Dessert Box Coklat', 'DSB-001', 14_000, 30_000, 12, 5], ['Dessert Box Tiramisu', 'DSB-002', 15_000, 32_000, 3, 5],
            ['Pudding Mangga', 'PDG-001', 6_000, 12_000, 20, 8], ['Brownies Panggang', 'BRW-001', 25_000, 55_000, 6, 3],
        ] as [$name, $sku, $cost, $price, $stock, $min]) {
            $p = $b->products()->create(['name' => $name, 'sku' => $sku, 'category' => 'Dessert', 'purchase_price' => $cost, 'selling_price' => $price, 'stock' => $stock, 'min_stock' => $min, 'supplier' => 'Produksi sendiri', 'unit' => 'pcs']);
            $p->movements()->create(['business_id' => $b->id, 'type' => 'initial', 'quantity' => $stock, 'unit_cost' => $cost, 'moved_on' => $joined->toDateString(), 'source' => 'manual', 'note' => 'Stok awal']);
        }
        foreach (range(1, 6) as $i) {
            $b->financialEntries()->create(['type' => 'income', 'entry_date' => $this->today->copy()->subDays(7 - $i), 'category' => 'product_sales', 'amount' => 240_000 + 35_000 * ($i % 3), 'description' => 'Penjualan harian', 'payment_method' => $i % 2 ? 'cash' : 'transfer', 'source' => 'manual']);
        }
        $b->financialEntries()->create(['type' => 'expense', 'entry_date' => $this->today->copy()->subDays(4), 'category' => 'raw_material', 'amount' => 650_000, 'description' => 'Belanja bahan (coklat, susu, gula)', 'payment_method' => 'cash', 'source' => 'manual']);

        $this->passport->refresh($b->fresh());
        $this->notifications->push($b, 'mission', 'Misi minggu 1 Anda sudah siap', 'Tentukan 3 segmen pelanggan teratas Anda.', 'Mulai misi', route('missions.index'));
        $this->notifications->push($b, 'opportunity', 'Upgrade ke Silver untuk aktivasi MyAds', 'Bronze dapat melihat rekomendasi; aktivasi kampanye tersedia di Silver.', 'Bandingkan tier', route('rewards.tiers'));

        return $user;
    }

    /** Demo B — Silver, the core closed loop (acquisition → retention). */
    private function chacha(): User
    {
        $user = $this->user('chacha', '081234560002');
        $joined = $this->today->copy()->subDays(10);
        $b = $this->business($user, 'chacha', [
            'category' => 'Food & Beverage', 'sub_category' => 'Kafe & Kopi', 'start_year' => 2021,
            'description' => 'Kedai kopi susu & sarapan dekat perkantoran, ramai di jam pagi dan makan siang.',
            'province' => 'Jawa Timur', 'city' => 'Surabaya', 'district' => 'Wonokromo', 'address' => 'Jl. Raya Darmo No. 88',
            'has_physical_store' => true, 'outlets' => 1, 'employees' => 6, 'revenue_range' => '10_25',
            'avg_monthly_transactions' => 1_100, 'avg_order_value' => 28_000, 'monthly_customers' => 500,
            'sales_channels' => ['store', 'whatsapp', 'instagram', 'marketplace'], 'instagram_url' => 'https://instagram.com/chachacoffee.demo', 'marketplace_url' => 'https://gofood.co.id/demo/chacha-coffee',
            'digital_tools' => ['social_media', 'marketplace', 'qris', 'pos', 'customer_db'], 'telkomsel_products' => ['mytelkomsel', 'digipos'],
            'onboarded_at' => $joined, 'goals_set_at' => $joined,
        ]);
        $b->goals()->createMany([
            ['goal_key' => 'increase_revenue', 'metric' => 'revenue', 'baseline_value' => 20_000_000, 'target_value' => 25_000_000, 'target_period_days' => 60, 'target_date' => $joined->copy()->addDays(60)],
            ['goal_key' => 'more_customers', 'metric' => 'new_customers', 'baseline_value' => 120, 'target_value' => 160, 'target_period_days' => 30],
            ['goal_key' => 'repeat_customers', 'metric' => 'repeat_rate', 'baseline_value' => 22, 'target_value' => 30, 'target_period_days' => 90],
        ]);

        $rows = $this->ledger($b, [
            ['period' => $this->month(3), 'revenue' => 17_200_000, 'new_customers' => 98, 'total_customers' => 430, 'conversion_rate' => 2.8, 'repeat_rate' => 23.5, 'marketing_spend' => 1_500_000, 'leads' => 360, 'digital_txn_share' => 45, 'source' => 'imported', 'note' => 'Riwayat sebelum bergabung (imported)'],
            ['period' => $this->month(2), 'revenue' => 18_500_000, 'new_customers' => 110, 'total_customers' => 470, 'conversion_rate' => 2.9, 'repeat_rate' => 22.5, 'marketing_spend' => 1_800_000, 'leads' => 380, 'digital_txn_share' => 50, 'source' => 'imported', 'note' => 'Riwayat sebelum bergabung (imported)'],
            ['period' => $this->month(1), 'is_baseline' => true, 'revenue' => 20_000_000, 'new_customers' => 120, 'total_customers' => 500, 'conversion_rate' => 3.0, 'repeat_rate' => 22, 'marketing_spend' => 2_000_000, 'leads' => 400, 'digital_txn_share' => 55, 'source' => 'self_reported', 'note' => 'Baseline dikonfirmasi saat Growth Diagnostic'],
        ]);
        $base = $rows[2];
        $base->forceFill(['created_at' => $joined])->save();

        $score = $this->diagnose($b, [
            'acq_new_customers' => '100_200', 'acq_channels' => ['store', 'recommendation', 'whatsapp'], 'acq_advertising' => 'boost', 'acq_budget' => 'lt500',
            'conv_rate' => '2_3', 'conv_tracking' => 'sheet', 'ret_repeat' => '20_30', 'ret_database' => 'sheet', 'ret_engagement' => 'sometimes',
            'prod_digital_txn' => 'gt70', 'prod_sales_system' => 'pos', 'prod_manual' => 'little', 'dp_presence' => 'multi', 'dm_ads_history' => 'once',
            'dm_measure' => 'sales', 'fin_records' => 'app', 'fin_margin' => 'main', 'inv_tracking' => 'app', 'inv_stockout' => 'rarely',
            'gr_outlet' => '12m', 'gr_new_market' => 'online',
        ], $base, $joined->copy()->addHour());

        $this->activate($b, 'kuncie', 3, $joined->copy()->addDays(2));
        $this->plan($b, $score, 1, $joined->copy()->addDays(1));
        $this->activate($b, 'myads', 1); // account connected; identity verification pending (step 2)
        $b->missions()->where('week', 1)->first()?->submissions()->first()?->update(['kpi_value' => 3, 'notes' => 'Segmen: pekerja kantor radius 2 km, mahasiswa, keluarga akhir pekan.', 'problem' => 'Belum tahu cara menargetkan iklan ke radius tertentu.']);

        $this->passport->refresh($b->fresh());
        $this->notifications->push($b, 'mission', 'Misi minggu 2 Anda sudah siap', 'Luncurkan kampanye tertarget pertama Anda dengan MyAds.', 'Mulai misi', route('missions.index'));
        $this->notifications->push($b, 'warning', 'Aktivasi MyAds tertunda di Identity Verification', 'Selesaikan verifikasi identitas untuk meluncurkan kampanye minggu 2.', 'Lanjutkan aktivasi', route('solutions.activation'));
        $this->notifications->push($b, 'ai', 'Kami menemukan bottleneck pertumbuhan Anda', 'Bottleneck utama: Customer Acquisition. Lihat rekomendasi AI.', 'Lihat rekomendasi', route('assistant'));

        return $user;
    }

    /** Demo C — Silver, strong sales, inventory & cost-control bottleneck. */
    private function sari(): User
    {
        $user = $this->user('sari', '081234560003');
        $joined = $this->month(3)->copy()->addDays(1);
        $b = $this->business($user, 'sari', [
            'category' => 'Food & Beverage', 'sub_category' => 'Bakery & Kue', 'start_year' => 2019,
            'description' => 'Bakery premium dengan kue potong, roti, dan pesanan ulang tahun. Penjualan kuat, biaya bahan meningkat.',
            'province' => 'Jawa Timur', 'city' => 'Surabaya', 'district' => 'Rungkut', 'address' => 'Jl. Rungkut Madya No. 21',
            'has_physical_store' => true, 'outlets' => 1, 'employees' => 6, 'revenue_range' => '25_50',
            'avg_monthly_transactions' => 1_450, 'avg_order_value' => 22_000, 'monthly_customers' => 950,
            'sales_channels' => ['store', 'whatsapp', 'instagram', 'marketplace'], 'instagram_url' => 'https://instagram.com/saribakery.demo',
            'digital_tools' => ['social_media', 'marketplace', 'qris', 'pos', 'digital_ads'], 'telkomsel_products' => ['mytelkomsel', 'kuncie'],
            'onboarded_at' => $joined, 'goals_set_at' => $joined,
        ]);
        $b->goals()->createMany([
            ['goal_key' => 'increase_revenue', 'metric' => 'revenue', 'baseline_value' => 29_000_000, 'target_value' => 36_000_000, 'target_period_days' => 90],
            ['goal_key' => 'improve_productivity', 'metric' => null],
        ]);

        // Catalogue: [name, sku, current unit cost, price, base units/day, current stock, min stock]
        $catalog = [
            ['Tiramisu Cake', 'TIRA-001', 18_000, 35_000, 4, 18, 5], ['Matcha Roll Cake', 'MTCH-002', 21_000, 38_000, 2, 4, 6],
            ['Sugar Bite Cookies', 'SGR-003', 9_000, 18_000, 7, 60, 15], ['Croissant Butter', 'CRS-004', 11_000, 20_000, 6, 0, 10],
            ['Roti Sobek Keju', 'RSK-005', 9_000, 16_000, 5, 45, 10], ['Brownies Fudge', 'BRW-006', 17_000, 32_000, 2.5, 30, 5],
            ['Cheese Tart', 'CHT-007', 7_000, 12_000, 6, 0, 12], ['Banana Bread', 'BNB-008', 14_000, 28_000, 0.4, 30, 5],
            ['Red Velvet Slice', 'RVS-009', 18_000, 33_000, 1.5, 20, 4], ['Donat Gula', 'DNT-010', 4_000, 7_000, 11, 120, 20],
            ['Lapis Legit Premium', 'LPL-011', 95_000, 160_000, 0.1, 8, 2], ['Pandan Chiffon', 'PDC-012', 25_000, 45_000, 1, 14, 3],
        ];
        // Unit cost multiplier by month: raw material prices rising.
        $costIndex = fn (Carbon $d) => match (true) {
            $d->lt($this->month(2)) => 0.86,
            $d->lt($this->month(1)) => 0.93,
            default => 1.0,
        };

        $start = $this->month(3);
        $days = (int) $start->diffInDays($this->today); // through yesterday
        foreach ($catalog as $i => [$name, $sku, $cost, $price, $daily, $stock, $min]) {
            $p = $b->products()->create(['name' => $name, 'sku' => $sku, 'category' => in_array($sku, ['SGR-003', 'DNT-010', 'RSK-005', 'CRS-004']) ? 'Roti & Kue Kering' : 'Cake', 'purchase_price' => $cost, 'selling_price' => $price, 'stock' => $stock, 'min_stock' => $min, 'supplier' => $i % 2 ? 'CV Bahan Prima' : 'ABC Supplier', 'unit' => 'pcs']);
            $sold = 0;
            $bought = 0;
            for ($d = 0; $d < $days; $d++) {
                $date = $start->copy()->addDays($d);
                $growth = 1 + 0.09 * floor($start->diffInMonths($date));
                $weekend = $date->isWeekend() ? 1.35 : 1.0;
                $units = (int) round($daily * $growth * $weekend * (1 + 0.25 * $this->wobble($d * 13 + $i)));
                // Stocked-out products stopped selling recently.
                if ($stock === 0 && $d > $days - 4) {
                    $units = 0;
                }
                if ($daily < 1) {
                    $units = ($d + $i) % (int) round(1 / $daily) === 0 ? 1 : 0;
                }
                if ($units <= 0) {
                    continue;
                }
                $unitCost = (int) round($cost * $costIndex($date) / 100) * 100;
                $entry = $b->financialEntries()->create(['type' => 'income', 'entry_date' => $date, 'category' => 'product_sales', 'amount' => $units * $price, 'cogs' => $units * $unitCost, 'product_id' => $p->id, 'description' => 'Penjualan '.$name.' × '.$units, 'payment_method' => ['qris', 'cash', 'qris', 'marketplace'][$d % 4], 'source' => 'inventory']);
                $p->movements()->create(['business_id' => $b->id, 'type' => 'sale', 'quantity' => -$units, 'unit_cost' => $unitCost, 'unit_price' => $price, 'moved_on' => $date, 'financial_entry_id' => $entry->id, 'source' => 'manual', 'note' => 'Penjualan']);
                $sold += $units;

                // Weekly restock (cash-out) for regular movers.
                if ($d % 7 === 3 && $daily >= 1 && $d < $days - 6) {
                    $qty = (int) round($daily * 7 * $growth);
                    $exp = $b->financialEntries()->create(['type' => 'expense', 'entry_date' => $date, 'category' => 'inventory', 'amount' => $qty * $unitCost, 'product_id' => $p->id, 'description' => 'Produksi/restock '.$name.' ('.$qty.' pcs)', 'payment_method' => 'transfer', 'source' => 'inventory']);
                    $p->movements()->create(['business_id' => $b->id, 'type' => 'purchase', 'quantity' => $qty, 'unit_cost' => $unitCost, 'moved_on' => $date, 'financial_entry_id' => $exp->id, 'source' => 'manual', 'note' => 'Restock dari '.$p->supplier]);
                    $bought += $qty;
                }
            }
            $p->movements()->create(['business_id' => $b->id, 'type' => 'initial', 'quantity' => $stock + $sold - $bought, 'unit_cost' => (int) ($cost * 0.86), 'moved_on' => $start->toDateString(), 'source' => 'manual', 'note' => 'Stok awal']);
        }

        foreach ([3, 2, 1, 0] as $ago) {
            $m = $this->month($ago);
            foreach ([['salary', 6_000_000, 25], ['rent', 3_000_000, 1], ['utilities', 850_000 + 50_000 * (3 - $ago), 20], ['marketing', 800_000, 5], ['transportation', 400_000, 15]] as [$cat, $amt, $day]) {
                $date = $m->copy()->addDays($day - 1);
                if ($date->gt($this->today)) {
                    continue;
                }
                $b->financialEntries()->create(['type' => 'expense', 'entry_date' => $date, 'category' => $cat, 'amount' => $amt, 'description' => config("tumbuh.expense_categories.{$cat}"), 'payment_method' => 'transfer', 'source' => 'manual']);
            }
        }

        // Ledger: baseline (self-reported) + three months computed from Growth Finance.
        $rows = [['period' => $this->month(4), 'is_baseline' => true, 'revenue' => 29_000_000, 'new_customers' => 160, 'total_customers' => 900, 'conversion_rate' => 4.2, 'repeat_rate' => 33, 'marketing_spend' => 800_000, 'leads' => 520, 'gross_margin' => 54.0, 'digital_txn_share' => 60, 'source' => 'self_reported', 'note' => 'Baseline dikonfirmasi saat Growth Diagnostic']];
        foreach ([3, 2, 1] as $k => $ago) {
            $fm = $this->finance->month($b, $this->month($ago));
            $rows[] = ['period' => $this->month($ago), 'revenue' => $fm['revenue'], 'new_customers' => 168 + 8 * $k, 'total_customers' => 940 + 40 * $k, 'conversion_rate' => 4.3 + 0.1 * $k, 'repeat_rate' => 33 - 0.5 * $k, 'marketing_spend' => $k === 2 ? 1_200_000 : 800_000, 'campaign_reach' => $k === 2 ? 18_400 : 0, 'leads' => 540 + 20 * $k, 'gross_margin' => $fm['gross_margin'], 'digital_txn_share' => 70 + 5 * $k, 'source' => 'integrated_demo',
                'contributions' => [['label' => 'Growth Finance records', 'detail' => num($fm['entries']).' transaksi tercatat', 'source' => 'integrated_demo']], 'note' => 'Dihitung dari Growth Finance'];
        }
        $rows = $this->ledger($b, $rows);
        $rows[0]->forceFill(['created_at' => $joined])->save();

        $score = $this->diagnose($b, [
            'acq_new_customers' => '100_200', 'acq_channels' => ['instagram', 'store', 'recommendation', 'marketplace', 'whatsapp'], 'acq_advertising' => 'regular', 'acq_budget' => '500_2m',
            'conv_rate' => '4_5', 'conv_tracking' => 'sheet', 'ret_repeat' => '30_45', 'ret_database' => 'sheet', 'ret_engagement' => 'monthly',
            'prod_digital_txn' => '30_70', 'prod_sales_system' => 'pos', 'prod_manual' => 'half', 'dp_presence' => 'social', 'dm_ads_history' => 'regular',
            'dm_measure' => 'sales', 'fin_records' => 'monthly', 'fin_margin' => 'estimate', 'inv_tracking' => 'manual', 'inv_stockout' => 'often',
            'gr_outlet' => 'consider', 'gr_new_market' => 'city',
        ], $rows[0], $joined->copy()->addHour());

        $b->campaigns()->create(['name' => 'Promo Ulang Tahun Toko', 'objective' => 'acquisition', 'audience' => 'Keluarga radius 3 km', 'radius_km' => 3, 'budget' => 400_000, 'status' => 'completed', 'reach' => 18_400, 'clicks' => 920, 'leads' => 140, 'conversions' => 38, 'spend' => 400_000, 'attributed_revenue' => 1_824_000, 'launched_at' => $this->month(1)->addDays(5)]);
        $this->activate($b, 'myads', 8, $this->month(1));
        $this->activate($b, 'umkm_plus', 2);

        $recalc = $this->scores->recalculate($b->fresh(), 'review');
        $this->backdate($recalc, $this->month(0)->addDays(1));
        $this->interpolateScores($b);
        $this->plan($b, $recalc, 1, $this->today->copy()->subDays(6));

        $this->passport->refresh($b->fresh());
        $this->rewards->evaluate($b->fresh());
        $this->notifications->push($b, 'warning', 'Matcha Roll Cake hampir habis', 'Sisa 4 pcs — diperkirakan habis dalam 1 hari.', 'Restock', route('inventory.index'));
        $this->notifications->push($b, 'ai', 'Gross margin turun bulan ini', 'Omzet naik, tetapi biaya bahan baku (COGS) naik lebih cepat. Lihat cost driver Anda.', 'Lihat laporan', route('finance.report'));

        return $user;
    }

    /** Demo D — Gold, scaling, expansion readiness & DCE pathway. */
    private function bima(): User
    {
        $user = $this->user('bima', '081234560004');
        $joined = $this->month(7)->copy()->addDays(3);
        $b = $this->business($user, 'bima', [
            'category' => 'Fashion', 'sub_category' => 'Batik & Wastra', 'start_year' => 2017,
            'description' => 'Brand batik modern dengan 2 outlet dan penjualan online nasional. Fokus berikutnya: ekspansi outlet ketiga.',
            'province' => 'Jawa Timur', 'city' => 'Surabaya', 'district' => 'Genteng', 'address' => 'Jl. Tunjungan No. 45',
            'has_physical_store' => true, 'outlets' => 2, 'employees' => 18, 'revenue_range' => '100p',
            'avg_monthly_transactions' => 520, 'avg_order_value' => 230_000, 'monthly_customers' => 2_400,
            'sales_channels' => ['store', 'whatsapp', 'instagram', 'tiktok', 'website', 'marketplace', 'reseller'], 'instagram_url' => 'https://instagram.com/nusantarafashion.demo', 'marketplace_url' => 'https://tokopedia.com/demo/nusantara-fashion',
            'digital_tools' => ['social_media', 'marketplace', 'qris', 'pos', 'digital_ads', 'customer_db', 'accounting', 'crm', 'telkomsel_tools'],
            'telkomsel_products' => ['mytelkomsel', 'umkm_plus', 'myads', 'digipos', 'soundbox', 'kuncie'],
            'onboarded_at' => $joined, 'goals_set_at' => $joined,
        ]);
        $b->goals()->createMany([
            ['goal_key' => 'increase_revenue', 'metric' => 'revenue', 'baseline_value' => 85_000_000, 'target_value' => 125_000_000, 'target_period_days' => 90],
            ['goal_key' => 'new_market', 'metric' => null],
            ['goal_key' => 'repeat_customers', 'metric' => 'repeat_rate', 'baseline_value' => 34, 'target_value' => 40, 'target_period_days' => 90],
        ]);

        $series = [
            [8, 85, 210, 3.2, 34.0, 3.5, 0, 48.0], [7, 89, 225, 3.4, 35.0, 4.0, 42_000, 48.2], [6, 94, 240, 3.7, 36.0, 4.5, 55_000, 48.5],
            [5, 99, 255, 3.9, 37.5, 5.0, 61_000, 48.6], [4, 105, 270, 4.2, 38.5, 5.5, 68_000, 49.0], [3, 112, 285, 4.4, 39.5, 6.0, 74_000, 49.3],
            [2, 118, 300, 4.6, 40.5, 6.0, 81_000, 49.5], [1, 124, 318, 4.8, 41.0, 6.5, 88_000, 49.6],
        ];
        $rows = [];
        foreach ($series as $k => [$ago, $rev, $new, $conv, $rep, $spend, $reach, $margin]) {
            $rows[] = ['period' => $this->month($ago), 'is_baseline' => $k === 0, 'revenue' => $rev * 1_000_000, 'new_customers' => $new, 'total_customers' => 1_800 + 120 * $k, 'conversion_rate' => $conv, 'repeat_rate' => $rep, 'marketing_spend' => (int) ($spend * 1_000_000), 'campaign_reach' => $reach, 'leads' => (int) ($new / ($conv / 100) / 6), 'gross_margin' => $margin, 'digital_txn_share' => min(95, 72 + 3 * $k), 'source' => $k === 0 ? 'self_reported' : 'integrated_demo',
                'contributions' => $k === 0 ? null : [['label' => 'Customer campaign', 'detail' => 'MyAds + DigiAds', 'source' => 'simulated'], ['label' => 'RCS re-engagement', 'detail' => 'Voucher pelanggan setia', 'source' => 'simulated']], 'note' => $k === 0 ? 'Baseline dikonfirmasi saat Growth Diagnostic' : 'Integrated (Demo)'];

            // Monthly finance entries summing to the ledger revenue.
            $m = $this->month($ago);
            $splits = [0.34, 0.27, 0.22, 0.17];
            foreach ($splits as $j => $share) {
                $amount = (int) round($rev * 1_000_000 * $share);
                $b->financialEntries()->create(['type' => 'income', 'entry_date' => $m->copy()->addDays(6 + 7 * $j), 'category' => 'product_sales', 'amount' => $amount, 'cogs' => (int) round($amount * (1 - $margin / 100)), 'description' => ['Penjualan marketplace', 'Penjualan outlet Tunjungan', 'Penjualan outlet Galaxy', 'Penjualan reseller & website'][$j], 'payment_method' => ['marketplace', 'qris', 'qris', 'transfer'][$j], 'source' => 'integrated_demo']);
            }
            foreach ([['salary', 21_000_000, 25], ['rent', 12_000_000, 1], ['utilities', 2_800_000, 20], ['marketing', (int) ($spend * 1_000_000), 10], ['transportation', 1_900_000, 15]] as [$cat, $amt, $day]) {
                $b->financialEntries()->create(['type' => 'expense', 'entry_date' => $m->copy()->addDays($day - 1), 'category' => $cat, 'amount' => $amt, 'description' => config("tumbuh.expense_categories.{$cat}"), 'payment_method' => 'transfer', 'source' => 'manual']);
            }
        }
        $rows = $this->ledger($b, $rows);
        $rows[0]->forceFill(['created_at' => $joined])->save();

        $score = $this->diagnose($b, [
            'acq_new_customers' => '100_200', 'acq_channels' => ['instagram', 'marketplace', 'recommendation', 'store'], 'acq_advertising' => 'regular', 'acq_budget' => '2m_5m',
            'conv_rate' => '4_5', 'conv_tracking' => 'sheet', 'ret_repeat' => '30_45', 'ret_database' => 'sheet', 'ret_engagement' => 'monthly',
            'prod_digital_txn' => 'gt70', 'prod_sales_system' => 'pos', 'prod_manual' => 'half', 'dp_presence' => 'multi', 'dm_ads_history' => 'regular',
            'dm_measure' => 'sales', 'fin_records' => 'app', 'fin_margin' => 'main', 'inv_tracking' => 'app', 'inv_stockout' => 'rarely',
            'gr_outlet' => 'budgeted', 'gr_new_market' => 'city',
        ], $rows[0], $joined->copy()->addHour());

        foreach ([
            ['Kemeja Batik Parang Modern', 'NF-KMJ-01', 165_000, 349_000, 42, 15], ['Dress Batik Mega Mendung', 'NF-DRS-02', 210_000, 459_000, 28, 10],
            ['Outer Tenun Lurik', 'NF-OTR-03', 180_000, 399_000, 18, 8], ['Kain Batik Tulis Premium', 'NF-KTL-04', 450_000, 950_000, 9, 4],
            ['Scarf Batik Sutra', 'NF-SCF-05', 85_000, 189_000, 35, 12], ['Kemeja Batik Anak', 'NF-KMA-06', 75_000, 169_000, 6, 10],
        ] as [$name, $sku, $cost, $price, $stock, $min]) {
            $p = $b->products()->create(['name' => $name, 'sku' => $sku, 'category' => 'Batik', 'purchase_price' => $cost, 'selling_price' => $price, 'stock' => $stock, 'min_stock' => $min, 'supplier' => 'Pengrajin Mitra Sidoarjo', 'unit' => 'pcs']);
            $p->movements()->create(['business_id' => $b->id, 'type' => 'initial', 'quantity' => $stock, 'unit_cost' => $cost, 'moved_on' => $this->month(1)->toDateString(), 'source' => 'integrated_demo', 'note' => 'Sinkronisasi stok (Demo)']);
        }

        foreach ([[6, 'Lebaran Collection Launch', 1_500_000, 3.4], [3, 'Back to Office Batik', 1_800_000, 3.1], [1, 'Loyal Customer Week (RCS)', 900_000, 4.2]] as [$ago, $name, $budget, $roas]) {
            $reach = (int) ($budget * 0.049);
            $b->campaigns()->create(['name' => $name, 'objective' => str_contains($name, 'RCS') ? 'retention' : 'acquisition', 'audience' => 'Profesional muda & keluarga Surabaya', 'radius_km' => 5, 'budget' => $budget, 'status' => 'completed', 'reach' => $reach, 'clicks' => (int) ($reach * 0.06), 'leads' => (int) ($reach * 0.012), 'conversions' => (int) ($reach * 0.0035), 'spend' => $budget, 'attributed_revenue' => (int) ($budget * $roas), 'launched_at' => $this->month($ago)->addDays(4)]);
        }

        foreach (['kuncie', 'umkm_plus', 'myads', 'digipos', 'soundbox'] as $key) {
            $this->activate($b, $key, 99, $joined);
        }
        $this->activate($b, 'digiads', 99, $this->month(5));
        $this->activate($b, 'rcs', 99, $this->month(2));
        $this->activate($b, 'msight', 1);

        $recalc = $this->scores->recalculate($b->fresh(), 'review');
        $this->backdate($recalc, $this->month(0)->addDays(1));
        $this->interpolateScores($b);
        $this->plan($b, $recalc, 0, $this->today->copy()->subDays(3));

        $b->assessments()->create(['kind' => 'readiness', 'inputs' => ['capacity' => 'high', 'budget' => 150_000_000, 'target_area' => 'Surabaya Barat'], 'score' => null, 'result' => []]);
        $b->dceApplication()->create(['stage' => 'eligibility', 'eligibility_score' => 0]);
        $b->achievements()->create(['key' => 'score_up_5', 'title' => 'Score Climber', 'description' => 'Growth Score naik ≥ 5 poin dalam satu siklus', 'icon' => 'trending_up', 'earned_at' => $this->month(3)]);

        $this->passport->refresh($b->fresh());
        $this->rewards->evaluate($b->fresh());
        $this->notifications->push($b, 'opportunity', 'Bisnis Anda mungkin siap untuk DCE', 'Growth Score dan pertumbuhan omzet memenuhi indikator awal. Kelayakan tetap melalui seleksi.', 'Cek kelayakan DCE', route('scale.dce'));
        $this->notifications->push($b, 'performance', 'Conversion naik 4,3% bulan ini', 'Conversion 4,6% → 4,8% setelah kampanye RCS.', 'Lihat analytics', route('analytics.index'));

        return $user;
    }

    /** Peer cohort for a credible leaderboard (no login, illustrative names). */
    public function peers(): void
    {
        Business::where('is_peer', true)->delete();
        $names = [
            ['Warung Bu Tini', 'Food & Beverage', 'Surabaya'], ['Batik Sekar Arum', 'Fashion', 'Sidoarjo'], ['Kopi Kenangan Kampung', 'Food & Beverage', 'Malang'],
            ['Glow Skin Lab', 'Beauty', 'Surabaya'], ['Toko Kelontong Makmur', 'Retail', 'Gresik'], ['Laundry Kilat 24', 'Services', 'Surabaya'],
            ['Keripik Tempe Sanan', 'Agriculture', 'Malang'], ['Rajut Nusantara', 'Creative', 'Bandung'], ['Bakso Pak Kumis', 'Food & Beverage', 'Surabaya'],
            ['Madu Hutan Asli', 'Health', 'Malang'], ['Sambal Bu Rudy KW', 'Food & Beverage', 'Surabaya'], ['Hijab Ayu Collection', 'Fashion', 'Surabaya'],
            ['Bengkel Motor Jaya', 'Services', 'Sidoarjo'], ['Les Privat Cerdas', 'Education', 'Malang'], ['Tahu Crispy Mama', 'Food & Beverage', 'Gresik'],
            ['Anyaman Bambu Lestari', 'Creative', 'Yogyakarta'], ['Jamu Gendong Modern', 'Health', 'Solo'], ['Seblak Juara', 'Food & Beverage', 'Bandung'],
            ['Kue Lumpur Ibu Ani', 'Food & Beverage', 'Surabaya'], ['Sepatu Kulit Garut', 'Fashion', 'Bandung'], ['Florist Melati', 'Retail', 'Surabaya'],
            ['Es Teh Solo Segar', 'Food & Beverage', 'Solo'], ['Kerajinan Kayu Jati', 'Creative', 'Semarang'], ['Barbershop Gentle', 'Beauty', 'Surabaya'],
            ['Dimsum Mentai Kita', 'Food & Beverage', 'Malang'], ['Tas Tenun Sumba', 'Fashion', 'Denpasar'], ['Ayam Geprek Mantap', 'Food & Beverage', 'Surabaya'],
            ['Pisang Nugget Hits', 'Food & Beverage', 'Sidoarjo'],
        ];
        $period = $this->month(0)->toDateString();
        foreach ($names as $i => [$name, $category, $city]) {
            $b = Business::create(['user_id' => null, 'name' => $name, 'category' => $category, 'city' => $city, 'tier' => ['bronze', 'silver', 'gold'][$i % 3], 'is_peer' => true, 'onboarded_at' => now()->subMonths(2), 'onboarding_step' => 7]);
            $t = max(5, min(100, 96 - $i * 3.1 + 6 * $this->wobble($i)));
            $c = [
                'target_achievement' => round($t, 1),
                'mission_completion' => round(max(10, min(100, 92 - $i * 2.4 + 8 * $this->wobble($i + 3))), 1),
                'kpi_improvement' => round(max(10, min(100, 88 - $i * 2 + 10 * $this->wobble($i + 7))), 1),
                'data_discipline' => round(max(10, min(100, 90 - $i * 1.8 + 10 * $this->wobble($i + 11))), 1),
            ];
            LeaderboardSnapshot::create(['business_id' => $b->id, 'period' => $period, ...$c, 'score' => $this->leaderboard->score($c)]);
        }
    }
}
