<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\TemporaryReservation;
use App\Models\PassengerInvitation;
use Illuminate\Support\Carbon;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})
  ->purpose('Display an inspiring quote')
  ->hourly();

/**
 * -------- SCHEDULE TASKS --------
 * php artisan schedule:work
 *
 */
// Delete expired temporary reservations
Schedule::call(function () {
  TemporaryReservation::where('expires_at', '<', Carbon::now())->delete();
})->everyFiveMinutes();

// Delete passenger invitations after 72 hours
Schedule::call(function () {
  PassengerInvitation::where('created_at', '<', Carbon::now()->subHours(72))->delete();
})->everyFourHours();

Schedule::command('telescope:prune')->daily();
