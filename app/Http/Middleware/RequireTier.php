<?php

namespace App\Http\Middleware;

use App\Services\TierService;
use Closure;
use Illuminate\Http\Request;

/**
 * Server-side tier enforcement: `->middleware('tier:before_after')`.
 * Locked features render the tier gate (HTTP 403), never the feature.
 */
class RequireTier
{
    public function __construct(private TierService $tiers) {}

    public function handle(Request $request, Closure $next, string $feature)
    {
        $business = $request->user()?->business;

        if (! $this->tiers->allows($business, $feature)) {
            return response()->view('app.tier-gate', [
                'feature' => $feature,
                'featureLabel' => $this->tiers->featureLabel($feature),
                'required' => $this->tiers->requiredTier($feature),
            ], 403);
        }

        return $next($request);
    }
}
