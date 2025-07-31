<?php

namespace App\Http\Controllers\AuthCustomer;

use App;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\SurvivorNumber;
use Illuminate\Support\Facades\Cookie;
use App\Models\TemporaryPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
// successResponse
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
            $user->tokens()->delete();
        }

        return $this->successResponse(['message' => 'Logout successful']);

    }
}
