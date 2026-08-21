<?php

namespace App\Providers;

use App\Services\AlertService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AlertService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip().'|'.strtolower((string) $request->input('email')));
        });

        RateLimiter::for('registration', function (Request $request): Limit {
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}
