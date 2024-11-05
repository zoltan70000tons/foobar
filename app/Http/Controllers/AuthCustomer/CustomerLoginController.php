<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use App\Models\SurvivorNumber;

class CustomerLoginController extends Controller
{
  /**
   * Check if the given email is unique among users.
   * If not, generate signed URLs for account recovery.
   *
   * @param string $email
   * @return \Illuminate\Http\JsonResponse|null
   */
  protected function handleEmailUniqueness(string $email): ?JsonResponse
  {
    $usersWithEmailCount = User::where("email", $email)->count();

    if ($usersWithEmailCount > 1) {
      // Generate signed URLs for account recovery
      $getSignedURL = URL::temporarySignedRoute(
        "recover.account.form",
        Carbon::now()->addMinutes(15),
        [],
        false // Generate relative URL
      );

      $postSignedURL = URL::temporarySignedRoute(
        "recover.account.verify",
        Carbon::now()->addMinutes(15),
        [],
        false // Generate relative URL
      );

      $registerSignedUrl = URL::temporarySignedRoute(
        "recover.account.register",
        Carbon::now()->addMinutes(15),
        [],
        false // Generate relative URL
      );

      return response()->json(
        [
          "status" => "error-uniqueness",
          "message" =>
            "Multiple accounts found with this email. Please recover your account.",
          "get_signed_url" => $getSignedURL,
          "post_signed_url" => $postSignedURL,
          "register_signed_url" => $registerSignedUrl,
        ],
        400
      );
    }

    return null; // Email is unique
  }

  /**
   * Handle an incoming authentication request.
   */
  public function store(Request $request): JsonResponse
  {
    // Step 1: Validate the request data
    $request->validate([
      "identifier" => "required|string",
      "password" => "required|string",
      "remember" => "boolean",
    ]);

    $identifier = $request->input("identifier");
    $password = $request->input("password");
    $remember = $request->input("remember", false);

    // Determine if the identifier is an email
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

    if ($isEmail) {
      // Step 3a: Handle email-based authentication

      // Check email uniqueness and handle recovery if necessary
      $uniquenessResponse = $this->handleEmailUniqueness($identifier);
      if ($uniquenessResponse) {
        return $uniquenessResponse;
      }

      // Prepare credentials for email login
      $credentials = [
        "email" => $identifier,
        "password" => $password,
      ];

      // Attempt to authenticate
      if (Auth::attempt($credentials, $remember)) {
        $user = Auth::user();

        if ($user) {
          // Check if user has the 'Customer' role
          if ($user->hasRole("Customer")) {
            $request->session()->regenerate();

            return response()->json($user, 200);
          } else {
            Auth::logout();

            return response()->json(
              [
                "message" => __("auth.failed"),
              ],
              403
            );
          }
        }
      }

      // Authentication failed
      return response()->json(
        [
          "message" => __("auth.failed"),
        ],
        401
      );
    } else {
      // Handle survivor_number-based authentication

      // Attempt to find the survivor number
      $survivorNumber = SurvivorNumber::where(
        "survivor_number",
        $identifier
      )->first();

      if (!$survivorNumber) {
        return response()->json(
          [
            "message" => __("auth.failed"),
          ],
          401
        );
      }

      // Retrieve the associated user
      $user = $survivorNumber->user;

      if (!$user) {
        return response()->json(
          [
            "message" => __("auth.failed"),
          ],
          401
        );
      }

      // Check email uniqueness and handle recovery if necessary
      $uniquenessResponse = $this->handleEmailUniqueness($user->email);
      if ($uniquenessResponse) {
        return $uniquenessResponse;
      }

      // Prepare credentials using the user's email
      $credentials = [
        "email" => $user->email,
        "password" => $password,
      ];

      // Attempt to authenticate
      if (Auth::attempt($credentials, $remember)) {
        // Check if user has the 'Customer' role
        if ($user->hasRole("Customer")) {
          $request->session()->regenerate();

          return response()->json($user, 200);
        } else {
          Auth::logout();

          return response()->json(
            [
              "message" => __("auth.failed"),
            ],
            403
          );
        }
      }

      // Authentication failed
      return response()->json(
        [
          "message" => __("auth.failed"),
        ],
        401
      );
    }
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): JsonResponse
  {
    Auth::guard("web")->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return response()->json(null, 204);
  }
}
