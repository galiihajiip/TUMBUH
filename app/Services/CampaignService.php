<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Campaign;
use App\Models\TelkomselSolution;
use App\Services\Telkomsel\MyAdsAdapter;
use Illuminate\Support\Facades\DB;

/**
 * MyAds campaign simulation. Metrics are deterministic functions of
 * budget, radius, objective and the business's own readiness, and are
 * always labelled Simulated.
 */
class CampaignService
{
    public function __construct(
        private SolutionService $solutions,
        private NotificationService $notifications,
        private RecommendationService $recommendations,
    ) {}

    public function launch(Business $business, array $data): Campaign
    {
        return DB::transaction(function () use ($business, $data) {
            $metrics = $this->simulate($business, $data['objective'], (int) $data['budget'], (int) $data['radius_km']);

            $campaign = $business->campaigns()->create([
                'solution_key' => 'myads',
                'name' => $data['name'],
                'objective' => $data['objective'],
                'audience' => $data['audience'],
                'radius_km' => $data['radius_km'],
                'budget' => $data['budget'],
                'status' => 'running',
                'source' => 'simulated',
                'launched_at' => now(),
                ...$metrics,
            ]);

            // Launching completes MyAds steps 5–8 (create → submit → running → monitor).
            $myads = TelkomselSolution::where('key', 'myads')->first();
            if ($myads) {
                $activation = $this->solutions->activation($business, $myads);
                if ($activation->status !== 'active') {
                    $this->solutions->completeStep($activation, count(app(MyAdsAdapter::class)->steps()));
                }
            }

            $tone = $campaign->conversionRate() < 25 ? 'Jangkauan kuat, tetapi konversi di bawah target.' : 'Konversi kampanye sesuai target.';
            $this->notifications->push($business, 'performance', 'Kampanye "'.$campaign->name.'" berjalan',
                'Jangkauan '.num($campaign->reach).', '.num($campaign->leads).' leads, '.num($campaign->conversions).' konversi (Simulated). '.$tone,
                'Lihat performa', route('campaigns.index'));

            return $campaign;
        });
    }

    /** Deterministic campaign funnel. */
    public function simulate(Business $business, string $objective, int $budget, int $radius): array
    {
        $radiusFactor = 0.8 + 0.1 * min(8, max(1, $radius));
        $learned = $this->recommendations->alreadyUses($business, 'kuncie') ? 1 : 0;
        $offerOptimized = $business->missions()->where('status', 'completed')->where('week', '>=', 3)->exists() ? 1 : 0;

        if ($objective === 'retention') {
            $reach = (int) round(min(max(200, ($business->kpiSnapshots()->max('total_customers') ?: 400) * 1.6), $budget * 0.004));
            $clickRate = 0.18;
            $leadRate = 0.40;
            $convRate = 0.32 + 0.04 * $offerOptimized;
        } else {
            $reach = (int) round($budget * 0.049 * $radiusFactor);
            $clickRate = 0.0506 + 0.004 * $learned;
            $leadRate = 0.145;
            $convRate = 0.233 + 0.05 * $offerOptimized;
        }

        $clicks = (int) round($reach * $clickRate);
        $leads = (int) round($clicks * $leadRate);
        $conversions = (int) round($leads * $convRate);
        $aov = (int) ($business->avg_order_value ?: 35_000);

        return [
            'reach' => $reach,
            'clicks' => $clicks,
            'leads' => $leads,
            'conversions' => $conversions,
            'spend' => $budget,
            'attributed_revenue' => $conversions * $aov * 2, // first order + one return visit
        ];
    }

    /** Optimise a running campaign (applies the review's advice, deterministic uplift). */
    public function optimize(Campaign $campaign): Campaign
    {
        $leads = (int) round($campaign->leads * 1.05);
        $conversions = (int) round($campaign->conversions * 1.18);
        $campaign->update([
            'leads' => $leads,
            'conversions' => $conversions,
            'attributed_revenue' => (int) round($campaign->attributed_revenue * 1.18),
        ]);

        return $campaign;
    }
}
