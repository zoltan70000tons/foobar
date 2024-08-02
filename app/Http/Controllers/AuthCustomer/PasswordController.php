<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\App;

class PasswordController extends Controller
{
  /**
   * Update the user's password.
   */
  public function update(Request $request): JsonResponse
  {
    $language = $request->input('language', 'en');
    App::setLocale($language);

    if (!Hash::check($request->input('current_password'), $request->user()->password)) {
      return response()->json(['message' => __('auth.current_password_incorrect')], 422);
    }

    $validated = $request->validate([
      'password' => ['required', Password::defaults(), 'confirmed'],
    ]);

    // Update the user's password
    $request->user()->update([
      'password' => Hash::make($validated['password']),
    ]);

    return response()->json(['message' => __('auth.password_updated_successfully')]);
  }
}
