<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\PaymentLegacyEngineToken;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class PaymentLegacyEngineTokenController extends Controller
{
    public function initiate(Request $request)
    {
        $validated = $request->validate([
          'passengerFirstName' => 'required|string|max:255',
          'passengerLastName'  => 'required|string|max:255',
          'passengerEmail'     => 'required|email|max:255',
          'bookingCode'        => 'required|string|max:255',
          'amount'             => 'required|numeric|min:0.01',
          'lang'               => 'nullable|string|in:en,de,es',
        ]);

        $token = (string) Str::uuid();

        PaymentLegacyEngineToken::create([
            'token' => $token,
            'email' => $validated['passengerEmail'],
            'data' => $validated,
            'expires_at' => now()->addMinutes(15),
        ]);

        $query = http_build_query([
            'passengerFirstName' => $validated['passengerFirstName'],
            'passengerLastName'  => $validated['passengerLastName'],
            'passengerEmail'     => $validated['passengerEmail'],
            'bookingCode'        => $validated['bookingCode'],
            'amount'             => $validated['amount'],
            'MySubmitButton'     => 'Proceed to pay',
            'lang'               => $validated['lang'] ?? 'en',
            'token'              => $token,
        ]);

      return redirect()->away("https://70000tons.com/checkout?{$query}");

    }

    public function verify(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        $attempt = PaymentLegacyEngineToken::where('token', $token)
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->first();

        return response()->json(['valid' => (bool) $attempt]);
    }
}