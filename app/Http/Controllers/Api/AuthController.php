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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('The provided credentials are incorrect.', 401);
        }

        // return $this->successResponse($user->createToken($request->email)->plainTextToken);
        
        // return response with user data and token
        return $this->successResponse([
            'user' => $user,
            'access_token' => $user->createToken($request->email)->plainTextToken
        ]);
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
            'password' => 'required|string|min:5|confirmed',
            'policy' => 'required|boolean',
        ]);

        $user = User::create([
            'email' => $request->email,
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'policy' => $request->policy,
        ]);

        // return $this->successResponse($user->createToken($request->email)->plainTextToken);
        
        // return response with user data and token
        return $this->successResponse('Account created successfully. You can now login.');
    }
}
