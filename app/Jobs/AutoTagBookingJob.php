<?php

namespace App\Jobs;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class AutoTagBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $now;



    /**
     * Create a new job instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->now = Carbon::now('America/Los_Angeles');
        $this->onQueue('auto-tagging');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->handleOverdueTag();
        $this->handleMissingInfoTag();
    }

    protected function handleOverdueTag()
    {
        $tags = collect($this->booking->tags ?? []);
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

        $overdueTag = createTag('OVERDUE', 'BOOKING', '#FF0000', 'Indicates that the booking has overdue payments.');

        if ($isOverdue) {
            $this->booking->attachTags([$overdueTag->id]);
        } else {
            $this->booking->detachTags([$overdueTag->id]);
        }
    }


    protected function handleMissingInfoTag()
    {
        $tags = collect($this->booking->tags ?? []);
        $missingInfo = false;

        foreach ($this->booking->passengers as $passenger) {
            $isPrivateCabin = $this->booking->cabin->cabinType->id == 1;
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
            attachTag($this->booking, 'MISSING INFO', 'BOOKING', '#FFA500', 'Indicates that the booking has passengers with missing information.');
        } else {
            removeTag($this->booking, 'MISSING INFO', 'BOOKING');
        }
    }
}
