<?php

namespace App\Http\Controllers;

use App\Models\TelkomselSolution;
use App\Services\RecommendationService;
use App\Services\SolutionService;
use App\Services\TierService;
use Illuminate\Http\Request;

/** Telkomsel solution orchestration: recommendations, detail, Activation Center. */
class SolutionController extends Controller
{
    public function __construct(private SolutionService $solutions, private RecommendationService $recommendations) {}

    public function index(Request $request)
    {
        $business = $this->business();
        $rec = $business->currentRecommendation;
        $focus = $request->query('focus', $rec?->bottleneck);
        $keys = $focus ? $this->recommendations->matchSolutions($business, $focus) : [];

        return view('solutions.index', [
            'business' => $business,
            'focus' => $focus,
            'recommended' => collect($keys),
            'catalog' => $this->solutions->catalog()->keyBy('key'),
            'activations' => $this->solutions->activationsFor($business)->keyBy(fn ($a) => $a->solution->key),
            'native' => RecommendationService::NATIVE,
            'alreadyUses' => fn ($key) => $this->recommendations->alreadyUses($business, $key),
        ]);
    }

    public function show(TelkomselSolution $solution, TierService $tiers)
    {
        $business = $this->business();
        $rec = $business->currentRecommendation;

        return view('solutions.show', [
            'business' => $business,
            'solution' => $solution,
            'activation' => $this->solutions->activation($business, $solution),
            'steps' => $this->solutions->steps($solution),
            'eligibility' => $this->solutions->eligibility($business, $solution),
            'canActivate' => $this->solutions->canActivate($business, $solution),
            'recommendedFor' => $rec && in_array($solution->key, $rec->solution_keys ?? [], true) ? $rec : null,
            'alreadyUses' => $this->recommendations->alreadyUses($business, $solution->key),
        ]);
    }

    public function activation()
    {
        $business = $this->business();
        $activations = $this->solutions->activationsFor($business);
        $focus = $activations->first(fn ($a) => in_array($a->status, ['activation_required', 'eligibility_check'], true));

        return view('solutions.activation', [
            'business' => $business,
            'activations' => $activations,
            'focus' => $focus,
            'focusSteps' => $focus ? $this->solutions->steps($focus->solution) : [],
            'service' => $this->solutions,
            'recommendation' => $business->currentRecommendation,
        ]);
    }

    public function advance(TelkomselSolution $solution)
    {
        $business = $this->business();
        $route = $this->solutions->advance($business, $solution);
        if ($route) {
            return redirect()->route($route, $route === 'campaigns.create' ? [] : [])->with('success', 'Lanjutkan langkah berikutnya untuk '.$solution->name.'.');
        }

        $activation = $this->solutions->activation($business, $solution)->fresh();
        $steps = $this->solutions->steps($solution);
        $msg = $activation->status === 'active'
            ? $solution->name.' aktif (Demo Simulation).'
            : 'Langkah "'.$steps[$activation->step - 1].'" selesai (simulasi). Berikutnya: '.($steps[$activation->step] ?? '—').'.';

        return back()->with('success', $msg);
    }
}
