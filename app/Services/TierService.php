<?php

namespace App\Services;

use App\Models\Business;

/**
 * Single source of truth for Bronze / Silver / Gold feature access.
 * Used by the `tier` route middleware, controllers and views.
 */
class TierService
{
    public function rank(string $tier): int
    {
        return (int) config("tumbuh.tiers.{$tier}.rank", 1);
    }

    public function requiredTier(string $feature): string
    {
        // Feature keys contain dots (e.g. "finance.pnl"), so read the map directly
        // instead of using config() dot-notation. Unknown features are locked to Gold.
        return config('tumbuh.features')[$feature] ?? 'gold';
    }

    public function allows(?Business $business, string $feature): bool
    {
        if (! $business) {
            return false;
        }

        return $this->meets($business, $this->requiredTier($feature));
    }

    public function meets(Business $business, string $tier): bool
    {
        return $business->tierRank() >= $this->rank($tier);
    }

    public function featureLabel(string $feature): string
    {
        return config('tumbuh.feature_labels')[$feature] ?? $feature;
    }

    /** Tier needed to activate a given solution (solution.min_tier). */
    public function canActivate(Business $business, string $minTier): bool
    {
        return $this->meets($business, $minTier);
    }
}
