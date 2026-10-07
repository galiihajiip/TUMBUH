<?php

namespace App\Http\Controllers;

use App\Services\FinancialAnalyticsService;
use App\Services\InventoryAnalyticsService;
use App\Services\KpiService;

/** Business Operating Dashboard — growth, finance, inventory and marketing in one view. */
class OperateController extends Controller
{
    public function index(KpiService $kpi, FinancialAnalyticsService $finance, InventoryAnalyticsService $inventory)
    {
        $business = $this->business();
        $latest = $kpi->latest($business);
        $previous = $kpi->previous($business);
        $campaign = $business->campaigns()->latest('launched_at')->first();
        $hasFinance = $finance->hasData($business);
        $fin = $hasFinance ? $finance->summary($business) : null;
        $insight = $hasFinance ? $finance->insight($business) : null;
        $invInsights = $inventory->insights($business);

        // One AI business insight: finance first, then inventory, then growth.
        $headline = $insight && $insight['tone'] === 'bad' ? $insight['headline'].' '.$insight['action']
            : ($invInsights[0]['text'] ?? ($business->currentRecommendation?->whats_wrong ?? 'Catat transaksi untuk mendapatkan insight bisnis.'));

        return view('operate.index', [
            'business' => $business,
            'score' => $business->latestScore,
            'first' => $business->scoreSnapshots()->first(),
            'latest' => $latest,
            'previous' => $previous,
            'fin' => $fin,
            'inv' => $inventory->stats($business),
            'campaign' => $campaign,
            'headline' => $headline,
            'kpi' => $kpi,
        ]);
    }
}
