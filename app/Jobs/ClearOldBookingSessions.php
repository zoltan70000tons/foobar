<?php

namespace App\Jobs;

use App\Models\BookingAgentSessions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use App\Events\BookingAgentSession;

class ClearOldBookingSessions implements ShouldQueue
{
    use Queueable;
    protected int $expirationMinutes;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->expirationMinutes = config('settings.session_expiration_minutes', 10);
        $this->onQueue('cleaning');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expiredTime = now()->subMinutes($this->expirationMinutes);

        $deleted = BookingAgentSessions::query()
            ->where('time', '<', $expiredTime)
            ->delete();

        if ($deleted > 0) {
            broadcast(new BookingAgentSession(
                agentId: "",
                bookingId: null,
                username: null
            ));
        }

        //logger()->info("ClearOldBookingSessions: Deleted {$deleted} expired booking session(s).");
    }
}
