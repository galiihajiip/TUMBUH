<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TierAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tier_rules_are_enforced_server_side(): void
    {
        $aisha = $this->scenario('aisha'); // Bronze
        $this->actingAs($aisha->user);
        $this->get(route('growth.before-after'))->assertForbidden()->assertSee('Fitur terkunci', false);
        $this->get(route('campaigns.index'))->assertForbidden();
        $this->get(route('finance.report'))->assertForbidden();
        $this->post(route('finance.scan.upload'), ['sample' => 0])->assertForbidden();
        $this->get(route('scale.msight'))->assertForbidden();
        $this->post(route('solutions.advance', 'myads'))->assertSessionHasErrors('solution');
        $this->get(route('growth.ledger'))->assertOk(); // basic ledger allowed
    }

    public function test_silver_and_gold_access(): void
    {
        $chacha = $this->scenario('chacha'); // Silver
        $this->actingAs($chacha->user);
        $this->get(route('growth.before-after'))->assertOk();
        $this->get(route('finance.report'))->assertOk();
        $this->get(route('scale.sitesense'))->assertForbidden();

        $bima = app(\App\Services\Demo\DemoScenarioBuilder::class)->build('bima')->business; // Gold
        $this->actingAs($bima->user)->get(route('scale.sitesense'))->assertOk();
        $this->actingAs($bima->user)->post(route('scale.msight.run'), ['question' => 'where'])->assertRedirect(route('scale.msight'));
        $this->assertSame(1, $bima->assessments()->where('kind', 'msight')->count());
    }

    public function test_myads_campaign_requires_activation_state_machine(): void
    {
        $chacha = $this->scenario('chacha');
        $this->actingAs($chacha->user);
        $this->get(route('campaigns.create'))->assertRedirect(route('solutions.show', 'myads'));

        foreach (range(1, 3) as $_) {
            $this->post(route('solutions.advance', 'myads'))->assertRedirect();
        }
        $this->post(route('solutions.advance', 'myads'))->assertRedirect(route('campaigns.create'));
        $this->post(route('campaigns.store'), ['name' => 'Promo', 'objective' => 'acquisition', 'audience' => 'Sekitar toko', 'radius_km' => 2, 'budget' => 500000])->assertRedirect(route('campaigns.index'));

        $campaign = $chacha->campaigns()->first();
        $this->assertSame(24500, $campaign->reach);
        $this->assertSame('active', $chacha->activations()->whereHas('solution', fn ($q) => $q->where('key', 'myads'))->value('status'));
    }

    public function test_consent_changes_are_timestamped(): void
    {
        $chacha = $this->scenario('chacha');
        $this->actingAs($chacha->user)->put(route('profile.consents'), ['consents' => ['marketing' => 1, 'analytics' => 1]])->assertRedirect();
        $consents = $chacha->user->consents()->get()->keyBy('consent_type');
        $this->assertTrue($consents['marketing']->granted);
        $this->assertNotNull($consents['marketing']->granted_at);
        $this->assertFalse($consents['partner']->granted);
        $this->assertNotNull($consents['partner']->revoked_at);
    }
}
