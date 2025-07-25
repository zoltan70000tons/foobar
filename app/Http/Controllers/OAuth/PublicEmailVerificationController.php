<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Inertia\Inertia;

class PublicEmailVerificationController extends Controller
{
    use HttpResponses;

    // notice
    public function notice(Request $request)
    {
        // Return the email verification notice view
       return Inertia::render('OAuth/VerifyEmail');

    }


    // resend
    public function resend(Request $request)
    {
        // Handle the email verification resend logic here
        // This is a placeholder for the actual implementation
        return $this->successResponse(['message' => 'Verification email resent']);
    }


    // verify
    public function verify(Request $request, $id, $hash)
    {
        // Handle the email verification logic here
        // This is a placeholder for the actual implementation
        return $this->successResponse(['message' => 'Email verified successfully']);
    }
}