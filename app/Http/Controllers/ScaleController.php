<?php

namespace App\Http\Controllers;

use App\Models\DceApplication;
use App\Services\ExpansionReadinessService;
use App\Services\PassportService;
use App\Services\TierService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Scale: Growth Passport, Expansion Readiness, SiteSense, MSIGHT, DCE pathway. */
class ScaleController extends Controller
{
    public function __construct(private ExpansionReadinessService $expansion, private PassportService $passport) {}

    public function index(TierService $tiers)
    {
        $business = $this->business();
        $this->passport->refresh($business);

        return view('scale.index', [
            'business' => $business->fresh(),
            'readiness' => $this->expansion->readiness($business),
            'dce' => $this->expansion->dceEligibility($business),
            'tiers' => $tiers,
        ]);
    }

    public function passport()
    {
        $business = $this->business();
        $evaluation = $this->passport->evaluate($business);
        $this->passport->refresh($business);

        return view('scale.passport', ['business' => $business->fresh(), 'evaluation' => $evaluation, 'level' => $this->passport->level($business, $evaluation)]);
    }

    public function readiness(TierService $tiers)
    {
        $business = $this->business();

        return view('scale.readiness', [
            'business' => $business,
            'readiness' => $this->expansion->readiness($business),
            'inputs' => $this->expansion->latestInputs($business),
            'advanced' => $tiers->allows($business, 'expansion.advanced'),
        ]);
    }

    public function assess(Request $request)
    {
        $inputs = $request->validate([
            'capacity' => ['required', Rule::in(['low', 'medium', 'high'])],
            'budget' => ['required', 'integer', 'min:0'],
            'target_area' => ['nullable', 'string', 'max:120'],
            'customer_concentration' => ['nullable', 'string', 'max:160'],
        ]);
        $a = $this->expansion->assess($this->business(), $inputs);

        return back()->with('success', 'Expansion Readiness diperbarui: '.$a->score.'/100.');
    }

    public function siteSense()
    {
        $business = $this->business();

        return view('scale.sitesense', [
            'business' => $business,
            'latest' => $business->assessments()->where('kind', 'sitesense')->latest('id')->first(),
            'targets' => ExpansionReadinessService::TARGET_CUSTOMERS,
        ]);
    }

    public function runSiteSense(Request $request)
    {
        $inputs = $request->validate([
            'current_location' => ['required', 'string', 'max:160'],
            'business_type' => ['required', 'string', 'max:80'],
            'target_customer' => ['required', Rule::in(array_keys(ExpansionReadinessService::TARGET_CUSTOMERS))],
            'budget' => ['required', 'integer', 'min:1000000'],
            'preferred_area' => ['nullable', 'string', 'max:80'],
            'store_size' => ['required', 'integer', 'min:10', 'max:2000'],
            'competition_tolerance' => ['required', Rule::in(['low', 'medium', 'high'])],
            'desired_market' => ['nullable', 'string', 'max:120'],
        ]);
        $this->expansion->siteSense($this->business(), $inputs);

        return redirect()->route('scale.sitesense')->with('success', 'Analisis lokasi selesai (Simulated Data).');
    }

    public function mSight()
    {
        $business = $this->business();

        return view('scale.msight', [
            'business' => $business,
            'history' => $business->assessments()->where('kind', 'msight')->latest('id')->limit(5)->get(),
            'questions' => ExpansionReadinessService::MSIGHT_QUESTIONS,
        ]);
    }

    public function runMSight(Request $request)
    {
        $data = $request->validate(['question' => ['required', Rule::in(array_keys(ExpansionReadinessService::MSIGHT_QUESTIONS))]]);
        $this->expansion->mSight($this->business(), $data['question']);

        return redirect()->route('scale.msight')->with('success', 'Demo Insight dibuat dari data simulasi.');
    }

    public function dce(TierService $tiers)
    {
        $business = $this->business();

        return view('scale.dce', [
            'business' => $business,
            'eligibility' => $this->expansion->dceEligibility($business),
            'application' => $business->dceApplication,
            'stages' => DceApplication::STAGES,
            'canApply' => $tiers->allows($business, 'dce.apply'),
            'priority' => $tiers->allows($business, 'dce.priority'),
        ]);
    }

    public function advanceDce(Request $request)
    {
        $business = $this->business();
        $eligibility = $this->expansion->dceEligibility($business);
        abort_unless($eligibility['eligible'] || $business->dceApplication, 422, 'Belum memenuhi indikator kelayakan DCE.');

        $profile = $request->validate([
            'pitch' => ['nullable', 'string', 'max:600'],
            'support_needed' => ['nullable', 'string', 'max:300'],
        ]);
        $app = $this->expansion->advanceDce($business, array_filter($profile) ?: null);

        return back()->with('success', 'Tahap DCE: '.DceApplication::STAGES[$app->stage].($app->stage === 'selection' ? ' — hasil seleksi tidak dijamin.' : '.'));
    }
}
