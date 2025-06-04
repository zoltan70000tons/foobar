<?php

use App\Jobs\ClearOldBookingSessions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\TemporaryReservation;
use App\Models\TemporaryPassword;
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

Schedule::job(new ClearOldBookingSessions())->everyMinute();


// Delete temporary passwords after 20 minutes
Schedule::call(function () {
  TemporaryPassword::where('expires_at', '<', Carbon::now())->delete();
})->everyTenMinutes();

// Autotag bookings with OVERDUE and MISSING_INFO tags
Schedule::command('bookings:dispatch-tags')->dailyAt('00:00')->timezone('America/Los_Angeles');
Schedule::command('app:clean-temporary-files')->dailyAt('00:00')->timezone('America/Los_Angeles');
