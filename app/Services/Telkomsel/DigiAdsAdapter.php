<?php

namespace App\Services\Telkomsel;

/** DigiAds — advanced marketing intelligence & targeting (consultative). */
class DigiAdsAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'digiads';
    }

    public function steps(): array
    {
        return ['Consultation', 'Audience Planning', 'Campaign Design', 'Implementation', 'Measurement'];
    }

    public function pendingStatus(): string
    {
        return 'eligibility_check';
    }
}
