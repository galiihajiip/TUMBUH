<?php

namespace Tests\Feature;

use App\Models\Diagnostic;
use App\Services\BottleneckService;
use App\Services\GrowthScoreService;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_score_is_deterministic_and_reproducible(): void
    {
        $business = $this->scenario('chacha');
        $diagnostic = Diagnostic::where('business_id', $business->id)->first();
        $svc = app(GrowthScoreService::class);

        $a = $svc->dimensionsFromDiagnostic($diagnostic);
        $b = $svc->dimensionsFromDiagnostic($diagnostic);
        $this->assertSame($a, $b);
        $this->assertSame(64, $svc->overall($a));
        $this->assertSame('acquisition', $business->latestScore->bottleneck);
    }

    public function test_overall_uses_explicit_weights(): void
    {
        $weights = collect(config('tumbuh.dimensions'))->sum('weight');
        $this->assertEqualsWithDelta(1.0, $weights, 0.0001);
        $this->assertSame(50, app(GrowthScoreService::class)->overall(array_fill_keys(array_keys(config('tumbuh.dimensions')), 50)));
    }

    public function test_bottleneck_is_lowest_with_weight_tiebreak_and_priority(): void
    {
        $svc = app(BottleneckService::class);
        $dims = ['digital_presence' => 60, 'acquisition' => 40, 'conversion' => 70, 'retention' => 40, 'productivity' => 80, 'financial_health' => 75, 'inventory_health' => 90, 'growth_readiness' => 65];
        $r = $svc->analyse($dims);
        $this->assertContains($r['bottleneck'], ['acquisition', 'retention']); // equal weight 0.16
        $this->assertSame('inventory_health', $r['strongest']);
        $this->assertSame('HIGH', $r['priority']);

        $dims['retention'] = 39;
        $this->assertSame('retention', $svc->analyse($dims)['bottleneck']);
        $this->assertSame('MEDIUM', $svc->priority(55));
        $this->assertSame('LOW', $svc->priority(70));
    }

    public function test_recommendation_depends_on_stored_data(): void
    {
        $chacha = $this->scenario('chacha');
        $sari = app(\App\Services\Demo\DemoScenarioBuilder::class)->build('sari')->business;

        $this->assertSame('acquisition', $chacha->currentRecommendation->bottleneck);
        $this->assertStringContainsString('120', $chacha->currentRecommendation->whats_wrong);
        $this->assertContains('myads', $chacha->currentRecommendation->solution_keys);

        $this->assertSame('inventory_health', $sari->currentRecommendation->bottleneck);
        $this->assertContains('growth_inventory', $sari->currentRecommendation->solution_keys);
    }

    public function test_solutions_already_used_are_ranked_last_as_optimisation(): void
    {
        $business = $this->scenario('chacha'); // uses DigiPOS
        $keys = app(RecommendationService::class)->matchSolutions($business, 'productivity');
        $this->assertSame('digipos', end($keys));
    }
}
