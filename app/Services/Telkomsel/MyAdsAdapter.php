<?php

namespace App\Services\Telkomsel;

/**
 * MyAds — acquisition engine. Mirrors the self-serve flow:
 * account → identity verification → approval → top up → campaign → submit → running → monitor.
 * Steps 5–8 are completed by launching a (simulated) campaign.
 */
class MyAdsAdapter extends MockAdapter
{
    public const CAMPAIGN_STEP = 5;

    public function key(): string
    {
        return 'myads';
    }

    public function steps(): array
    {
        return ['Create / Connect Account', 'Identity Verification', 'Approval', 'Top Up', 'Create Campaign', 'Submit', 'Campaign Running', 'Monitor'];
    }

    public function stepRoute(int $step): ?string
    {
        return $step >= self::CAMPAIGN_STEP ? 'campaigns.create' : null;
    }
}
