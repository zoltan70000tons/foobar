<?php

namespace App\Jobs;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\Booking;
use App\Support\GlobalLogger;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\TagHelper;

class AutoTagBookingJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Booking $booking;
    protected Carbon $now;

    public function __construct(Booking $booking) {
        $this->booking = $booking;
        $this->now = Carbon::now('America/Los_Angeles');
        $this->onQueue('auto-tagging');
    }

    public function handle(): void {
        $this->handleOverdueTag();
        $this->handleMissingInfoTag();
    }

    protected function handleOverdueTag(): void {
        $isOverdue = false;

        foreach ($this->booking->passengers as $passenger) {
            $installmentStatus = $passenger->getInstallmentStatus();
            $nextInstallment = $installmentStatus['next_installment'] ?? null;

            if (!$nextInstallment || empty($nextInstallment['due_date'])) {
                continue;
            }

            $dueDate = Carbon::parse($nextInstallment['due_date']);

            if ($dueDate->lt($this->now->copy()->subHours(48))) {
                $isOverdue = true;
                break;
            }
        }

        if ($isOverdue) {
            if (!TagHelper::isTagged($this->booking, 'OVERDUE', 'BOOKING')) {
                TagHelper::attachTag(
                    $this->booking,
                    'OVERDUE',
                    'BOOKING',
                    '#FF0000',
                    'Overdue installments > 48h past due',
                );
                GlobalLogger::log(
                    LogActionBooking::SYSTEM_ADDED_OVERDUE_TAG,
                    'booking',
                    $this->booking->id,
                    'Added OVERDUE TAG by system',
                );
            }
        } else {
            if (TagHelper::removeTag($this->booking, 'OVERDUE', 'BOOKING')) {
                GlobalLogger::log(
                    LogActionBooking::SYSTEM_REMOVED_OVERDUE_TAG,
                    'booking',
                    $this->booking->id,
                    'Removed OVERDUE TAG by system',
                );
            }
        }
    }

    protected function handleMissingInfoTag(): void {
        $missingInfo = false;

        foreach ($this->booking->passengers as $passenger) {
            $isPrivateCabin = (int) $this->booking->cabin->cabinType->id === 1;
            $isSeatOccupied = $passenger->empty_seat === false;

            if (
                (empty($passenger->first_name) || empty($passenger->last_name) || empty($passenger->dob)) &&
                $isPrivateCabin &&
                $isSeatOccupied
            ) {
                $missingInfo = true;
                break;
            }
        }

        if ($missingInfo) {
            if (!TagHelper::isTagged($this->booking, 'MISSING PAX', 'BOOKING')) {
                TagHelper::attachTag(
                    $this->booking,
                    'MISSING PAX',
                    'BOOKING',
                    '#FFA500',
                    'Indicates passengers with missing information.',
                );
                GlobalLogger::log(
                    LogActionBooking::SYSTEM_ADDED_MISSING_PAX_TAG,
                    'booking',
                    $this->booking->id,
                    'Added MISSING PAX TAG by system',
                );
            }
        } else {
            if (TagHelper::removeTag($this->booking, 'MISSING PAX', 'BOOKING')) {
                GlobalLogger::log(
                    LogActionBooking::SYSTEM_REMOVED_MISSING_PAX_TAG,
                    'booking',
                    $this->booking->id,
                    'Removed MISSING PAX Tag by system',
                );
            }
        }
    }
}
