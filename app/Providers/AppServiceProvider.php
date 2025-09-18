<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\Customer;
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
    // if local or stage development, allow password grant client to issue tokens
    if (in_array($this->app->environment(), ['local'])) {
      Passport::enablePasswordGrant();
    }

    Passport::useClientModel(Client::class);
    Passport::authorizationView('auth.oauth.authorize');
    Passport::tokensExpireIn(now()->addMinutes(20));
    Passport::refreshTokensExpireIn(now()->addDays(30));
    
    // Define a gate to authorize access to the Pulse dashboard
    Gate::define('viewPulse', function (User $user): bool {
      return $user->hasRole('SuperAdmin');
    });

    Relation::morphMap([
      'booking'   => Booking::class,
      'cabin'     => Cabin::class,
      // 'user'      => User::class,
      'customer'  => Customer::class,
      ]);
  }
}
