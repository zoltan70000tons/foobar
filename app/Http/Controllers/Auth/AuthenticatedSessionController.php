<?php

namespace App\Http\Controllers\Auth;

use App\Enums\GlobalLog\LogActionUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\GlobalLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $email = $request->get('email');

        $user = $request->user(); 
        $email = $user->email;
    

        GlobalLogger::log(
            LogActionUser::LOGIN,
            'user',
            $user->id,
            'User logged in',
            [
                'before' => [
                    'email' => $email,
                ],
            ]
        );

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = optional(Auth::user())->id;

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        GlobalLogger::log(
            LogActionUser::LOGOUT,
            'user',
            $userId,
            'User logged out',
            [],
            $userId,
        );

        return redirect('/');
    }
}
