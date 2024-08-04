<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;


class AuthenticatedSessionController extends Controller
{
  /**
   * Handle an incoming authentication request.
   */
  public function store(LoginRequest $request): JsonResponse
  {

    $language = $request->language;
    App::setLocale($language);

    try {
      $request->authenticate();
      $request->session()->regenerate();

      return response()->json(null, 204);
    } catch (\Exception $e) {
      return response()->json([
        'message' => __('auth.failed'),
      ], 401);
    }
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): Response
  {
    Auth::guard('web')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return response()->noContent();
  }
}
