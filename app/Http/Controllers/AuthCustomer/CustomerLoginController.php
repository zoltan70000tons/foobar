<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class CustomerLoginController extends Controller
{
  /**
   * Handle an incoming authentication request.
   */
  public function store(Request $request): JsonResponse
  {
    // Validate the request data
    $request->validate([
        'email'    => 'required|string|email',
        'password' => 'required|string',
    ]);

    $email = $request->input('email');

    // Check if multiple users have the same email
    $usersWithEmailCount = User::where('email', $email)->count();

    if ($usersWithEmailCount > 1) {

        $temporarySignedURL = URL::temporarySignedRoute(
            'recover.account.form', // Route name to handle account recovery
            Carbon::now()->addMinutes(15),
            [],
            false // Generate a relative URL without the domain
        );

        return response()->json([
            'status'  => 'error-uniqueness',
            'message' => 'Multiple accounts found with this email. Please recover your account.',
            'recover_url' => $temporarySignedURL,
        ], 400);
    }

    // Proceed to attempt login
    $credentials = [
        'email'    => $email,
        'password' => $request->input('password'),
    ];

    // Attempt to log in with the provided credentials
    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        if ($user) {
            if ($user->hasRole('Customer')) {
                $request->session()->regenerate();

                return response()->json($user, 200);
            } else {
                Auth::logout();

                return response()->json([
                    'message' => 'Unauthorized: Only customers can login through this route.',
                ], 403);
            }
        }
    }

    return response()->json([
        'message' => __('auth.failed'),
    ], 401);
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): JsonResponse
  {
    Auth::guard('web')->logout();
    
    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return response()->json(null, 204);
  }
}
