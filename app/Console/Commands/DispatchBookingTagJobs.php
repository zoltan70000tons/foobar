<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Jobs\AutoTagBookingJob;

class DispatchBookingTagJobs extends Command {
    protected $signature = 'bookings:dispatch-tags';
    protected $description = 'Dispatches tagging jobs for each booking';

    public function handle() {
        $bookings = Booking::with(['passengers'])->get();

        foreach ($bookings as $booking) {
            AutoTagBookingJob::dispatch($booking);
        }

        $this->info('All tagging jobs dispatched successfully.');
    }
}
