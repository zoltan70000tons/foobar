<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PublicAuthController extends Controller
{
    use HttpResponses;

    // showLoginForm
    public function showLoginForm(Request $request)
    {
        $frontURL = config('app.frontend_url');

        if (! $request->has(['client_id', 'code_challenge', 'code_challenge_method'])) {
            return redirect($frontURL);
        }

        // Return the login view
       return Inertia::render('OAuth/Login');
    }

    // login
    public function login(Request $request)
    {
        // Handle the OAuth login logic here
        // This is a placeholder for the actual implementation
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        // Use exactly what frontend sent in the query string
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

    // logout
    public function logout()
    {
        return $this->successResponse(['message' => 'Logout successful']);
    }

}