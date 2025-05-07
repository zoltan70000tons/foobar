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
    
        if ($isOverdue) {
            if (!$tags->contains('OVERDUE')) {
                $tags->push('OVERDUE');
                $this->booking->update(['tags' => $tags->unique()->values()->all()]);
            }
        } else {
            if ($tags->contains('OVERDUE')) {
                $tags = $tags->reject(fn($tag) => $tag === 'OVERDUE');
                $this->booking->update(['tags' => $tags->values()->all()]);
            }
        }
    }
    

    protected function handleMissingInfoTag()
    {
        $tags = collect($this->booking->tags ?? []);
        $missingInfo = false;
    
        foreach ($this->booking->passengers as $passenger) {
            if (
                empty($passenger->first_name) ||
                empty($passenger->last_name) ||
                empty($passenger->dob) &&
                $passenger->cabin_type == 1 &&
                $passenger->empty_seat === false
            ) {
                $missingInfo = true;
                break;
            }
        }
    
        if ($missingInfo) {
            if (!$tags->contains('MISSING INFO')) {
                $tags->push('MISSING INFO');
                $this->booking->update(['tags' => $tags->unique()->values()->all()]);
            }
        } else {
            if ($tags->contains('MISSING INFO')) {
                $tags = $tags->reject(fn($tag) => $tag === 'MISSING INFO');
                $this->booking->update(['tags' => $tags->values()->all()]);
            }
        }
    }
    
}
