<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use App\Models\User;
//use App\Models\Customer;
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
use App\Helpers\CustomerHelper;
use App\Models\SurvivorNumber;

class CustomerRegisteredController extends Controller
{


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
          'username' => ['required', 'string', 'max:255', 'unique:users'],
          'email' => ['unique:users', 'required', 'string', 'lowercase', 'email', 'max:255'],
          'password' => ['required', 'confirmed', Rules\Password::defaults()],
      ]);

      $language = $request->language;
      App::setLocale($language);

      DB::beginTransaction();

      try {
          // Create the user
          $user = User::create([
              'username' => $request->username,
              'email' => $request->email,
              'password' => Hash::make($request->string('password')),
          ]);

          // Assign the customer role
          setPermissionsTeamId(1);
          $user->assignRole('Customer');

          // Generate a unique numeric survivor number and store it
          $survivorNumber = CustomerHelper::generateSurvivorNumber();
          SurvivorNumber::create([
              'user_id' => $user->id,
              'survivor_number' => $survivorNumber,
          ]);

          // Trigger event for user registration
          event(new Registered($user));

          DB::commit();

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
   * @param User $user
   * @param string $language
   * @return void
   */
  protected function sendWelcomeEmail(User $user, string $language): void
  {
    try {
      $email = $user->email;
      Mail::to($email)->send(new CustomerRegistered($user, $language));
    } catch (\Exception $e) {
      Log::error('Failed to send welcome email to user ID ' . $user->id . ': ' . $e->getMessage());
    }
  }
}
