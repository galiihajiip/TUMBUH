<?php

namespace App\Services;

use App\Models\Business;
use App\Models\KpiSnapshot;
use App\Models\TelkomselSolution;
use App\Services\Telkomsel\MyAdsAdapter;
use Illuminate\Support\Facades\DB;

/**
 * Deterministic demo actions that make cause → effect visible:
 * activity (missions, campaigns, sales) → KPI change → review → score
 * update → next bottleneck → new plan. All outputs are labelled Simulated.
 */
class DemoSimulationService
{
    public function __construct(
        private KpiService $kpi,
        private GrowthScoreService $scores,
        private PerformanceReviewService $review,
        private RecommendationService $recommendations,
        private ActionPlanService $plans,
        private MissionService $missions,
        private CampaignService $campaigns,
        private SolutionService $solutions,
        private InventoryService $inventory,
        private InventoryAnalyticsService $inventoryAnalytics,
        private FinancialAnalyticsService $finance,
        private PassportService $passport,
        private RewardService $rewards,
        private NotificationService $notifications,
    ) {}

    public function completeMission(Business $business): ?string
    {
        $mission = $this->missions->current($business);
        if (! $mission) {
            return null;
        }
        $this->missions->submit($mission, [
            'completion' => 'yes',
            'kpi_value' => $mission->kpi_target,
            'notes' => 'Diselesaikan melalui Demo Mode.',
        ]);

        return 'Misi minggu '.$mission->week.' selesai: '.$mission->title;
    }

    public function launchCampaign(Business $business): string
    {
        $myads = TelkomselSolution::where('key', 'myads')->firstOrFail();
        $activation = $this->solutions->activation($business, $myads);
        if ($activation->step < MyAdsAdapter::CAMPAIGN_STEP - 1) {
            $this->solutions->completeStep($activation, MyAdsAdapter::CAMPAIGN_STEP - 1);
        }

        $focus = $business->activePlan?->bottleneck ?? $business->latestScore?->bottleneck;
        $retention = $focus === 'retention';
        $campaign = $this->campaigns->launch($business, [
            'name' => $retention ? 'Ajak Kembali — Voucher Kunjungan Kedua' : 'Weekend Promo Radius 2 km',
            'objective' => $retention ? 'retention' : 'acquisition',
            'audience' => $retention ? 'Pelanggan tidak aktif 14 hari' : 'Pekerja & keluarga radius 2 km',
            'radius_km' => 2,
            'budget' => 500_000,
        ]);

        // Launching satisfies the "launch campaign" mission if it is current.
        $current = $this->missions->current($business);
        if ($current && in_array($current->solution_key, ['myads', 'rcs'], true) && $current->planItem?->phase === 'Activate') {
            $this->missions->submit($current, ['completion' => 'yes', 'kpi_value' => $campaign->reach, 'notes' => 'Kampanye diluncurkan (Simulated).']);
        }

        return 'Kampanye "'.$campaign->name.'" berjalan — reach '.num($campaign->reach).', '.num($campaign->conversions).' konversi (Simulated).';
    }

    public function recordSale(Business $business): ?string
    {
        $row = $this->inventoryAnalytics->products($business)->filter(fn ($r) => $r['product']->stock >= 3)->sortByDesc('sold')->first();
        if (! $row) {
            return null;
        }
        $this->inventory->recordSale($row['product'], 3, now()->toDateString(), 'qris', 'demo');

        return 'Penjualan 3 × '.$row['product']->name.' tercatat → stok −3, omzet +'.rupiah_short(3 * $row['product']->selling_price).', COGS +'.rupiah_short(3 * $row['product']->purchase_price).'.';
    }

    public function restock(Business $business): ?string
    {
        $row = $this->inventoryAnalytics->products($business)
            ->filter(fn ($r) => $r['product']->stockStatus() !== 'ok' || ($r['days_to_stockout'] !== null && $r['days_to_stockout'] <= 5))
            ->sortBy(fn ($r) => $r['product']->stock)->first();
        if (! $row) {
            return null;
        }
        $qty = max($row['restock_qty'], $row['product']->min_stock * 2);
        $this->inventory->restock($row['product'], $qty, null, now()->toDateString(), 'demo');

        return 'Restock '.$qty.' '.$row['product']->unit.' '.$row['product']->name.' → stok '.($row['product']->stock + $qty).', cash-out '.rupiah_short($qty * $row['product']->purchase_price).'.';
    }

    /**
     * Close the 30-day cycle: turn the cycle's activity into next month's
     * KPI snapshot, then run review → score update → next plan.
     */
    public function closeCycle(Business $business): ?string
    {
        $last = $this->kpi->latest($business);
        if (! $last) {
            return null;
        }

        return DB::transaction(function () use ($business, $last) {
            $since = $last->created_at;
            $period = $last->period->copy()->addMonthNoOverflow()->startOfMonth();
            $plan = $business->activePlan ?? $business->actionPlans()->latest('id')->first();
            $focus = $plan?->bottleneck ?? $business->latestScore?->bottleneck;

            $done = $business->missions()->whereIn('status', ['completed', 'partial'])->where('completed_at', '>', $since)->count();
            $m = min(1, $done / 4);
            $campaigns = $business->campaigns()->where('launched_at', '>', $since)->get();
            $conv = (int) $campaigns->sum('conversions');
            $learned = $this->recommendations->alreadyUses($business, 'kuncie');

            $new = (int) round($last->new_customers + $conv * 0.6 + $last->new_customers * 0.04 * $m);
            $conversion = round($last->conversion_rate + ($conv > 0 ? 0.4 : 0) + (($focus === 'conversion' || $learned) ? 0.7 : 0.2) * $m, 1);
            $newGrowth = $last->new_customers > 0 ? ($new - $last->new_customers) / $last->new_customers : 0;
            $repeat = $focus === 'retention'
                ? round($last->repeat_rate + 5 * $m + ($campaigns->where('objective', 'retention')->isNotEmpty() ? 1 : 0), 1)
                : round($last->repeat_rate - ($newGrowth > 0.15 ? 1 : 0), 1);
            $convGrowth = $last->conversion_rate > 0 ? ($conversion - $last->conversion_rate) / $last->conversion_rate : 0;

            $financeMonth = $this->finance->month($business, $period);
            $revenue = $financeMonth['revenue'] > $last->revenue * 0.5
                ? $financeMonth['revenue']
                : (int) (round($last->revenue * (1 + 0.6 * $newGrowth + 0.15 * $convGrowth + ($repeat - $last->repeat_rate) / 100 * 0.5) / 100_000) * 100_000);

            // Operational tools lift digital recording only in the cycle they were activated.
            $operational = $business->activations()->where('status', 'active')->where('activated_at', '>', $since)->whereHas('solution', fn ($q) => $q->whereIn('key', ['digipos', 'soundbox']))->exists();

            $contributions = [];
            if ($campaigns->isNotEmpty()) {
                $contributions[] = ['label' => 'Customer campaign', 'detail' => num($campaigns->sum('reach')).' reach · '.num($conv).' konversi via MyAds', 'source' => 'simulated'];
            }
            if ($learned || $business->missions()->where('solution_key', 'kuncie')->whereIn('status', ['completed', 'partial'])->exists()) {
                $contributions[] = ['label' => 'Kuncie learning', 'detail' => 'Modul diselesaikan & diterapkan', 'source' => 'self_reported'];
            }
            if ($business->missions()->where('week', '>=', 3)->whereIn('status', ['completed', 'partial'])->where('completed_at', '>', $since)->exists()) {
                $contributions[] = ['label' => 'Marketing optimization', 'detail' => 'Audiens & pesan dioptimalkan', 'source' => 'self_reported'];
            }
            if ($financeMonth['entries'] > 0) {
                $contributions[] = ['label' => 'Growth Finance records', 'detail' => num($financeMonth['entries']).' transaksi tercatat', 'source' => 'integrated_demo'];
            }

            $snapshot = KpiSnapshot::updateOrCreate(
                ['business_id' => $business->id, 'period' => $period->toDateString()],
                [
                    'is_baseline' => false,
                    'revenue' => $revenue,
                    'new_customers' => $new,
                    'total_customers' => $last->total_customers + (int) round($new * 0.5),
                    'conversion_rate' => $conversion,
                    'repeat_rate' => $repeat,
                    'marketing_spend' => $campaigns->isNotEmpty() ? (int) ($campaigns->sum('spend') + $last->marketing_spend * 0.5) : $last->marketing_spend,
                    'campaign_reach' => (int) $campaigns->sum('reach'),
                    'leads' => (int) ($campaigns->sum('leads') ?: $last->leads),
                    'gross_margin' => $financeMonth['gross_margin'] ?? $last->gross_margin,
                    'digital_txn_share' => min(100, $last->digital_txn_share + 10 * $m + ($operational ? 10 : 0)),
                    'source' => $campaigns->isNotEmpty() ? 'integrated_demo' : 'self_reported',
                    'contributions' => $contributions,
                    'note' => 'Penutupan siklus 30 hari (Demo Simulation)',
                ],
            );

            $before = $business->latestScore?->overall;
            $this->review->run($business->fresh(), 'cycle_close');
            $business->refresh();
            $score = $business->latestScore;

            $rec = $business->currentRecommendation;
            if ($rec) {
                $business->increment('growth_cycle');
                $this->plans->generate($business->fresh(), $rec);
            }

            [$oldLevel, $newLevel] = $this->passport->refresh($business->fresh());
            $this->rewards->evaluate($business->fresh());
            if ($score && $before !== null && $score->overall - $before >= 5) {
                $business->achievements()->firstOrCreate(['key' => 'score_up_5'], ['title' => 'Score Climber', 'description' => 'Growth Score naik ≥ 5 poin dalam satu siklus', 'icon' => 'trending_up', 'earned_at' => now()]);
            }
            if ($snapshot->revenue > $last->revenue && $last->revenue > 0) {
                $this->notifications->push($business, 'performance', 'Omzet naik '.pct(($snapshot->revenue - $last->revenue) / $last->revenue * 100).' bulan ini',
                    rupiah_short($last->revenue).' → '.rupiah_short($snapshot->revenue).'. Lihat Before vs After.', 'Before vs After', route('growth.before-after'));
            }
            if ($rec && $rec->bottleneck !== $focus) {
                $this->notifications->push($business, 'ai', 'Kami menemukan bottleneck baru: '.config("tumbuh.dimensions.{$rec->bottleneck}.label", 'Expansion'),
                    $rec->headline, 'Lihat rencana baru', route('plan.show'));
            }
            if ($oldLevel !== $newLevel) {
                $this->notifications->push($business, 'reward', 'Growth Passport naik ke '.config("tumbuh.passport_levels.{$newLevel}"), 'Kriteria performa terpenuhi.', 'Lihat Passport', route('scale.passport'));
            }

            return 'Siklus '.$period->translatedFormat('F Y').' ditutup — Growth Score '.($before ?? '—').' → '.($score?->overall ?? '—').', bottleneck berikutnya: '.config('tumbuh.dimensions.'.($rec?->bottleneck).'.label', 'Expansion').'.';
        });
    }

    public function recalculate(Business $business): ?string
    {
        $before = $business->latestScore?->overall;
        $score = $this->scores->recalculate($business);
        if (! $score) {
            return null;
        }
        $this->passport->refresh($business->fresh());

        return 'Growth Score dihitung ulang: '.$before.' → '.$score->overall.' · bottleneck '.config("tumbuh.dimensions.{$score->bottleneck}.label").'.';
    }

    public function runReview(Business $business): ?string
    {
        $rec = $this->review->run($business);

        return $rec ? 'AI Performance Review diperbarui. Next action: '.str($rec->what_to_do)->limit(90) : null;
    }
}
