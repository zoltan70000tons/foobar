<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentLegacyEngineToken;

class CleanExpiredPaymentLegacyEngineTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-payment-legacy-engine-tokens';
    

    /**
     * The console command description.
     *
     * @var string
     */
    
    protected $description = 'Deletes expired and unused payment attempts';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        PaymentLegacyEngineToken::where('expires_at', '<', now())
            ->where('used', false)
            ->delete();

        $this->info('Expired payment attempts cleaned up successfully.');
    }
}
