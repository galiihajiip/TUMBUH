<?php

namespace App\Http\Controllers;

use App\Services\KpiService;
use App\Services\MissionService;
use App\Services\SolutionService;

class OverviewController extends Controller
{
    public function index(KpiService $kpi, MissionService $missions, SolutionService $solutions)
    {
        $business = $this->business()->load('goals');
        $score = $business->latestScore;
        $first = $business->scoreSnapshots()->first();

        return view('app.overview', [
            'business' => $business,
            'score' => $score,
            'scoreDelta' => $score && $first ? $score->overall - $first->overall : 0,
            'previousScore' => $business->scoreSnapshots()->reorder()->orderByDesc('calculated_at')->orderByDesc('id')->skip(1)->first(),
            'kpiRows' => $kpi->kpiRows($business),
            'recommendation' => $business->currentRecommendation,
            'mission' => $missions->current($business),
            'missions' => $business->planMissions()->get(),
            'activations' => $solutions->activationsFor($business)->filter(fn ($a) => $a->status !== 'not_activated')->take(4),
            'latestKpi' => $kpi->latest($business),
        ]);
    }
}
