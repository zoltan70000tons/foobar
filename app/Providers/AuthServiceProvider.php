<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Notifications\CustomerResetPassword;
use Illuminate\Support\Facades\Password;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    // Use the custom notification for customers
    // ResetPassword::toMailUsing(function ($notifiable, $token) {
    //   return (new CustomerResetPassword($token))->toMail($notifiable);
    // });
  }
}
