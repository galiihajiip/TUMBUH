<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Support\Facades\Gate;

abstract class Controller
{
    private ?Business $currentBusiness = null;

    protected function business(): Business
    {
        $this->currentBusiness ??= auth()->user()?->business()->first();
        abort_unless($this->currentBusiness, 404);

        return $this->currentBusiness;
    }

    /** Ownership check for business-scoped records (Gate "own"). */
    protected function authorizeOwn($model): void
    {
        Gate::authorize('own', $model);
    }
}
