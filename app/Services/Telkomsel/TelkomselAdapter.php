<?php

namespace App\Services\Telkomsel;

use App\Models\Business;
use App\Models\SolutionActivation;

/**
 * Integration boundary for a Telkomsel capability.
 *
 * Every implementation in this prototype is a LOCAL MOCK: no production
 * Telkomsel API is called and no credentials exist. To integrate for real,
 * implement this interface against the partner API and bind it in
 * AdapterRegistry — domain services stay unchanged.
 */
interface TelkomselAdapter
{
    public function key(): string;

    /** Ordered activation steps shown in the Activation Center. */
    public function steps(): array;

    /** Status while the flow is in progress: activation_required | eligibility_check. */
    public function pendingStatus(): string;

    /** @return array{eligible: bool, reasons: string[]} */
    public function eligibility(Business $business): array;

    /** Optional route the user must complete for a step (e.g. a campaign form). */
    public function stepRoute(int $step): ?string;

    /** Side effects after a step completes (mock state changes only). */
    public function afterStep(SolutionActivation $activation, int $step): void;

    /** Integration mode label: always "simulated" in this prototype. */
    public function mode(): string;
}
