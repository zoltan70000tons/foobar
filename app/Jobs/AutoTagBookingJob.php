<?php

namespace App\Jobs;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\TagHelper;
use App\Traits\BookingLogTrait;

class AutoTagBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BookingLogTrait;

    protected Booking $booking;
    protected Carbon $now;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->now = Carbon::now('America/Los_Angeles');
        $this->onQueue('auto-tagging');
    }

    public function handle(): void
    {
        $this->handleOverdueTag();
        $this->handleMissingInfoTag();
    }

    protected function handleOverdueTag(): void
    {
        $isOverdue = false;

        foreach ($this->booking->passengers as $passenger) {
            $installmentStatus = $passenger->getInstallmentStatus();
            $nextInstallment   = $installmentStatus['next_installment'] ?? null;

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
                    'Overdue installments > 48h past due'
                );
                $this->saveBookingLog(
                    $this->booking->id,
                    'Added OVERDUE TAG by system',
                    'Added Tag: OVERDUE on ' . now()
                );
            }
        } else {
            if (TagHelper::removeTag($this->booking, 'OVERDUE', 'BOOKING')) {
                $this->saveBookingLog(
                    $this->booking->id,
                    'Removed OVERDUE TAG by system',
                    'Removed Tag: OVERDUE on ' . now()
                );
            }
        }
    }

    protected function handleMissingInfoTag(): void
    {
        $missingInfo = false;

        foreach ($this->booking->passengers as $passenger) {
            $isPrivateCabin = (int) $this->booking->cabin->cabinType->id === 1;
            $isSeatOccupied = ($passenger->empty_seat === false);

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
                    'Indicates passengers with missing information.'
                );
                $this->saveBookingLog(
                    $this->booking->id,
                    'Added MISSING PAX TAG by system',
                    'Added Tag: MISSING PAX on ' . now()
                );
            }
        } else {
            if (TagHelper::removeTag($this->booking, 'MISSING PAX', 'BOOKING')) {
                $this->saveBookingLog(
                    $this->booking->id,
                    'Removed MISSING PAX Tag by system',
                    'Removed Tag: MISSING PAX on ' . now()
                );
            }
        }
    }
}
