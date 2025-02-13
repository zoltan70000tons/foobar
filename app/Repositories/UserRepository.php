<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\SurvivorNumber;

class UserRepository
{
  public function getCurrentUser()
  {
    return Auth::user();
  }

  /*
  |--------------------------------------------------------------------------
  | Count users by email.
  |--------------------------------------------------------------------------
  |
  |  This method is used to check if the user already exists in the database.
  |  Used with Customers.
  |
  */
  public function countUsersByEmail(string $email): int
  {
    return User::where('email', $email)->whereHas('roles', fn($query) => $query->where('name', 'Customer'))->count();
  }

  /*
  |--------------------------------------------------------------------------
  | Count activated users by email.
  |--------------------------------------------------------------------------
  |
  |  This method is used to validate if there's more than one activated user with the same email in the database.
  |  Used with Customers.
  |
  */
  public function countActivatedUsersByEmail(string $email): int
  {
    return User::where('email', $email)
      ->whereNotNull('user_activated_at') // Only activated users
      ->count();
  }

  /*
  |--------------------------------------------------------------------------
  | Get all users with the same email.
  |--------------------------------------------------------------------------
  |
  | This method is used to get all users with the same email.

  |
  */
  public function getUsersByEmail(string $email)
  {
    return User::where('email', $email)->whereHas('roles', fn($query) => $query->where('name', 'Customer'))->get();
  }

  /*
    |--------------------------------------------------------------------------
    | Update Duplicate Emails with Prefix
    |--------------------------------------------------------------------------
    |
    | When a user claims an email, this method updates the emails of other users
    | with the same email by adding a prefix: `{original_email}+{survivor_number}`.
    |
    */
  public function updateDuplicateEmails(string $email, string $claimedUserId)
  {
    DB::beginTransaction();

    try {
      // Find all users with the same email (excluding the one that claimed it)
      $duplicateUsers = User::where('email', $email)
        ->where('id', '!=', $claimedUserId)
        ->get();

      foreach ($duplicateUsers as $duplicateUser) {
        // Get the user's survivor number
        $survivor = SurvivorNumber::where('user_id', $duplicateUser->id)->first();
        if ($survivor) {
          $prefixedEmail = $email . '+' . $survivor->survivor_number;

          // Update user email with the prefixed version
          $duplicateUser->update(['email' => $prefixedEmail]);
        }
      }

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error updating duplicate emails: ' . $e->getMessage());
    }
  }
}
