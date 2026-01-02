<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        Passport::tokensCan([
            'view-booking' => 'View booking details via check-booking',
        ]);

        // Use the custom notification for customers
        // ResetPassword::toMailUsing(function ($notifiable, $token) {
        //   return (new CustomerResetPassword($token))->toMail($notifiable);
        // });
    }
}
