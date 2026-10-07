<?php

namespace App\Services\Telkomsel;

use App\Models\Business;
use App\Models\SolutionActivation;

/** Shared behaviour for the local mock adapters. */
abstract class MockAdapter implements TelkomselAdapter
{
    public function pendingStatus(): string
    {
        return 'activation_required';
    }

    public function eligibility(Business $business): array
    {
        return ['eligible' => true, 'reasons' => []];
    }

    public function stepRoute(int $step): ?string
    {
        return null;
    }

    public function afterStep(SolutionActivation $activation, int $step): void
    {
        $meta = $activation->meta ?? [];
        $meta['log'][] = ['step' => $step, 'label' => $this->steps()[$step - 1] ?? null, 'at' => now()->toDateTimeString(), 'mode' => 'simulated'];
        $activation->meta = $meta;
    }

    public function mode(): string
    {
        return 'simulated';
    }
}
