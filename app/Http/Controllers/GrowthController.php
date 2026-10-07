<?php

namespace App\Http\Controllers;

use App\Services\GrowthScoreService;
use App\Services\KpiService;
use App\Services\PerformanceReviewService;
use App\Services\TierService;

/** My Growth: Growth Score, Growth Ledger, Before vs After, AI Performance Review. */
class GrowthController extends Controller
{
    public function __construct(private KpiService $kpi, private GrowthScoreService $scores) {}

    public function index()
    {
        $business = $this->business();

        return view('growth.index', [
            'business' => $business,
            'score' => $business->latestScore,
            'history' => $business->scoreSnapshots()->get(),
            'kpiRows' => $this->kpi->kpiRows($business),
            'diagnostic' => $business->diagnostics()->latest('id')->first(),
        ]);
    }

    public function score()
    {
        $business = $this->business();
        $score = $business->latestScore;
        if (! $score) {
            return redirect()->route('diagnostic.start');
        }

        return view('growth.score', [
            'business' => $business,
            'score' => $score,
            'previous' => $business->scoreSnapshots()->reorder()->orderByDesc('calculated_at')->orderByDesc('id')->skip(1)->first(),
            'latestKpi' => $this->kpi->latest($business),
            'repeatTarget' => $this->kpi->target($business, 'repeat_rate'),
            'recommendation' => $business->currentRecommendation,
        ]);
    }

    public function ledger(TierService $tiers)
    {
        $business = $this->business();
        $ledger = $this->kpi->ledger($business);
        $full = $tiers->allows($business, 'growth_ledger.full');

        return view('growth.ledger', [
            'business' => $business,
            // Bronze sees the basic ledger (last 3 months); Silver+ sees the full history.
            'ledger' => $full ? $ledger : $ledger->slice(-3)->values(),
            'full' => $full,
            'campaigns' => $business->campaigns()->latest('launched_at')->get(),
        ]);
    }

    public function beforeAfter()
    {
        $business = $this->business();

        return view('growth.before-after', [
            'business' => $business,
            'comparison' => $this->kpi->beforeAfter($business),
            'kpi' => $this->kpi,
        ]);
    }

    public function review(PerformanceReviewService $reviews, TierService $tiers)
    {
        $business = $this->business();

        return view('growth.review', [
            'business' => $business,
            'stored' => $business->currentReview,
            'live' => $reviews->analyse($business),
            'score' => $business->latestScore,
            'previous' => $business->scoreSnapshots()->reorder()->orderByDesc('calculated_at')->orderByDesc('id')->skip(1)->first(),
            'full' => $tiers->allows($business, 'performance_review.full'),
            'recommendation' => $business->currentRecommendation,
        ]);
    }
}
