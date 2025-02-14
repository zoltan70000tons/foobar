<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\SurvivorNumber;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerRegistered;
use App\Helpers\CustomerHelper;

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
   *       name,
   *       middlename,
   *       surname,
   *       date_of_birth,
   *       country,
   *       gender,
   *       email,
   *       password,
   *       password_confirmation,
   *       language
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'middlename' => ['nullable', 'string', 'max:255'],
      'surname' => ['required', 'string', 'max:255'],
      'date_of_birth' => ['required', 'date'],
      'country' => ['required', 'string', 'max:255'],
      'gender' => ['required', 'string', 'max:255'],
      'email' => ['unique:users', 'required', 'string', 'lowercase', 'email', 'max:255'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // make sure email is lowercase
    $email = strtolower($request->email);
    $request->merge(['email' => $email]);

    // set language
    $language = $request->language;
    App::setLocale($language);

    DB::beginTransaction();

    try {
      // Create the user
      $user = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->string('password')),
      ]);

      // Create the user details
      $user->detail()->create([
        'first_name' => $request->name,
        'middle_name' => $request->middlename,
        'last_name' => $request->surname,
        'dob' => $request->date_of_birth,
        'gender' => $request->gender,
        'language' => $language,
        'citizenship' => $request->country,
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

      // Send a welcome email to the customer
      $this->sendWelcomeEmail($user, $language, $survivorNumber);

      return response()->json(
        [
          'message' => __('auth.account_created'),
        ],
        204
      );
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('User registration failed: ' . $e->getMessage());
      return response()->json(['message' => 'User registration failed'], 500);
    }
  }

  /**
   *
   *
   */
  public function storeUserSurvivor(Request $request): JsonResponse
  {
    $request->validate([
      'survivor_number' => ['required', 'string', 'max:9'],
      'name' => ['required', 'string', 'max:255'],
      'last_name' => ['required', 'string', 'max:255'],
      'date_of_birth' => ['required', 'date'],
      'email' => ['unique:users', 'required', 'string', 'lowercase', 'email', 'max:255'],
      'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // set language
    $language = $request->language;
    App::setLocale($language);

    // check the survivor number exist in the database
    $survivorNumber = $request->survivor_number;
    $first_name = strtoupper($request->name);
    $last_name = strtoupper($request->last_name);

    $survivor = SurvivorNumber::where('survivor_number', $survivorNumber)->first();

    if (!$survivor) {
      return response()->json(['message' => 'Survivor Number not found.'], 404);
    }

    // get user details from the survivor number
    $userDetail = UserDetail::where('user_id', $survivor->user_id)->first();

    // if user details not found return error
    if (!$userDetail) {
      return response()->json(['message' => 'User details not found.'], 404);
    }

    // check date of birth is equal to dob and name to first_name
    if ($userDetail->dob !== $request->date_of_birth || $userDetail->first_name !== $first_name || $userDetail->last_name !== $last_name) {
      return response()->json(['message' => 'The information provided does not match our records, please make sure you are entering the correct information.'], 404);
    }

    // if is correct get the user and set the email and new password
    $user = User::find($survivor->user_id);

    $user->update([
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);

    // Send a welcome email to the customer
    $this->sendWelcomeEmail($user, $language, $survivorNumber);

    return response()->json(['message' => 'Account updated successfully.'], 200);
  }

  /**
   * Send a welcome email to the customer.
   *
   * @param User $user
   * @param string $language
   * @param string $survivorNumber
   * @return void
   */
  protected function sendWelcomeEmail(User $user, string $language, string $survivorNumber): void
  {
    try {
      $email = $user->email;
      Mail::to($email)->send(new CustomerRegistered($user, $language, $survivorNumber));
    } catch (\Exception $e) {
      Log::error('Failed to send welcome email to user ID ' . $user->id . ': ' . $e->getMessage());
    }
  }
}
