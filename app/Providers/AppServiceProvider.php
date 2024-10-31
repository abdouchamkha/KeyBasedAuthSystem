<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(
            function ($notifiable, $token) {
                return config('app.frontend_url')."/reset-password?token={$token}&email={$notifiable->getEmailForPasswordReset()}";
            }
        );
        // Rate limiting setup
        RateLimiter::for('retrieveLoaderLog', function (Request $request) {
            return Limit::perSecond(2)->by($request->ip());
        });
        RateLimiter::for('storeLoaderLog', function (Request $request) {
            return Limit::perSecond(2)->by($request->ip());
        });
    }
}
