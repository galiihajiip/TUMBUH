<?php

namespace App\Http\Controllers;

use App\Services\FinancialAnalyticsService;
use App\Services\InventoryAnalyticsService;
use App\Services\KpiService;
use App\Services\TierService;

class AnalyticsController extends Controller
{
    public function index(KpiService $kpi, FinancialAnalyticsService $finance, InventoryAnalyticsService $inventory, TierService $tiers)
    {
        $business = $this->business();

        return view('analytics.index', [
            'business' => $business,
            'kpiRows' => $kpi->kpiRows($business, ['revenue', 'new_customers', 'conversion_rate', 'repeat_rate', 'marketing_spend']),
            'ledger' => $kpi->ledger($business),
            'campaigns' => $business->campaigns()->latest('launched_at')->get(),
            'scores' => $business->scoreSnapshots()->get(),
            'finance' => $finance->hasData($business) ? $finance->series($business) : [],
            'inventory' => $inventory->stats($business),
            'top' => $inventory->topSelling($business, 5),
            'advanced' => $tiers->allows($business, 'analytics.advanced'),
            'kpi' => $kpi,
        ]);
    }
}
