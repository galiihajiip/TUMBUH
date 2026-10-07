<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_completes_mission_and_activates_next(): void
    {
        $business = $this->scenario('aisha');
        $this->actingAs($business->user);
        $mission = $business->missions()->where('status', 'active')->first();
        $this->assertSame(1, $mission->week);

        $this->post(route('missions.check-in', $mission), ['completion' => 'maybe'])->assertSessionHasErrors('completion');
        $this->post(route('missions.check-in', $mission), ['completion' => 'yes', 'kpi_value' => 3, 'notes' => 'Segmen siap', 'problem' => 'Waktu terbatas'])
            ->assertRedirect(route('missions.index'));

        $this->assertSame('completed', $mission->fresh()->status);
        $this->assertSame('Waktu terbatas', $mission->submissions()->first()->problem);
        $this->assertSame('active', $business->missions()->where('week', 2)->first()->status);
        $this->assertTrue($business->achievements()->where('key', 'first_mission')->exists());
    }

    public function test_failed_check_in_keeps_mission_active(): void
    {
        $business = $this->scenario('aisha');
        $mission = $business->missions()->where('status', 'active')->first();
        $this->actingAs($business->user)->post(route('missions.check-in', $mission), ['completion' => 'no', 'problem' => 'Belum sempat']);
        $this->assertSame('active', $mission->fresh()->status);
    }

    public function test_cannot_access_another_business_mission(): void
    {
        $aisha = $this->scenario('aisha');
        $chacha = app(\App\Services\Demo\DemoScenarioBuilder::class)->build('chacha')->business;
        $foreign = $chacha->missions()->first();

        $this->actingAs($aisha->user)->get(route('missions.show', $foreign))->assertForbidden();
        $this->actingAs($aisha->user)->post(route('missions.check-in', $foreign), ['completion' => 'yes'])->assertForbidden();
    }
}
