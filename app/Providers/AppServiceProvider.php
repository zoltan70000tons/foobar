<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

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
    // Define a gate to authorize access to the Pulse dashboard
    Gate::define('viewPulse', function (User $user): bool {
      return $user->hasRole('SuperAdmin');
  });
  }
}
