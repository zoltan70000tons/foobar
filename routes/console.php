<?php

use App\Jobs\ClearOldBookingSessions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\TemporaryReservation;
use App\Models\TemporaryPassword;
use App\Models\PassengerInvitation;
use Illuminate\Support\Carbon;
use App\Models\PassengerToken;
use Laravel\Sanctum\PersonalAccessToken;

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

// Delete personal access tokens after 24 hours
Schedule::call(function () {
  PersonalAccessToken::where('created_at', '<', Carbon::now()->subHours(24))->delete();
})->everyOddHour();

// Delete passenger tokens after 24 hours
Schedule::call(function () {
  PassengerToken::where('created_at', '<', Carbon::now()->subHours(24))->delete();
})->everyOddHour();

// Clean up expired payment legacy engine tokens
Schedule::command('app:clean-expired-payment-legacy-engine-tokens')->dailyAt('00:00')->timezone('America/Los_Angeles');

// Autotag bookings with OVERDUE and MISSING_INFO tags
Schedule::command('bookings:dispatch-tags')->dailyAt('00:00')->timezone('America/Los_Angeles');
Schedule::command('app:clean-temporary-files')->dailyAt('00:00')->timezone('America/Los_Angeles');

// Delete access tand refresh tokens which expired daily
Schedule::command('passport:purge-expired-tokens')->dailyAt('00:00')->timezone('America/Los_Angeles');
Schedule::command('bookings:dispatch-reminders')->dailyAt('00:00')->timezone('America/Los_Angeles');

// Create log partitions for the next year (e.g. logs_booking_2026, logs_event_2026, etc.)
Schedule::command('logs:create-year-partitions')
    ->yearlyOn(12, 15, '03:00') // Run on December 15th at 3:00 AM
    ->withoutOverlapping()
    ->runInBackground();
