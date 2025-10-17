<?php

namespace App\Providers;

use App\Models\BookingAgentSessions;
use App\Models\Event;
use App\Models\Fee;
use App\Models\OnboardCredit;
use App\Models\Passenger;
use App\Models\PassengerDiscount;
use App\Models\PassengerInvitation;
use App\Models\Payment;
use App\Observers\BookingAgentSessionsObserver;
use App\Observers\CabinObserver;
use App\Observers\CabinSpecsObserver;
use App\Observers\BookingObserver;
use App\Observers\DiscountObserver;
use App\Observers\EventObserver;
use App\Observers\FeeObserver;
use App\Observers\OnboardCreditObserver;
use App\Observers\PassengerInvitationObserver;
use App\Observers\PassengerObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\CabinSpec;
use App\Models\Customer;
use Laravel\Passport\Passport;
use App\Models\Passport\Client;
use App\Observers\CustomerObserver;

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

    Cabin::observe(CabinObserver::class);
    CabinSpec::observe(CabinSpecsObserver::class);
    Booking::observe(BookingObserver::class);
    Customer::observe(CustomerObserver::class);
    User::observe(CustomerObserver::class);
    Payment::observe(PaymentObserver::class);
    Fee::observe(FeeObserver::class);
    PassengerDiscount::observe(DiscountObserver::class);
    BookingAgentSessions::observe(BookingAgentSessionsObserver::class);
    OnboardCredit::observe(OnboardCreditObserver::class);
    Passenger::observe(PassengerObserver::class);
    PassengerInvitation::observe(PassengerInvitationObserver::class);
    Event::observe(EventObserver::class);

    Relation::morphMap([
      'booking'   => Booking::class,
      'cabin'     => Cabin::class,
      // 'user'      => User::class,
      'customer'  => Customer::class,
    ]);
  }
}
