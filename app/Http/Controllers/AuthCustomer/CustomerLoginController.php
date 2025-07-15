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

class CustomerLoginController extends Controller
{
  /**
   * Handle an incoming authentication request.
   */
    public function store(Request $request): JsonResponse
    {
        $language = $request->input('language', 'en');
        App::setLocale($language);

        // Step 1: Validate the request data
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
            'rememberMe' => 'boolean',
        ]);

        $identifier = $request->input('identifier');
        $password = $request->input('password');
        $remember = $request->input('rememberMe', false);

        // Step 2: Resolve email from identifier
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);


        $email = $isEmail
            ? $identifier
            : optional(SurvivorNumber::where('survivor_number', $identifier)->first())->user->email;

        if (!$email) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        // Step 3: Check user existence and role
        $user = User::where('email', $email)->first();

        if (!$user || !$user->hasRole('Customer')) {
            return response()->json(['message' => __('auth.failed')], 403);
        }

        $appUrl = env('APP_URL');

        // Step 4: Try Passport Password Grant Token
        $response = Http::asForm()->post($appUrl . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'username' => $email,
            'password' => $password,
            'scope' => '',
        ]);

        \Log::info('Passport Password Grant Response', [
            'status' => $response->status(),
            'data' => $response->json(),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'user' => $user,
                'token' => $data['access_token'],
                'expires_in' => $data['expires_in'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'token_type' => $data['token_type'],
            ]);
        }

        // Step 5: Check temporary password fallback
        $validTempPassword = TemporaryPassword::where('customer_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->get()
            ->first(fn($record) => Hash::check($password, $record->temporary_password));

        if ($validTempPassword) {
            $validTempPassword->update(['used_at' => now()]);
            Auth::login($user, $remember);
            setPermissionsTeamId(1);
            $request->session()->regenerate();

            return response()->json($user, 200);
        }

    return response()->json(['message' => __('auth.failed')], 401);
  
 }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): JsonResponse
  {
    // Auth::guard('web')->logout();

    // Cookie::queue(Cookie::forget('email_verified'));

    // $request->session()->invalidate();

    // $request->session()->regenerateToken();

    // return response()->json(null, 204);

    Auth::user()->tokens()->delete();

      return response()->json([
          'success' => true,
          'statusCode' => 204,
          'message' => 'Logged out successfully.',
      ], 204);
    }
}
