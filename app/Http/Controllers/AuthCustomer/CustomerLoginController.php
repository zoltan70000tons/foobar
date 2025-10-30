<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Enums\GlobalLog\LogActionUser;
use App\Http\Controllers\Controller;
use App\Support\GlobalLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Traits\HttpResponses;

class CustomerLoginController extends Controller
{
    use HttpResponses;
    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    |
    |  Logout the customer.
    |
    */
    public function logout(Request $request): JsonResponse
    {
        if ($user = $request->user()) {
            // revoke just current token
            $token = $user->token();
            $token->revoke();
            $token->refreshToken?->revoke();
        }

        GlobalLogger::log(
            LogActionUser::LOGOUT,
            'user',
            $user->id,
            'User logged out',
            [
                'before' => [
                    'email' => $user->email,
                ],
            ]
        );

        return response()->json(['message' => 'API token revoked'])
            ->withCookie(cookie()->forget('access_token'));
    }
}
