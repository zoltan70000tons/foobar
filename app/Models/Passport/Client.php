<?php

namespace App\Models\Passport;

use Laravel\Passport\Client as PassportClient;

use Illuminate\Contracts\Auth\Authenticatable;

class Client extends PassportClient
{
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        // first party clients can skip authorization
        return $this->firstParty();
    }
}