<?php

namespace App\Console\Commands;

use App\Enums\GlobalLog\LogActionBooking;
use App\Support\GlobalLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\UserDetail;
use App\Models\PotentialSurvivorMatch;
use App\Services\SurvivorMatchScoring;
use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SyncSurvivorNumbers extends Command {
    protected $signature = 'survivors:sync {--only-summary}';
    protected $description = 'Sync missing survivor numbers using fuzzy matching';

    public function handle(): int {
        if (!$this->option('only-summary')) {
            $this->info('Starting Survivor Number Sync...');
        }

        $bookings = Booking::query()
            ->where('status', '!=', BookingStatus::CANCELLED->value)
            ->whereHas('event', fn($q) => $q->where('status', '!=', EventStatus::CLOSED->value))
            ->with(['passengers'])
            ->get();

        $updatedCount = 0;
        $attemptedCount = 0;
        $storedCount = 0;

        $candidates = UserDetail::query()
            ->join('survivor_numbers', 'user_details.user_id', '=', 'survivor_numbers.user_id')
            ->select('user_details.*', 'survivor_numbers.survivor_number')
            ->get();

        foreach ($bookings as $booking) {
            foreach ($booking->passengers as $passenger) {
                if ($passenger->survivor_number || $passenger->survivor_sync_attempts >= 3) {
                    continue;
                }

                $attemptedCount++;

                $bestScore = 0.0;
                $bestMatch = null;

                foreach ($candidates as $candidate) {
                    $score = SurvivorMatchScoring::totalScore(
                        $passenger->first_name,
                        $candidate->first_name,
                        $passenger->last_name,
                        $candidate->last_name,
                        $passenger->dob,
                        $candidate->dob,
                    );

                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestMatch = $candidate;
                    }
                }

                // No suitable similarity
                if ($bestScore < 80) {
                    $passenger->increment('survivor_sync_attempts');
                    continue;
                }

                // Potential match (requires manual review)
                if ($bestScore < 90) {
                    $alreadyExists = PotentialSurvivorMatch::where('passenger_id', $passenger->id)
                        ->where('user_detail_id', $bestMatch->id)
                        ->where('status', 'in_progress')
                        ->exists();

                    if (!$alreadyExists) {
                        PotentialSurvivorMatch::create([
                            'passenger_id' => $passenger->id,
                            'passenger_first_name' => $passenger->first_name,
                            'passenger_last_name' => $passenger->last_name,
                            'passenger_dob' => $passenger->dob,

                            'user_detail_id' => $bestMatch->id,
                            'user_first_name' => $bestMatch->first_name,
                            'user_last_name' => $bestMatch->last_name,
                            'user_dob' => $bestMatch->dob,

                            'score' => $bestScore,
                            'status' => 'in_progress',
                        ]);

                        $storedCount++;

                        GlobalLogger::log(
                            LogActionBooking::SURVIVOR_NUMBER_NEEDS_MANUAL_VERIFICATION,
                            'booking',
                            $booking->id,
                            sprintf(
                                '%s %s added to verification queue (score %.2f)',
                                $passenger->first_name,
                                $passenger->last_name,
                                $bestScore,
                            ),
                            [
                                'after' => [
                                    'firstName' => $passenger->first_name,
                                    'lastName' => $passenger->last_name,
                                    'dob' => Carbon::parse($passenger->dob)->toDateString(),
                                    'survivorNumber' => $bestMatch->survivor_number,
                                    'passengerId' => $passenger->id,
                                    'userId' => $bestMatch->user_id,
                                    'score' => $bestScore,
                                ],
                            ],
                        );
                    }

                    $passenger->increment('survivor_sync_attempts');
                    continue;
                }

                // Automatic high-confidence match (>= 90)
                DB::transaction(function () use ($bestScore, $passenger, $bestMatch, $booking, &$updatedCount) {
                    // --- PREVENT DOUBLE BOOKING CHECK ---
                    $eventId = $booking->event_id;
                    $survivorNumber = $bestMatch->survivor_number;

                    $conflictExists = Booking::query()
                        ->where('event_id', $eventId)
                        ->where('id', '!=', $booking->id)
                        ->where('status', '!=', 'CANCELLED')
                        ->whereHas('event', function ($q) {
                            $q->where('status', '!=', 'CLOSED');
                        })
                        ->whereHas('passengers', function ($q) use ($survivorNumber) {
                            $q->where('survivor_number', $survivorNumber);
                        })
                        ->exists();

                    if ($conflictExists) {
                        GlobalLogger::log(
                            LogActionBooking::POTENTIAL_DUPLICATE_REJECTED,
                            'booking',
                            $booking->id,
                            sprintf(
                                'Automatic match prevented for %s %s — Survivor Number %s already used in this event.',
                                $passenger->first_name,
                                $passenger->last_name,
                                $survivorNumber,
                            ),
                            [
                                'attempted' => [
                                    'passengerId' => $passenger->id,
                                    'firstName' => $passenger->first_name,
                                    'lastName' => $passenger->last_name,
                                    'dob' => Carbon::parse($passenger->dob)->toDateString(),
                                    'survivorNumber' => $survivorNumber,
                                    'score' => $bestScore,
                                ],
                                'eventId' => $eventId,
                            ],
                        );

                        PotentialSurvivorMatch::create([
                            'passenger_id' => $passenger->id,
                            'passenger_first_name' => $passenger->first_name,
                            'passenger_last_name' => $passenger->last_name,
                            'passenger_dob' => $passenger->dob,

                            'user_detail_id' => $bestMatch->id,
                            'user_first_name' => $bestMatch->first_name,
                            'user_last_name' => $bestMatch->last_name,
                            'user_dob' => $bestMatch->dob,

                            'score' => $bestScore,
                            'type' => 'double_booking',
                        ]);

                        $passenger->increment('survivor_sync_attempts');
                        return;
                    }
                    // --- END DOUBLE BOOKING CHECK ---

                    $passenger->update([
                        'survivor_number' => $bestMatch->survivor_number,
                        'survivor_sync_attempts' => 0,
                    ]);

                    GlobalLogger::log(
                        LogActionBooking::SURVIVOR_NUMBER_SYNCED,
                        'booking',
                        $booking->id,
                        sprintf(
                            '%s %s automatically matched (score %.2f) and assigned Survivor Number %s.',
                            $passenger->first_name,
                            $passenger->last_name,
                            $bestScore,
                            $bestMatch->survivor_number,
                        ),
                        [
                            'after' => [
                                'firstName' => $passenger->first_name,
                                'lastName' => $passenger->last_name,
                                'dob' => Carbon::parse($passenger->dob)->toDateString(),
                                'survivorNumber' => $bestMatch->survivor_number,
                                'passengerId' => $passenger->id,
                                'userId' => $bestMatch->user_id,
                                'score' => $bestScore,
                            ],
                        ],
                    );

                    $updatedCount++;
                });
            }
        }

        $this->info(
            "Sync completed. {$updatedCount} passengers updated. {$storedCount} stored. {$attemptedCount} checked.",
        );

        return CommandAlias::SUCCESS;
    }
}
