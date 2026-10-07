<?php

namespace App\Services\Telkomsel;

/**
 * DCE — high-touch growth accelerator pathway (selection-based).
 * Not a product SKU; eligibility never guarantees acceptance.
 */
class DceAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'dce';
    }

    public function steps(): array
    {
        return ['Eligibility Assessment', 'Application', 'Profile Submission', 'Selection', 'Mentoring / Accelerator'];
    }

    public function pendingStatus(): string
    {
        return 'eligibility_check';
    }

    public function stepRoute(int $step): ?string
    {
        return 'scale.dce';
    }
}
