<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
    use HttpResponses;

    /**
     * Return the authenticated user
     *
     */
    public function user(Request $request) {
        return $this->successResponse([
            'user' => $request->user(),
        ]);
    }

    /**
     * Reset password with validated old one
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function resetPassword(Request $request) {
        $request->validate([
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The provided password does not match our records.'],
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->successResponse('Password changed successfully');
    }
}
