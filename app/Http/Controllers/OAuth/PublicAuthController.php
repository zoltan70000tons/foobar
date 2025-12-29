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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

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
    // Set locale from query as early as possible for both views
    $language = $request->query('language');
    if (in_array($language, ['en', 'de', 'es'])) {
      App::setLocale($language);
    }

    if (Auth::check() && Auth::user()->hasRole('Customer') && !Auth::user()->hasVerifiedEmail()) {
      return Inertia::render('OAuth/EmailVerify', [
        'tAuth' => Lang::get('oauth.Auth'),
        'tGeneral' => Lang::get('general.General'),
        'language' => App::getLocale(),
        'user' => Auth::user()
          ? [
            'id' => Auth::user()->id,
            'email' => Auth::user()->email,
            'name' => Auth::user()->name,
          ]
          : null,
      ]);
    }

    // if not contain client_id redirect to error page
    if (!$request->query('client_id')) {
      $frontURL = config('app.frontend_url') . '/' . App::getLocale();
      return redirect()->to($frontURL);
    }

    // Return the login view with translations for the page
    return Inertia::render('OAuth/Login', [
      'tAuth' => Lang::get('oauth.Auth'),
      'tGeneral' => Lang::get('general.General'),
      'language' => App::getLocale(),
    ]);
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
      'lang' => ['required', 'string', 'max:5'],
      'identifier' => ['required', 'string'],
      'password' => ['required', 'string'],
    ]);

    // set language
    $language = $data['lang'];
    if (in_array($language, ['en', 'de', 'es'])) {
      App::setLocale($language);
    }
    $identifier = $data['identifier'];
    $password = $data['password'];

    // Step 2: Resolve user by email or survivor number
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

    setPermissionsTeamId(1);

    $user = $isEmail
      ? User::where('email', $identifier)->first()
      : optional(SurvivorNumber::where('survivor_number', $identifier)->first())->user;

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
      ->first(fn($record) => Hash::check($password, $record->temporary_password));

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
        'redirect' => '/oauth/verify-email?language=' . $language,
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
      'redirect' => '/oauth/authorize?' . http_build_query($params),
    ]);
  }

  /*
    |--------------------------------------------------------------------------
    | Logout from OAuth WEB
    |--------------------------------------------------------------------------
    |  Sometimes used in web context to logout the customer. example: Email verification
    |  We use there POST method 
    |
    */
  public function logoutWeb()
  {
    // Handle the OAuth logout logic here
    Auth::guard('web')->logout();

    // Clear the session
    session()->invalidate();
    session()->regenerateToken();

    // Redirect to the frontend
    return response()->json(['message' => __('auth.logout_successful')]);
  }

  /*
    |--------------------------------------------------------------------------
    | Custom error page for OAuth SPA
    |--------------------------------------------------------------------------
    |  This method is reverved for GET request and OAuth Errors.
    |
    */
  public function errorPage(Request $request)
  {
    // Set locale from query as early as possible for both views
    $language = $request->query('language');
    if (in_array($language, ['en', 'de', 'es'])) {
      App::setLocale($language);
    }

    $frontURL = config('app.frontend_url') . '/' . $language;

    // if no param error, redirect to login
    if ($request->query('error') === null) {
      return redirect()->to($frontURL);
    }

    $errorMessage = null;
    $errorDescription = null;

    // Handle specific error cases
    $error = $request->query('error');
    if ($error === 'expired') {
      $errorMessage = __('oauth.Auth.link_expired');
    }

    // Return the error view with translations for the page
    return Inertia::render('OAuth/CustomError', [
      'tAuth' => Lang::get('oauth.Auth'),
      'tGeneral' => Lang::get('general.General'),
      'language' => App::getLocale(),
      'error' => $errorMessage,
      'error_description' => $errorDescription,
    ]);
  }

  /*
    |--------------------------------------------------------------------------
    | Logout from OAuth SPA
    |--------------------------------------------------------------------------
    |  This method is reverved for GET request and SPA context.
    |  
    |
    */
  public function logoutSPA()
  {
    // Get param langauge from url
    $language = request()->query('language', 'en');

    // Handle the OAuth logout logic here
    Auth::guard('web')->logout();

    // Clear the session
    session()->invalidate();
    session()->regenerateToken();

    $frontURL = config('app.frontend_url') . '/' . $language;

    // Redirect to the frontend
    return redirect($frontURL)->with('message', __('auth.logout_successful'));
  }
}
