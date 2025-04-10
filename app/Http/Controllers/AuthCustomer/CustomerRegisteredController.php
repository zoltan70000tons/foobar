<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\SurvivorNumber;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerRegistered;
use App\Mail\ActivateSurvivor;
use App\Helpers\CustomerHelper;
use App\Traits\StringNormalization;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewUserRegistered;

class CustomerRegisteredController extends Controller
{
  use StringNormalization;
  /*
  |--------------------------------------------------------------------------
  |  Store new customer
  |--------------------------------------------------------------------------
  |
  |  This method is responsible for storing a new customer.
  |
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

      //Log::info('User data', ['user' => $user->load('detail')]);

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
      Notification::route('slack', env('SLACK_BOOKING_ENGINE_NOTIFICATIONS'))->notify(new NewUserRegistered($user));

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

  /*
  |--------------------------------------------------------------------------
  |  Store survivor user
  |--------------------------------------------------------------------------
  |
  |  Difference between store and storeUserSurvivor is that storeUserSurvivor
  |  is used to update the user credentials of an existing survivor.
  |  We check if the provided information matches the stored information,
  |  and if it does, we update the user's email and password.
  |
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

    // Set language
    $language = $request->language;
    App::setLocale($language);

    // Normalize inputs
    $survivorNumber = $request->survivor_number;
    $inputFirstName = $this->normalizeString($request->name);
    $inputLastName = $this->normalizeString($request->last_name);
    $inputDob = $request->date_of_birth;

    // Fetch survivor
    $survivor = SurvivorNumber::where('survivor_number', $survivorNumber)->first();

    if (!$survivor) {
      return response()->json(['message' => 'Survivor Number not found.'], 404);
    }

    // Fetch user details
    $userDetail = UserDetail::where('user_id', $survivor->user_id)->first();

    if (!$userDetail) {
      return response()->json(['message' => 'User details not found.'], 404);
    }

    // Normalize stored values
    $storedFirstName = $this->normalizeString($userDetail->first_name);
    $storedLastName = $this->normalizeString($userDetail->last_name);
    $storedDob = $userDetail->dob;

    // Check similarity instead of strict equality
    $nameSimilarity = $this->isSimilar($inputFirstName, $storedFirstName);
    $lastNameSimilarity = $this->isSimilar($inputLastName, $storedLastName);

    if ($storedDob !== $inputDob || !$nameSimilarity || !$lastNameSimilarity) {
      return response()->json(
        [
          'message' =>
            'The information provided does not match our records, please make sure you are entering the correct information.',
        ],
        404
      );
    }

    // Update user credentials
    $user = User::find($survivor->user_id);

    if ($user->email !== null) {
      return response()->json(['message' => 'Account already activated.'], 400);
    }

    $user->update([
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);

    // Send welcome email
    $this->sendActivateSurvivorEmail($user, $language, $survivorNumber);

    return response()->json(['message' => 'Account updated successfully.'], 200);
  }

  /*
  |--------------------------------------------------------------------------
  | Send welcome email NEW USER
  |--------------------------------------------------------------------------
  |
  |  This email is send to the user after registration.
  |
  */
  protected function sendWelcomeEmail(User $user, string $language, string $survivorNumber): void
  {
    try {
      $user->load('detail');
      $email = $user->email;
      Mail::to($email)->queue(new CustomerRegistered($user, $language, $survivorNumber));
    } catch (\Exception $e) {
      Log::error('Failed to send welcome email to user ID ' . $user->id . ': ' . $e->getMessage());
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Send welcome email ACTIVATE SURVIVOR USER
  |--------------------------------------------------------------------------
  |
  |  This email is send, to confirm email address and activate the survivor.
  |
  */
  protected function sendActivateSurvivorEmail(User $user, string $language, string $survivorNumber): void
  {
    try {
      $email = $user->email;
      $user->load('detail');

      Mail::to($email)->queue(new ActivateSurvivor($user, $language, $survivorNumber));
    } catch (\Exception $e) {
      Log::error('Failed to send activate survivor email to user ID ' . $user->id . ': ' . $e->getMessage());
    }
  }
}
