<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerVerificationEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

class VerifyEmailController extends Controller
{
    use HttpResponses;

    /*
    |--------------------------------------------------------------------------
    |  Index email verification
    |--------------------------------------------------------------------------
    |
    |  Show the email verification form.
    |
    */
    public function index(Request $request)
    {
        $frontURL = config('app.frontend_url');

        // Set locale from query early
        $language = $request->query('language');
        if (in_array($language, ['en', 'de', 'es'])) {
            App::setLocale($language);
        }

        // if user is authenticated and has verified email, redirect to login
        if (Auth::check() && Auth::user()->hasVerifiedEmail()) {
            return redirect()->to($frontURL . '/en/login?verified=1');
        }

        // Return the email verification view with translations
        return Inertia::render('OAuth/EmailVerify', [
            'tAuth' => Lang::get('oauth.Auth'),
            'tGeneral' => Lang::get('general.General'), 
            'language' => App::getLocale(),
            'user' => Auth::user() ? [
                'id' => Auth::user()->id,
                'email' => Auth::user()->email,
                'name' => Auth::user()->name,
            ] : null,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Resend email verification
    |--------------------------------------------------------------------------
    |
    |  Resend the email verification link to customer.
    |
    */
    public function resend(Request $request)
    {
        // Validate the request
        $language = $request->language;
        App::setLocale($language);

        if ($request->user()->hasVerifiedEmail()) {
          return response()->json([
            'status' => 'already-verified',
            'message' => __('systemEmails.email_verified_already'),
          ]);
        }

        $user = User::findOrFail($request->user()->id);

        if ($user->hasVerifiedEmail()) {
          return response()->json([
            'status' => 'already-verified',
            'message' => __('systemEmails.email_verified_already'),
          ]);
        }

        // Generate verification link
        $verificationUrl = URL::temporarySignedRoute('oauth.email.verify.link', Carbon::now()->addMinutes(60), [
          'id' => $user->getKey(),
          'hash' => sha1($user->getEmailForVerification()),
        ]);

        Mail::to($user->email)->queue(new CustomerVerificationEmail($user, $verificationUrl, $language));
        // $request->user()->notify(new CustomerVerification());

        return response()->json([
          'status' => 'verification-link-sent',
          'message' => __('systemEmails.email_verification_link_sent'),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Verify email
    |--------------------------------------------------------------------------
    |
    |  Verify the email address of the customer.
    |
    */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->to(config('app.frontend_url') . '/en/login?verified=0');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->to(config('app.frontend_url') . '/en/login?verified=1');
        }

        $user->markEmailAsVerified();

        return redirect()->to(config('app.frontend_url') . '/en/login?verified=1');
    }
   
}
