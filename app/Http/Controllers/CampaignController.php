<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\TelkomselSolution;
use App\Services\CampaignService;
use App\Services\SolutionService;
use App\Services\Telkomsel\MyAdsAdapter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** MyAds campaign dashboard (Simulated data). */
class CampaignController extends Controller
{
    public function index()
    {
        $business = $this->business();
        $campaigns = $business->campaigns()->latest('launched_at')->get();
        $current = $campaigns->first();
        $target = 30.0; // leads → buyer conversion target (%)

        return view('campaigns.index', [
            'business' => $business,
            'campaigns' => $campaigns,
            'current' => $current,
            'insight' => $current ? ($current->conversionRate() < $target
                ? ['tone' => 'bad', 'text' => 'Jangkauan kuat, tetapi konversi leads → pembeli '.pct($current->conversionRate()).' masih di bawah target '.pct($target, 0).'. Optimalkan penawaran sebelum menambah budget.']
                : ['tone' => 'good', 'text' => 'Konversi '.pct($current->conversionRate()).' sesuai target. Pertimbangkan menaikkan budget secara bertahap.']) : null,
        ]);
    }

    /** MyAds steps 1–4 (account → verification → approval → top up) must be done first. */
    private function requireMyAdsReady()
    {
        $myads = TelkomselSolution::where('key', 'myads')->firstOrFail();
        $activation = app(SolutionService::class)->activation($this->business(), $myads);
        if ($activation->step < MyAdsAdapter::CAMPAIGN_STEP - 1) {
            return redirect()->route('solutions.show', $myads)->withErrors(['solution' => 'Selesaikan aktivasi MyAds (hingga Top Up) sebelum membuat kampanye.']);
        }

        return null;
    }

    public function create()
    {
        if ($redirect = $this->requireMyAdsReady()) {
            return $redirect;
        }
        $business = $this->business();

        return view('campaigns.create', [
            'business' => $business,
            'focus' => $business->activePlan?->bottleneck,
        ]);
    }

    public function store(Request $request, CampaignService $campaigns)
    {
        if ($redirect = $this->requireMyAdsReady()) {
            return $redirect;
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'objective' => ['required', Rule::in(['acquisition', 'retention', 'conversion'])],
            'audience' => ['required', 'string', 'max:160'],
            'radius_km' => ['required', 'integer', 'min:1', 'max:10'],
            'budget' => ['required', 'integer', 'min:100000', 'max:50000000'],
        ], ['budget.min' => 'Budget minimal Rp100.000.']);

        $campaigns->launch($this->business(), $data);

        return redirect()->route('campaigns.index')->with('success', 'Kampanye diluncurkan (Demo Simulation). Hasil simulasi ditampilkan di bawah.');
    }

    public function optimize(Campaign $campaign, CampaignService $campaigns)
    {
        $this->authorizeOwn($campaign);
        $campaigns->optimize($campaign);

        return back()->with('success', 'Penawaran & audiens dioptimalkan — konversi simulasi diperbarui.');
    }
}
