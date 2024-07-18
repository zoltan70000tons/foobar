<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use HttpResponses;

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
      try {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        // Ensure the user model uses Laravel Sanctum or Passport
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ]);
      } catch (\Exception $e) {
          // Log the error for further debugging
          Log::error('Login error: ' . $e->getMessage());
          return response()->json(['message' => 'An error occurred during login.'], 500);
      }
    }


    /**
     * Return the authenticated user
     * 
     */
    public function user(Request $request)
    {
        return $this->successResponse([
          'user' => $request->user()
        ]);
    }


    /**
     * Handle an incoming logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Delete the current token request
        // $request->user()->currentAccessToken()->delete();

        // Delete all tokens
        $request->user()->tokens()->delete();

        return $this->successResponse('Logged out successfully');
    }

    /**
     * Verify token request
     * The request is made from the middleware front end client side.
     * We want to verify if the token is valid.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function verifyToken(Request $request)
    {
        // check if the token is valid
        return $this->successResponse('Token is valid');
    }


    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:'.User::class.'|min:1',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => 'required|string|min:6|confirmed',
            'policy' => 'required|boolean',
        ]);

        $user = User::create([
            'email' => $request->email,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'policy' => $request->policy,
        ]);

        // return response with user data and token
        return $this->successResponse('Account created successfully. You can now login.');
    }

    /**
     * Check the user confimation status
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkConfirmation(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return $this->successResponse('Email is confirmed');
        }

        return $this->errorResponse('Email is not confirmed', 401);
    }

    /**
     * Reset password with validated old one
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * 
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The provided password does not match our records.'],
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->successResponse('Password changed successfully');
    }


}
