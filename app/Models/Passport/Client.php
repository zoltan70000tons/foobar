<?php

namespace App\Models\Passport;

use Laravel\Passport\Client as BaseClient;
use Illuminate\Contracts\Auth\Authenticatable;

class Client extends BaseClient
{
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return true;
    }
}