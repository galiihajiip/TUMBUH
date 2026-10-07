<?php

namespace App\Services\Telkomsel;

/** RCS Business Messaging — retention & customer engagement engine. */
class RcsAdapter extends MockAdapter
{
    public function key(): string
    {
        return 'rcs';
    }

    public function steps(): array
    {
        return ['Use-case Planning', 'Select Audience', 'Create Interactive Message', 'Campaign Live & Measure'];
    }
}
