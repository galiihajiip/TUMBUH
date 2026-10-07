<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Phone/email OTP must be verified before entering the platform. */
class EnsureVerified
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ! $request->user()->isVerified()) {
            return redirect()->route('otp.show');
        }

        return $next($request);
    }
}
