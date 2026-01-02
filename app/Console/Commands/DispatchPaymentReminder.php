<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminderJob;
use App\Models\Booking;
use App\Models\Installment;
use App\Models\Passenger;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class DispatchPaymentReminder extends Command {
    protected $signature = 'bookings:dispatch-reminders';
    protected $description = 'send payment reminders to all bookings with due installments';

    public function __construct() {
        parent::__construct();
    }
    public function handle(): int {
        Booking::with(['passengers'])->chunk(100, function ($bookings) {
            foreach ($bookings as $booking) {
                SendPaymentReminderJob::dispatch($booking->id);
            }
        });
        return self::SUCCESS;
    }
}
