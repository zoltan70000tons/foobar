<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\TemporaryReservation;
use Illuminate\Support\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/**
 * -------- SCHEDULE TASKS --------
 * 
 * 
 */
// Delete expired temporary reservations
Schedule::call(function () {
    TemporaryReservation::where('expires_at', '<', Carbon::now())->delete();
})->everyMinute();