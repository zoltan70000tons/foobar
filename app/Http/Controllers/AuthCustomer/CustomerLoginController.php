<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerLoginController extends Controller
{
  /**
   * Handle an incoming authentication request.
   */
  public function store(Request $request): JsonResponse
  {
    // Validate the request data
    $request->validate([
      'survival_number' => 'required|string',
      'password' => 'required|string',
    ]);

    $credentials = [
      'survival_number' => $request->input('survival_number'),
      'password' => $request->input('password'),
    ];

    // Attempt to log in with survival_number first
    if (Auth::guard('customer')->attempt($credentials)) {
      return $this->authenticated($request);
    }

    // Attempt to log in with name if the first attempt fails
    $credentials = [
      'name' => $request->input('survival_number'),
      'password' => $request->input('password'),
    ];

    if (Auth::guard('customer')->attempt($credentials)) {
      return $this->authenticated($request);
    }

    return response()->json([
      'message' => __('auth.failed'),
    ], 401);
  }

  // Handle an incoming authentication request. 
  // Based on the user's email or username
  protected function authenticated(Request $request): JsonResponse
  {
    $language = $request->language;
    App::setLocale($language);

    try {
      $request->session()->regenerate();

      // Get the authenticated customer
      $customer = Auth::guard('customer')->user();

      return response()->json($customer, 200);
    } catch (\Exception $e) {
      return response()->json([
        'message' => __('auth.failed'),
      ], 401);
    }
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): JsonResponse
  {
    Auth::guard('customer')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return response()->json(null, 204);
  }
}
