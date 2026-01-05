<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OauthClientSeeder extends Seeder {
    public function run() {
        // Get the frontend URL from config
        $frontendUrl = config('app.frontend_url');
        $redirectUri = "{$frontendUrl}/auth/callback";

        DB::table('oauth_clients')->insert([
            'id' => '9f79697c-6d55-4148-8edc-111c76fc38f5',
            'owner_type' => null,
            'owner_id' => null,
            'name' => '70kFrontEnd',
            'secret' => null,
            'provider' => null,
            'redirect_uris' => json_encode([$redirectUri]),
            'grant_types' => json_encode(['authorization_code', 'refresh_token']),
            'revoked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
