<?php

namespace Tests\Feature;

use App\Models\DiagnosticQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Full new-user journey: onboarding → goals → diagnostic → baseline → score → AI → plan. */
class OnboardingDiagnosticTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(): User
    {
        $user = User::create(['name' => 'Budi', 'email' => 'budi@example.com', 'phone' => '081211112222', 'role' => 'owner', 'password' => 'rahasia123']);
        $user->forceFill(['phone_verified_at' => now()])->save();

        return $user;
    }

    public function test_onboarding_goals_and_diagnostic_produce_score_bottleneck_and_plan(): void
    {
        $this->seedCatalog();
        $user = $this->verifiedUser();
        $this->actingAs($user);

        $this->get('/overview')->assertRedirect(route('onboarding.show', ['step' => 1]));
        $this->post('/onboarding/1', ['name' => 'Warung Budi', 'category' => 'Food & Beverage', 'sub_category' => 'Restoran', 'start_year' => 2020])->assertRedirect(route('onboarding.show', 2));
        $this->post('/onboarding/3', [])->assertNotFound(); // cannot skip ahead
        $this->post('/onboarding/2', ['province' => 'Jawa Timur', 'city' => 'Surabaya', 'district' => 'Gubeng', 'has_physical_store' => 1, 'outlets' => 1])->assertSessionHasNoErrors()->assertRedirect(route('onboarding.show', 3));
        $this->post('/onboarding/3', ['employees' => 3, 'revenue_range' => '10_25', 'avg_monthly_transactions' => 600, 'avg_order_value' => 25000, 'monthly_customers' => 300])->assertSessionHasNoErrors()->assertRedirect(route('onboarding.show', 4));
        $this->post('/onboarding/4', ['sales_channels' => ['store', 'instagram']])->assertSessionHasErrors('instagram_url');
        $this->post('/onboarding/4', ['sales_channels' => ['store', 'instagram'], 'instagram_url' => 'https://instagram.com/warungbudi']);
        $this->post('/onboarding/5', ['digital_tools' => ['qris', 'social_media']]);
        $this->post('/onboarding/6', ['telkomsel_products' => ['mytelkomsel']])->assertRedirect(route('goals.edit'));

        $business = $user->fresh()->business;
        $this->assertNotNull($business->onboarded_at);
        $this->assertSame(['store', 'instagram'], $business->sales_channels);

        $this->post('/goals', ['goals' => ['increase_revenue', 'more_customers', 'repeat_customers', 'new_market']])->assertSessionHasErrors('goals');
        $this->post('/goals', ['goals' => ['increase_revenue', 'more_customers'], 'current_revenue' => 15000000, 'target_revenue' => 20000000, 'target_period' => 60])
            ->assertRedirect(route('diagnostic.start'));
        $this->assertEquals(20000000, $business->goals()->where('goal_key', 'increase_revenue')->value('target_value'));

        // Diagnostic: one question at a time with validation and autosave.
        $questions = DiagnosticQuestion::active()->get();
        $this->post('/diagnostic/q/1', ['answer' => 'not-an-option'])->assertSessionHasErrors('answer');
        $this->postJson('/diagnostic/autosave', ['question_id' => $questions[0]->id, 'answer' => $questions[0]->options[0]['value']])->assertOk()->assertJson(['saved' => true]);
        foreach ($questions as $i => $q) {
            $answer = $q->type === 'multi' ? [$q->options[0]['value']] : $q->options[0]['value'];
            $this->post('/diagnostic/q/'.($i + 1), ['answer' => $answer])->assertRedirect();
        }
        $this->post('/diagnostic/submit')->assertRedirect(route('diagnostic.baseline'));
        $this->post('/diagnostic/baseline', ['revenue' => 15000000, 'new_customers' => 40, 'total_customers' => 300, 'conversion_rate' => 2, 'repeat_rate' => 15, 'marketing_spend' => 0])
            ->assertRedirect(route('growth.score'));

        $business->refresh();
        $score = $business->latestScore;
        $this->assertNotNull($score);
        $this->assertGreaterThanOrEqual(0, $score->overall);
        $this->assertLessThanOrEqual(100, $score->overall);
        $this->assertSame(collect($score->dimensions)->sort()->keys()->first(), $score->bottleneck);
        $this->assertNotNull($business->currentRecommendation);
        $this->assertCount(4, $business->activePlan->items);
        $this->assertSame(1, $business->missions()->where('status', 'active')->count());
        $this->assertTrue($business->kpiSnapshots()->first()->is_baseline);

        $this->get('/growth/score')->assertOk()->assertSee('Your biggest growth bottleneck is');
        $this->get('/assistant')->assertOk()->assertSee("What's wrong?");
    }
}
