<?php

namespace App\Providers;

use App\Models\User;
use App\Services\TierService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');

        // Authorization: a record is accessible only by the owner of its business.
        Gate::define('own', fn (User $user, $model) => $user->business && (int) $model->business_id === (int) $user->business->id);

        // Shell data for the authenticated app layout.
        View::composer('components.layouts.app', function ($view) {
            $business = current_business();
            $view->with([
                'shellBusiness' => $business,
                'shellUnread' => $business ? $business->notifications()->unread()->count() : 0,
                'shellScore' => $business?->latestScore,
                'tiers' => app(TierService::class),
            ]);
        });
    }
}
