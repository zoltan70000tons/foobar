<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use App\Models\Passport\Client;

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

    Passport::useClientModel(Client::class);
    Passport::authorizationView('auth.oauth.authorize');
    Passport::tokensExpireIn(now()->addMinutes(20));
    Passport::refreshTokensExpireIn(now()->addDays(30));
    
    // Define a gate to authorize access to the Pulse dashboard
    Gate::define('viewPulse', function (User $user): bool {
      return $user->hasRole('SuperAdmin');
    });
  }
}
