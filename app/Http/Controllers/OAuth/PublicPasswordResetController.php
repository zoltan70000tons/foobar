<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Inertia\Inertia;

class PublicPasswordResetController extends Controller
{
    use HttpResponses;

    // showLinkRequestForm
    public function showLinkRequestForm()
    {
        // Return the password reset request view
        return Inertia::render('OAuth/ForgotPassword');
    }


    // sendResetLink
    public function sendResetLink(Request $request)
    {
        // Handle the password reset link sending logic here
        // This is a placeholder for the actual implementation
        return $this->successResponse(['message' => 'Password reset link sent']);
    }

    // showResetForm
    public function showResetForm(Request $request, $token = null)
    {
        // Return the password reset form view
        return Inertia::render('OAuth/ResetPassword', ['token' => $token]);
    }

    // reset
    public function reset(Request $request)
    {
        // Handle the password reset logic here
        // This is a placeholder for the actual implementation
        return $this->successResponse(['message' => 'Password has been reset']);
    }
}