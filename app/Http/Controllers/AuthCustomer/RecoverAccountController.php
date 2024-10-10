<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\SurvivorNumber;
use Illuminate\Validation\Rules;

class RecoverAccountController extends Controller
{

    /**
     * Show the recover form
     * 
     * @param  \Illuminate\Http\Request  $request
     */
    public function showRecoverForm(Request $request)
    {
        // Validate the signed URL
        if (! $request->hasValidRelativeSignature()) {
            return response()->json(['message' => 'Invalid or expired link.'], 403);
        }
    
        // Return a response prompting the user to enter their survivor number
        return response()->json([
            'message' => 'Please enter your survivor number to proceed.',
        ], 200);
    }

    /**
     * Verify the survivor number
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $email
     */
    public function recoverAccountVerify(Request $request)
    {
        // Validate the signed URL
        if (! $request->hasValidRelativeSignature()) {
            return response()->json(['message' => 'Invalid or expired link.'], 403);
        }
    
        $request->validate([
            'survivor_number' => 'required|numeric',
        ]);
    
        $survivorNumber = $request->input('survivor_number');
    
        // Find the survivor number and verify it matches the email
        $survivor = SurvivorNumber::where('survivor_number', $survivorNumber)->first();
    
        if (!$survivor) {
            return response()->json(['message' => 'Invalid survivor number.'], 404);
        }
    
        $user = User::find($survivor->user_id);
    
        if (!$user) {
            return response()->json(['message' => 'There is no user with this survivor number.'], 404);
        }
    
        return response()->json([
            'message' => 'Survivor number verified. Please proceed to update your email, username, and password.',
            'survivor_number' => $survivorNumber,
        ], 200);
    }

    /**
     * Recover the account
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $email
     */
    public function recoverAccount(Request $request)
    {
        // Validate the signed URL
        if (! $request->hasValidRelativeSignature()) {
            return response()->json(['message' => 'Invalid or expired link.'], 403);
        }
    
        $request->validate([
            'survivor_number' => ['required', 'numeric'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['unique:users', 'required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    
        $survivorNumber = $request->input('survivor_number');
        $newEmail       = $request->input('email');
        $newUsername    = $request->input('username');
        $newPassword    = $request->input('password');
    
        DB::beginTransaction();
    
        try {
            // Find the survivor number
            $survivor = SurvivorNumber::where('survivor_number', $survivorNumber)->first();
    
            if (!$survivor) {
                return response()->json(['message' => 'Invalid survivor number.'], 404);
            }
    
            $user = User::find($survivor->user_id);

            // Assign the customer role
            setPermissionsTeamId(1);
            $user->assignRole('Customer');
    
            // Update user's email, username, and password
            $user->email    = $newEmail;
            $user->username = $newUsername;
            $user->password = Hash::make($newPassword);
            $user->save();
    
            DB::commit();
    
            return response()->json(['message' => 'Account updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating account: ' . $e->getMessage());
    
            return response()->json(['error' => 'User registration failed'], 500);
        }
    }
}
