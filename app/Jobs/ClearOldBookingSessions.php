<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

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

        $deleted = DB::table('booking_agent_sessions')
            ->where('time', '<', $expiredTime)
            ->delete();

        //logger()->info("ClearOldBookingSessions: Deleted {$deleted} expired booking session(s).");
    }
}
