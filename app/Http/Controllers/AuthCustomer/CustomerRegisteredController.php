<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
// use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerRegistered;

class CustomerRegisteredController extends Controller
{

  // Generate a unique survival number
  private function generateUniqueSuperId(): string
  {
    do {
      // Generate a random survivor_number, e.g., 'SN#####' format
      $sn = 'SN' . str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
    } while (Customer::where('super_id', $sn)->exists());

    return $sn;
  }

  /**
   * Handle an incoming registration request.
   * 
   * @JG 07/08/2024
   * We use there DB::beginTransaction() and DB::commit() to wrap the user creation in a transaction.
   * It mean that if an exception is thrown during the user creation, the transaction will be rolled back and the user won't be created.
   * It will prevent the database from being in an inconsistent state.
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'name' => ['required', 'string', 'max:255', 'unique:customers'],
      'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $language = $request->language;
    App::setLocale($language);

    DB::beginTransaction();

    try {

      $survival_number = $this->generateUniqueSuperId();

      $user = Customer::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->string('password')),
        'survival_number' => $survival_number,
      ]);


      event(new Registered($user));

      DB::commit();

      // send email
      $this->sendWelcomeEmail($user, $language);


      return response()->json([
        'message' => __('auth.account_created'),
      ], 204);
    } catch (\Exception $e) {

      DB::rollBack();

      Log::error('User registration failed: ' . $e->getMessage());

      return response()->json(['error' => 'User registration failed'], 500);
    }
  }


  /**
   * Send a welcome email to the customer.
   *
   * @param Customer $user
   * @param string $language
   * @return void
   */
  protected function sendWelcomeEmail(Customer $user, string $language): void
  {
    try {
      $email = $user->email;
      Mail::to($email)->send(new CustomerRegistered($user, $language));
    } catch (\Exception $e) {
      Log::error('Failed to send welcome email to user ID ' . $user->id . ': ' . $e->getMessage());
    }
  }
}
