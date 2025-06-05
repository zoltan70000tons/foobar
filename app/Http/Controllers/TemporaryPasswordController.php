<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TemporaryPassword;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\UserLog;

class TemporaryPasswordController extends Controller
{

  /*
  |--------------------------------------------------------------------------
  | Create Temporary Password
  |--------------------------------------------------------------------------
  |
  |  In this method we will create a temporary password for a customer.
  |  password will be valid for 20 minutes, then will be deleted by scheduler
  |
  */
  public function create(Request $request)
  {
    $request->validate([
      'customer_id' => 'required|exists:users,id',
    ]);

    $randomPassword = Str::random(12);

    TemporaryPassword::create([
      'customer_id' => $request->customer_id,
      'generated_by' => Auth::id(),
      'temporary_password' => Hash::make($randomPassword),
      'expires_at' => Carbon::now()->addMinutes(20),
    ]);

    // log the action
    UserLog::create([
      'author_id' => Auth::id(),
      'customer_id' => $request->customer_id,
      'action' => 'Agent created temporary password for customer',
      'description' => 'Temporary password created successfully for customer ID: ' . $request->customer_id,
    ]);

    // return response json
    return response()->json([
      'message' => 'Temporary password created successfully.',
      'raw_password' => $randomPassword,
    ], 201);
  }

  // manualy delete a temporary password
  public function delete($userId)
  {
    // delete all temporary passwords related to the customer

    TemporaryPassword::where('customer_id', $userId)->delete();
   
    return response()->json([
      'message' => 'Temporary passwords deleted successfully.',
    ], 200);
  }

}
