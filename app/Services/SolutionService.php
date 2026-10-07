<?php

namespace App\Services;

use App\Models\Business;
use App\Models\SolutionActivation;
use App\Models\TelkomselSolution;
use App\Services\Telkomsel\AdapterRegistry;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Telkomsel solution orchestration: status per business, tier/eligibility
 * gates, and the local activation state machine driven by adapters.
 */
class SolutionService
{
    public const STATUS_LABELS = [
        'active' => 'Active',
        'activation_required' => 'Activation Required',
        'eligibility_check' => 'Eligibility Check',
        'not_activated' => 'Not Activated',
        'simulated' => 'Simulated',
    ];

    public const CONNECTION_LABELS = [
        'connected' => 'Connected (Demo)',
        'simulated' => 'Simulated',
        'pending' => 'Pending',
        'unavailable' => 'Unavailable',
        'self_reported' => 'Self-reported',
    ];

    public function __construct(
        private AdapterRegistry $adapters,
        private TierService $tiers,
        private NotificationService $notifications,
    ) {}

    public function catalog(): Collection
    {
        return TelkomselSolution::orderBy('position')->get();
    }

    /** Activation row for a business, created lazily with the right default. */
    public function activation(Business $business, TelkomselSolution $solution): SolutionActivation
    {
        $activation = $business->activations()->firstOrNew(['telkomsel_solution_id' => $solution->id]);
        if (! $activation->exists) {
            $declared = $business->usesProduct($solution->key);
            $activation->fill([
                'status' => $declared ? 'active' : 'not_activated',
                'step' => $declared ? count($this->adapters->for($solution->key)->steps()) : 0,
                'connection' => $declared ? 'self_reported' : ($this->tiers->canActivate($business, $solution->min_tier) ? 'pending' : 'unavailable'),
                'activated_at' => $declared ? $business->onboarded_at : null,
            ])->save();
        }

        return $activation->setRelation('solution', $solution);
    }

    public function activationsFor(Business $business): Collection
    {
        return $this->catalog()->map(fn ($s) => $this->activation($business, $s));
    }

    public function steps(TelkomselSolution $solution): array
    {
        return $this->adapters->for($solution->key)->steps();
    }

    public function eligibility(Business $business, TelkomselSolution $solution): array
    {
        return $this->adapters->for($solution->key)->eligibility($business);
    }

    public function canActivate(Business $business, TelkomselSolution $solution): bool
    {
        return $this->tiers->canActivate($business, $solution->min_tier);
    }

    /**
     * Advance the activation state machine by one step.
     * Returns the route name the user must visit next, if any.
     */
    public function advance(Business $business, TelkomselSolution $solution): ?string
    {
        if (! $this->canActivate($business, $solution)) {
            throw ValidationException::withMessages(['solution' => $solution->name.' membutuhkan tier '.ucfirst($solution->min_tier).' untuk aktivasi.']);
        }

        $adapter = $this->adapters->for($solution->key);
        $eligibility = $adapter->eligibility($business);
        if (! $eligibility['eligible']) {
            $activation = $this->activation($business, $solution);
            $activation->update(['status' => 'eligibility_check', 'connection' => 'pending']);
            throw ValidationException::withMessages(['solution' => implode(' ', $eligibility['reasons'])]);
        }

        $activation = $this->activation($business, $solution);
        $steps = $adapter->steps();
        if ($activation->status === 'active') {
            return null;
        }

        $next = $activation->step + 1;
        if ($route = $adapter->stepRoute($next)) {
            return $route;
        }

        $this->completeStep($activation, $next);

        return null;
    }

    /** Mark a step as completed (also used by campaign launch / DCE flow). */
    public function completeStep(SolutionActivation $activation, int $step): SolutionActivation
    {
        $solution = $activation->solution;
        $adapter = $this->adapters->for($solution->key);
        $total = count($adapter->steps());
        $step = min($step, $total);

        $adapter->afterStep($activation, $step);
        $done = $step >= $total;
        $activation->fill([
            'step' => $step,
            'status' => $done ? 'active' : $adapter->pendingStatus(),
            'connection' => $done ? 'simulated' : 'pending',
            'activated_at' => $done ? ($activation->activated_at ?? now()) : null,
        ])->save();

        if ($done) {
            $this->notifications->push($activation->business, 'performance', $solution->name.' aktif (simulasi)',
                'Aktivasi '.$solution->name.' selesai dalam mode Demo Simulation. Dampaknya akan diukur di Growth Ledger.', 'Lihat Activation Center', route('solutions.activation'));
        }

        return $activation;
    }

    public function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }
}
