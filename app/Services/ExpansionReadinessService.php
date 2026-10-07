<?php

namespace App\Services;

use App\Models\Business;
use App\Models\DceApplication;
use App\Models\ExpansionAssessment;
use App\Services\Telkomsel\MSightAdapter;
use App\Services\Telkomsel\SiteSenseAdapter;

/**
 * Scale layer: Expansion Readiness, SiteSense location scoring, MSIGHT
 * market insight and DCE eligibility. Deterministic local scoring over
 * the business's own data plus SIMULATED market datasets from adapters.
 */
class ExpansionReadinessService
{
    public const WEIGHTS = [
        'growth_score' => 0.30,
        'revenue_trend' => 0.20,
        'retention' => 0.15,
        'financial' => 0.15,
        'capacity' => 0.10,
        'digital_maturity' => 0.10,
    ];

    public const TARGET_CUSTOMERS = [
        'family' => 'Keluarga', 'office' => 'Pekerja kantoran', 'student' => 'Mahasiswa',
        'young_pro' => 'Profesional muda', 'tourist' => 'Wisatawan',
    ];

    public const MSIGHT_QUESTIONS = [
        'where' => 'Di mana pelanggan potensial saya?',
        'segment' => 'Segmen pelanggan mana yang paling potensial?',
        'when' => 'Kapan permintaan tertinggi di area saya?',
        'opportunity' => 'Pasar mana yang tumbuh paling cepat?',
    ];

    public function __construct(
        private KpiService $kpi,
        private SiteSenseAdapter $siteSense,
        private MSightAdapter $mSight,
    ) {}

    public function latestInputs(Business $business): array
    {
        return $business->assessments()->where('kind', 'readiness')->latest('id')->first()?->inputs ?? [];
    }

    /** @return array{score: int, components: array, verdict: string} */
    public function readiness(Business $business, ?array $inputs = null): array
    {
        $inputs ??= $this->latestInputs($business);
        $score = $business->latestScore;
        $ledger = $this->kpi->ledger($business);
        $latest = $ledger->last();
        $ref = $ledger->count() >= 4 ? $ledger[$ledger->count() - 4] : $ledger->first();
        $revGrowth = $latest && $ref && $ref->revenue > 0 ? ($latest->revenue - $ref->revenue) / $ref->revenue * 100 : 0;

        $capacityMap = ['low' => 40, 'medium' => 70, 'high' => 90];
        $capacity = $capacityMap[$inputs['capacity'] ?? ''] ?? match (true) {
            ($business->employees ?? 0) >= 10 => 80,
            ($business->employees ?? 0) >= 5 => 65,
            default => 45,
        };
        $passportRank = array_search($business->passport_level, array_keys(config('tumbuh.passport_levels')), true) ?: 0;

        $components = [
            'growth_score' => ['label' => 'Growth Score', 'value' => $score?->overall ?? 0],
            'revenue_trend' => ['label' => 'Tren omzet 3 bulan', 'value' => (int) round(max(0, min(100, 50 + $revGrowth * 2.5)))],
            'retention' => ['label' => 'Retensi pelanggan', 'value' => $score?->scoreFor('retention') ?? 0],
            'financial' => ['label' => 'Kesehatan keuangan', 'value' => $score?->scoreFor('financial_health') ?? 0],
            'capacity' => ['label' => 'Kapasitas operasional', 'value' => $capacity],
            'digital_maturity' => ['label' => 'Kematangan digital (Passport)', 'value' => (int) round($passportRank / 5 * 100)],
        ];

        $total = 0;
        foreach (self::WEIGHTS as $k => $w) {
            $total += $components[$k]['value'] * $w;
            $components[$k]['weight'] = $w;
        }

        if (! empty($inputs['budget']) && $inputs['budget'] < 30_000_000) {
            $total -= 5; // thin runway for a new outlet
        }

        $total = (int) round(max(0, min(100, $total)));

        return [
            'score' => $total,
            'components' => $components,
            'revenue_growth' => round($revGrowth, 1),
            'verdict' => match (true) {
                $total >= 80 => 'Expansion Ready',
                $total >= 65 => 'Hampir siap — perkuat komponen terendah',
                default => 'Belum siap — fokus pada pertumbuhan inti',
            },
        ];
    }

    public function assess(Business $business, array $inputs): ExpansionAssessment
    {
        $r = $this->readiness($business, $inputs);

        return $business->assessments()->create(['kind' => 'readiness', 'inputs' => $inputs, 'score' => $r['score'], 'result' => $r]);
    }

    /** SiteSense: rank simulated candidate sites against the criteria. */
    public function siteSense(Business $business, array $inputs): ExpansionAssessment
    {
        $budget = (int) ($inputs['budget'] ?? 60_000_000);
        $tolerance = ['low' => 1.2, 'medium' => 1.0, 'high' => 0.7][$inputs['competition_tolerance'] ?? 'medium'];
        $target = $inputs['target_customer'] ?? 'family';
        $rentCap = $budget / 6; // six months of rent within budget

        $level = fn (int $v) => $v >= 75 ? 'HIGH' : ($v >= 55 ? 'MEDIUM' : 'LOW');

        $ranked = collect($this->siteSense->candidates($business->city ?? 'Surabaya'))->map(function ($c) use ($tolerance, $target, $rentCap, $inputs, $level) {
            $segment = in_array($target, $c['segments'], true) ? 100 : 50;
            $budgetFit = $c['rent'] <= $rentCap ? 100 : (int) round(100 * $rentCap / $c['rent']);
            $competitionScore = (int) round(max(0, min(100, (100 - $c['competition']) * $tolerance + 20)));
            $score = 0.30 * $c['footfall'] + 0.20 * $c['accessibility'] + 0.20 * $competitionScore + 0.20 * $segment + 0.10 * $budgetFit;
            if (! empty($inputs['preferred_area']) && str_contains(strtolower($c['name']), strtolower($inputs['preferred_area']))) {
                $score += 5;
            }
            $score = (int) round(min(100, $score));

            return $c + [
                'score' => $score,
                'labels' => [
                    'Customer Potential' => $level((int) round(($c['footfall'] + $segment) / 2)),
                    'Accessibility' => $level($c['accessibility']),
                    'Competition' => $c['competition'] >= 75 ? 'HIGH' : ($c['competition'] >= 50 ? 'MEDIUM' : 'LOW'),
                    'Market Fit' => $level($segment),
                    'Budget Fit' => $level($budgetFit),
                ],
                'rationale' => array_values(array_filter([
                    $segment === 100 ? 'Segmen '.(self::TARGET_CUSTOMERS[$target] ?? $target).' dominan di area ini.' : null,
                    'Footfall index '.$c['footfall'].'/100, aksesibilitas '.$c['accessibility'].'/100.',
                    $c['rent'] <= $rentCap ? 'Estimasi sewa '.rupiah_short($c['rent']).'/bln masih dalam budget.' : 'Sewa '.rupiah_short($c['rent']).'/bln melebihi 1/6 budget.',
                    $c['competition'] >= 75 ? 'Persaingan tinggi — perlu diferensiasi kuat.' : 'Tingkat persaingan '.strtolower($level(100 - $c['competition'])).'.',
                ])),
            ];
        })->sortByDesc('score')->values()->all();

        return $business->assessments()->create([
            'kind' => 'sitesense',
            'inputs' => $inputs,
            'score' => $ranked[0]['score'] ?? null,
            'result' => ['candidates' => $ranked, 'engine' => 'SiteSense mock adapter — Simulated Data'],
        ]);
    }

    /** MSIGHT: data → insight → decision, from simulated area profiles. */
    public function mSight(Business $business, string $question): ExpansionAssessment
    {
        $areas = collect($this->mSight->areaProfiles($business->city ?? 'Surabaya'));
        $byValue = $areas->sortByDesc(fn ($a) => $a['density'] * $a['spend_index'])->values();
        $top = $byValue->first();
        $fastest = $areas->sortByDesc('growth')->first();

        [$insight, $decision] = match ($question) {
            'segment' => ['Segmen "'.$top['segment'].'" memiliki konsentrasi tertinggi di '.$top['area'].' (density '.$top['density'].'/100).', 'Fokuskan pesan kampanye dan menu pada segmen '.$top['segment'].'.'],
            'when' => ['Permintaan tertinggi di '.$top['area'].' terjadi pada '.$top['peak'].'.', 'Jadwalkan kampanye MyAds dan stok ekstra menjelang '.$top['peak'].'.'],
            'opportunity' => [$fastest['area'].' tumbuh paling cepat ('.signed($fastest['growth'], 1, '%').' YoY, simulasi).', 'Pertimbangkan uji pasar (pop-up / pengiriman) di '.$fastest['area'].' sebelum membuka outlet.'],
            default => ['Segmen pelanggan "'.$top['segment'].'" memiliki konsentrasi tinggi di '.$top['area'].'.', 'Pertimbangkan ekspansi/pemasaran di '.$top['area'].'. Validasi lokasi dengan SiteSense.'],
        };

        return $business->assessments()->create([
            'kind' => 'msight',
            'inputs' => ['question' => $question],
            'score' => null,
            'result' => [
                'question' => self::MSIGHT_QUESTIONS[$question] ?? $question,
                'insight' => $insight,
                'decision' => $decision,
                'areas' => $byValue->all(),
                'engine' => 'MSIGHT mock adapter — Demo Insight / Simulated Data',
            ],
        ]);
    }

    /** DCE eligibility: score, performance, digital maturity, potential. */
    public function dceEligibility(Business $business): array
    {
        $score = $business->latestScore?->overall ?? 0;
        $readiness = $this->readiness($business);
        $passportRank = array_search($business->passport_level, array_keys(config('tumbuh.passport_levels')), true) ?: 0;
        $performance = (int) round(max(0, min(100, 50 + $readiness['revenue_growth'] * 1.5)));
        $maturity = (int) round($passportRank / 5 * 100);

        $total = (int) round(0.40 * $score + 0.25 * $performance + 0.20 * $maturity + 0.15 * $readiness['score']);

        return [
            'score' => $total,
            'eligible' => $total >= 75,
            'components' => [
                ['label' => 'Growth Score', 'value' => $score],
                ['label' => 'Business performance', 'value' => $performance],
                ['label' => 'Digital maturity', 'value' => $maturity],
                ['label' => 'Growth potential', 'value' => $readiness['score']],
            ],
        ];
    }

    public function advanceDce(Business $business, ?array $profile = null): DceApplication
    {
        $eligibility = $this->dceEligibility($business);
        $app = $business->dceApplication ?? $business->dceApplication()->create(['stage' => 'eligibility', 'eligibility_score' => $eligibility['score']]);
        $stages = array_keys(DceApplication::STAGES);
        $idx = array_search($app->stage, $stages, true);

        $next = $stages[min($idx + 1, count($stages) - 1)];
        $app->update([
            'stage' => $next,
            'eligibility_score' => $eligibility['score'],
            'profile' => $profile ?? $app->profile,
            'submitted_at' => $next === 'profile_submitted' ? now() : $app->submitted_at,
        ]);

        return $app;
    }
}
