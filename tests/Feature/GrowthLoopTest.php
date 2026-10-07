<?php

namespace Tests\Feature;

use App\Services\DemoSimulationService;
use App\Services\ExpansionReadinessService;
use App\Services\KpiService;
use App\Services\LeaderboardService;
use App\Services\PassportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrowthLoopTest extends TestCase
{
    use RefreshDatabase;

    public function test_closed_loop_moves_score_and_next_bottleneck(): void
    {
        $chacha = $this->scenario('chacha');
        $this->assertSame(64, $chacha->latestScore->overall);
        $this->assertSame('productive', $chacha->passport_level);
        $this->assertNull(app(KpiService::class)->beforeAfter($chacha));

        $demo = app(DemoSimulationService::class);
        $this->travel(1)->minutes();
        $demo->launchCampaign($chacha);
        $demo->completeMission($chacha->fresh());
        $demo->completeMission($chacha->fresh());
        $demo->closeCycle($chacha->fresh());

        $chacha->refresh();
        $score = $chacha->latestScore;
        $this->assertSame(74, $score->overall);
        $this->assertSame('retention', $score->bottleneck);
        $this->assertSame('retention', $chacha->currentRecommendation->bottleneck);
        $this->assertSame('repeat_rate', $chacha->activePlan->kpi_metric);
        $this->assertSame('growing', $chacha->passport_level);
        $this->assertGreaterThan(0, $score->changes['acquisition']['delta']);
        $this->assertNotEmpty($chacha->currentReview->review['worked']);

        // Idle cycle: no new activity → no score inflation.
        $this->travel(1)->minutes();
        $demo->closeCycle($chacha->fresh());
        $this->assertSame(74, $chacha->fresh()->latestScore->overall);
    }

    public function test_before_after_deltas(): void
    {
        $kpi = app(KpiService::class);
        $rev = $kpi->delta('revenue', 20_000_000, 23_000_000);
        $this->assertSame(15.0, $rev['relative']);
        $this->assertNull($rev['pp']);
        $conv = $kpi->delta('conversion_rate', 3.0, 4.1);
        $this->assertSame(1.1, $conv['pp']);
        $this->assertSame('+1,1pp', $kpi->formatDelta($conv));

        $bima = $this->scenario('bima');
        $ba = $kpi->beforeAfter($bima);
        $this->assertNotNull($ba);
        $this->assertSame(85_000_000.0, $ba['rows'][0]['before']);
        $this->assertSame(124_000_000.0, $ba['rows'][0]['after']);
    }

    public function test_growth_ledger_page_shows_seeded_months(): void
    {
        $bima = $this->scenario('bima');
        $this->actingAs($bima->user)->get(route('growth.ledger'))->assertOk()->assertSee('Revenue trend')->assertSee('Baseline');
        $this->assertSame(8, $bima->kpiSnapshots()->count());
    }

    public function test_leaderboard_formula_and_ranking(): void
    {
        $svc = app(LeaderboardService::class);
        $this->assertSame(73.5, $svc->score(['target_achievement' => 80, 'mission_completion' => 60, 'kpi_improvement' => 70, 'data_discipline' => 80]));

        $this->scenario('chacha');
        app(\App\Services\Demo\DemoScenarioBuilder::class)->peers();
        $board = $svc->board();
        $this->assertSame(29, $board->count());
        $this->assertTrue($board->pluck('score')->sort()->reverse()->values()->all() === $board->pluck('score')->all());
    }

    public function test_passport_and_expansion_readiness(): void
    {
        $bima = $this->scenario('bima');
        $this->assertSame('scaling', $bima->passport_level);
        $r = app(ExpansionReadinessService::class)->readiness($bima);
        $this->assertGreaterThanOrEqual(80, $r['score']);
        $this->assertLessThanOrEqual(100, $r['score']);

        $site = app(ExpansionReadinessService::class)->siteSense($bima, ['budget' => 60_000_000, 'target_customer' => 'young_pro', 'competition_tolerance' => 'medium']);
        $scores = collect($site->result['candidates'])->pluck('score')->all();
        $this->assertSame($scores, collect($scores)->sortDesc()->values()->all());

        $dce = app(ExpansionReadinessService::class)->dceEligibility($bima);
        $this->assertTrue($dce['eligible']);

        $aisha = app(\App\Services\Demo\DemoScenarioBuilder::class)->build('aisha')->business;
        $this->assertSame('digital_ready', app(PassportService::class)->level($aisha));
        $this->assertFalse(app(ExpansionReadinessService::class)->dceEligibility($aisha)['eligible']);
    }
}
