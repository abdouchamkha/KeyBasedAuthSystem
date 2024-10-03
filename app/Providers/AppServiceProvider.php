<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
