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
    // public function index(Request $request): JsonResponse
    // {
    //     $language = $request->input('language', 'en');
    //     App::setLocale($language);

    //     // Step 1: Validate the request data
    //     $request->validate([
    //         'identifier' => 'required|string',
    //         'password' => 'required|string',
    //         'rememberMe' => 'boolean',
    //     ]);

    //     $identifier = $request->input('identifier');
    //     $password = $request->input('password');
    //     $remember = $request->input('rememberMe', false);

    //     // Step 2: Resolve email from identifier
    //     $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);


    //     $email = $isEmail
    //         ? $identifier
    //         : optional(SurvivorNumber::where('survivor_number', $identifier)->first())->user->email;

    //     if (!$email) {
    //         return response()->json(['message' => __('auth.failed')], 401);
    //     }

    //     setPermissionsTeamId(1);

    //     // Step 3: Check user existence and role
    //     $user = User::where('email', $email)->first();

    //     if (!$user || !$user->hasRole('Customer')) {
    //         return response()->json(['message' => __('auth.failed')], 403);
    //     }

    //     $appUrl = env('APP_URL');

    //     // Step 4: Try Passport Password Grant Token
    //     $response = Http::asForm()->post($appUrl . '/oauth/token', [
    //         'grant_type' => 'password',
    //         'client_id' => env('PASSPORT_CLIENT_ID'),
    //         'client_secret' => env('PASSPORT_CLIENT_SECRET'),
    //         'username' => $email,
    //         'password' => $password,
    //         'scope' => '',
    //     ]);

    //     \Log::info('Passport Password Grant Response', [
    //         'status' => $response->status(),
    //         'data' => $response->json(),
    //     ]);

    //     if ($response->successful()) {
    //         $data = $response->json();
    //         return response()->json([
    //             'user' => $user,
    //             'refresh_token' => $data['refresh_token'],
    //             'token' => $data['access_token'],
    //             'expires_in' => $data['expires_in'],
    //             'token_type' => $data['token_type'],
    //         ]);
    //     }

    //     // Step 5: Check temporary password fallback
    //     $validTempPassword = TemporaryPassword::where('customer_id', $user->id)
    //         ->whereNull('used_at')
    //         ->where('expires_at', '>', now())
    //         ->get()
    //         ->first(fn($record) => Hash::check($password, $record->temporary_password));

    //     if ($validTempPassword) {
    //         $validTempPassword->update(['used_at' => now()]);
    //         Auth::login($user, $remember);
    //         setPermissionsTeamId(1);
    //         // $request->session()->regenerate();

    //         // return response()->json($user, 200);
    //         return response()->json([
    //             'user' => $user,
    //         ]);
    //     }

    //     return response()->json(['message' => __('auth.failed')], 401);
  
    // }

    // USE login with PKCE credential flow
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
            'redirect_uri' => 'required|url',
            'code_challenge' => 'required|string',
            'state' => 'required|string',
        ]);

        // Resolve user by email or survivor number
        $identifier = $request->input('identifier');
        $password = $request->input('password');

        $email = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $identifier
            : optional(SurvivorNumber::where('survivor_number', $identifier)->first())->user->email;

        if (!$email) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !$user->hasRole('Customer')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();

        // Save PKCE + state in session for /oauth/authorize to use
        $request->session()->put('code_challenge', $request->input('code_challenge'));
        $request->session()->put('code_challenge_method', 'S256');
        $request->session()->put('state', $request->input('state'));

        // APP_LOCAL_DEV_URL
        $appUrl = env('APP_LOCAL_DEV_URL', 'http://localhost:8000');

        // Redirect back to /oauth/authorize manually
        $query = http_build_query([
            'client_id' => $request->input('client_id'),
            'redirect_uri' => $request->input('redirect_uri'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $request->input('state'),
            'code_challenge' => $request->input('code_challenge'),
            'code_challenge_method' => 'S256',
        ]);

        return response()->json([
            'redirect_to' => $appUrl . '/oauth/authorize?' . $query,
        ]);

    }


    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current access token
        $request->user()?->token()?->revoke();

        // Clear secure cookies (optional if you're using cookies for access_token)
        return response()->json(['message' => 'Logged out successfully'])
            ->withoutCookie('access_token') 
            ->withoutCookie('refresh_token');
    }
}
