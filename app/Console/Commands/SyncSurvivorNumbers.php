<?php

namespace App\Console\Commands;

use App\Enums\GlobalLog\LogActionBooking;
use App\Support\GlobalLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\UserDetail;
use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SyncSurvivorNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survivors:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing survivor numbers for manually added passengers in active bookings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Survivor Number Sync...');

        $bookings = Booking::query()
            ->where('status', '!=', BookingStatus::CANCELLED->value)
            ->whereHas('event', fn($q) => $q->where('status', '!=', EventStatus::CLOSED->value))
            ->with(['passengers'])
            ->get();

        $updatedCount = 0;
        $attemptedCount = 0;

        foreach ($bookings as $booking) {
            foreach ($booking->passengers as $passenger) {
                if ($passenger->survivor_number || $passenger->survivor_sync_attempts >= 3) {
                    continue;
                }

                $attemptedCount++;

                $match = UserDetail::query()
                    ->where('first_name', $passenger->first_name)
                    ->where('last_name', $passenger->last_name)
                    ->whereDate('dob', $passenger->dob)
                    ->join('survivor_numbers', 'user_details.user_id', '=', 'survivor_numbers.user_id')
                    ->select('user_details.user_id', 'survivor_numbers.survivor_number')
                    ->first();

                if ($match) {
                    DB::transaction(function () use ($passenger, $match, $booking, &$updatedCount) {
                        $passenger->update([
                            'survivor_number' => $match->survivor_number,
                            'survivor_sync_attempts' => DB::raw('survivor_sync_attempts + 1'),
                        ]);

                        GlobalLogger::log(
                            LogActionBooking::SURVIVOR_NUMBER_SYNCED,
                            'booking',
                            $booking->id,
                            sprintf(
                                '%s %s was assigned Survivor Number %s',
                                $passenger->first_name,
                                $passenger->last_name,
                                $match->survivor_number
                            ),
                            [
                                'after' => [
                                    'firstName' => $passenger->first_name,
                                    'lastName' => $passenger->last_name,
                                    'dob' => Carbon::parse($passenger->dob)->toDateString(),
                                    'survivorNumber' => $match->survivor_number,
                                    'passengerId' => $passenger->id,
                                    'userId' => $match->user_id,
                                ],
                            ],
                        );

                        $updatedCount++;
                    });
                } else {
                    // No match found → increment attempts
                    $passenger->increment('survivor_sync_attempts');
                }
            }
        }

        $this->info("Sync completed. {$updatedCount} passengers updated. {$attemptedCount} checked.");

        return CommandAlias::SUCCESS;
    }
}
