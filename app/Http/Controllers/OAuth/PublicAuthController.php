<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PublicAuthController extends Controller
{
    use HttpResponses;


    // showLoginForm
    public function showLoginForm()
    {
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

        \Log::debug('Redirecting to /oauth/authorize with params', $params);

        return response()->json([
            'redirect' => '/oauth/authorize?' . http_build_query($params)
        ]);
        //return redirect('/oauth/authorize?' . http_build_query($params));
    }

    // logout
    public function logout(Request $request)
    {
        // Handle the OAuth logout logic here
        // This is a placeholder for the actual implementation
        return $this->successResponse(['message' => 'Logout successful']);
    }

    // showRegistrationForm
    public function showRegistrationForm()
    {
        // Return the registration view
        return Inertia::render('OAuth/Register');

    }

    // register
    public function register(Request $request)
    {
        // Handle the OAuth registration logic here
        // This is a placeholder for the actual implementation
        return $this->successResponse(['message' => 'Registration successful']);
    }
}