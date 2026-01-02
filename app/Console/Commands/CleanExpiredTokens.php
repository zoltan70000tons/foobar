<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Token;
use Laravel\Passport\RefreshToken;

class CleanExpiredTokens extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:purge-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired or revoked access and refresh tokens';

    /**
     * Execute the console command.
     */
    public function handle() {
        $countAccess = Token::where('expires_at', '<', now())->delete();
        $countRefresh = RefreshToken::where('expires_at', '<', now())->delete();

        $this->info("Purged {$countAccess} access tokens and {$countRefresh} refresh tokens.");
    }
}
