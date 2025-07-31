<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\SurvivorNumber;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\TemporaryPassword;


class PublicAuthController extends Controller
{
    use HttpResponses;

    /*
    |--------------------------------------------------------------------------
    | Index login
    |--------------------------------------------------------------------------
    |
    |  Show the login form for the customer.
    |
    */
    public function index(Request $request)
    {
        $frontURL = config('app.frontend_url');

        if (! $request->has(['client_id', 'code_challenge', 'code_challenge_method'])) {
            return redirect($frontURL);
        }

        // Return the login view
       return Inertia::render('OAuth/Login');
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    |
    |  Login the customer. This method handles the OAuth login logic.
    |  If the user is not verified, it redirects to the email verification page.
    |
    */
    public function login(Request $request)
    {
        // Step 1: Validate inputs
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = $data['identifier'];
        $password = $data['password'];

        // Step 2: Resolve user by email or survivor number
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        setPermissionsTeamId(1);

        $user = $isEmail
            ? User::where('email', $identifier)->first()
            : optional(
                SurvivorNumber::where('survivor_number', $identifier)->first()
            )->user;

        // Step 3: Validate resolved user
        if (!$user || !$user->hasRole('Customer')) {
            return response()->json(['message' => __('auth.failed')], 403);
        }

        // Step 4: Check password (standard or temporary)
        $standardLogin = Auth::attempt(['email' => $user->email, 'password' => $password]);

        $validTempPassword = TemporaryPassword::where('customer_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->get()
            ->first(fn ($record) => Hash::check($password, $record->temporary_password));

        if (!$standardLogin && !$validTempPassword) {
            throw ValidationException::withMessages([
                'identifier' => __('auth.failed'),
            ]);
        }

        // If temp password used → mark as used and log in manually
        if ($validTempPassword && !$standardLogin) {
            $validTempPassword->update(['used_at' => now()]);
            Auth::login($user); // Manually log in
        }

        // Step 5: Check email verification
        if (!Auth::user()->hasVerifiedEmail()) {
            return response()->json([
                'redirect' => '/oauth/verify-email',
                'message' => __('auth.email_not_verified'),
            ]);
        }

        // Step 6: Regenerate session + redirect
        $request->session()->regenerate();

        $params = $request->only([
            'client_id',
            'redirect_uri',
            'response_type',
            'code_challenge',
            'code_challenge_method',
            'state',
            'scope',
        ]);

        return response()->json([
            'redirect' => '/oauth/authorize?' . http_build_query($params)
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout from OAuth WEB
    |--------------------------------------------------------------------------
    |  Sometimes used in web context to logout the customer. example: Email verification
    |  This will clear the session and redirect the user to the frontend.
    |
    */
    public function logout()
    {
        // Handle the OAuth logout logic here
        Auth::logout();

        // Clear the session
        session()->invalidate();
        session()->regenerateToken();

        return $this->successResponse(['message' => 'Logout successful']);
    }

}