<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\Passenger;
use App\Support\GlobalLogger;
use Illuminate\Support\Carbon;

class PassengerObserver
{
    public function updated(Passenger $passenger): void
    {
        $changes = $passenger->getChanges();
        if (empty($changes)) {
            return; // nothing changed
        }

        $original = $passenger->getOriginal();

        if ($this->emptySeatChanged($original, $passenger)) {
            $action = $passenger->empty_seat
                ? LogActionBooking::SET_EMPTY_BED
                : LogActionBooking::UNSET_EMPTY_BED;

            $description = $passenger->empty_seat ? 'Set empty bed' : 'Unset empty bed';

            GlobalLogger::log(
                $action,
                'booking',
                $passenger->booking_id,
                $description,
                [
                    'before' => ['empty_seat' => $original['empty_seat']],
                    'after'  => ['empty_seat' => $passenger->empty_seat],
                ]
            );

            return;
        }

        // Skip cost/balance/timestamp-only updates
        $nonTrackedFieldsOnly = collect(array_keys($changes))
            ->diff(['passenger_allocated_cost', 'passenger_balance', 'updated_at'])
            ->isEmpty();

        if ($nonTrackedFieldsOnly) {
            return;
        }

        // Passenger Added (was empty, now filled)
        if (
            empty($original['first_name']) &&
            empty($original['last_name']) &&
            empty($original['dob']) &&
            $passenger->first_name &&
            $passenger->last_name &&
            $passenger->dob
        ) {
            $this->logAdded($passenger, $original);
            return;
        }

        // Passenger Changed (core identity fields changed)
        if (
            $this->fieldsChanged($changes, ['first_name', 'last_name', 'dob'])
        ) {
            $this->logChanged($passenger, $original);
            return;
        }

        // Survivor Number Synced (only survivor_number changed)
        if (
            $this->fieldsChanged($changes, ['survivor_number']) &&
            !$this->fieldsChanged($changes, ['first_name', 'last_name', 'dob'])
        ) {
            //$this->logSurvivorSynced($passenger, $original); //Logged in SyncSurvivorNumbers console command
            return;
        }

        // Generic Passenger Update
        $this->logUpdated($passenger, $original, $changes);

        // Check if passenger has a survivor number and previous sync attempts
        if (
            is_null($passenger->getOriginal('survivor_number')) &&
            $passenger->getOriginal('survivor_sync_attempts') !== 0 &&
            (
                $passenger->first_name !== $passenger->getOriginal('first_name') ||
                $passenger->last_name !== $passenger->getOriginal('last_name') ||
                $passenger->dob !== $passenger->getOriginal('dob')
            )
        ) {
            // Reset sync attempts if name or DOB changed
            $passenger->updateQuietly(['survivor_sync_attempts' => 0]);
        }
    }

    private function emptySeatChanged(array $original, Passenger $passenger): bool
    {
        return ($original['empty_seat'] ?? false) !== (bool) $passenger->empty_seat;
    }

    private function fieldsChanged(array $changes, array $fields): bool
    {
        return !empty(array_intersect(array_keys($changes), $fields));
    }

    private function logAdded(Passenger $passenger, array $original): void
    {
        GlobalLogger::log(
            LogActionBooking::PASSENGER_ADDED,
            'booking',
            $passenger->booking_id,
            sprintf(
                'Passenger added: %s %s (%s)',
                $passenger->first_name,
                $passenger->last_name,
                Carbon::parse($passenger->dob)->toDateString()
            ),
            ['before' => $original, 'after' => $passenger->getChanges()]
        );
    }

    private function logChanged(Passenger $passenger, array $original): void
    {
        GlobalLogger::log(
            LogActionBooking::PASSENGER_CHANGED,
            'booking',
            $passenger->booking_id,
            sprintf(
                'Passenger identity changed: %s %s (%s)',
                $passenger->first_name,
                $passenger->last_name,
                Carbon::parse($passenger->dob)->toDateString()
            ),
            ['before' => $original, 'after' => $passenger->getChanges()]
        );
    }


    private function logUpdated(Passenger $passenger, array $original, array $changes): void
    {
        $changedFields = implode(', ', array_keys($changes));

        GlobalLogger::log(
            LogActionBooking::PASSENGER_UPDATED,
            'booking',
            $passenger->booking_id,
            "Passenger updated: {$changedFields}",
            ['before' => $original, 'after' => $passenger->getChanges()]
        );
    }
}
