<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** The platform is only reachable after Business Onboarding and goal setting. */
class EnsureOnboarded
{
    public function handle(Request $request, Closure $next)
    {
        $business = $request->user()?->business()->first();

        if (! $business || ! $business->onboardingComplete()) {
            return redirect()->route('onboarding.show', ['step' => $business?->onboarding_step ?? 1]);
        }
        if (! $business->goals_set_at) {
            return redirect()->route('goals.edit');
        }

        return $next($request);
    }
}
