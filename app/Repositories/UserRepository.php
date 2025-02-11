<?php
namespace App\Repositories;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
}
