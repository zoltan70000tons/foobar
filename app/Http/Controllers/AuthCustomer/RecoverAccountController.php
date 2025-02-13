<?php

namespace App\Http\Controllers\AuthCustomer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\SurvivorNumber;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rules;
use App\Rules\UniqueActivatedEmail;
use App\Repositories\UserRepository;

class RecoverAccountController extends Controller
{
  // protected UserRepository $userRepository;
// public function __construct(UserRepository $userRepository)
// {
//   $this->userRepository = $userRepository;
// }
// /**
//  * Show the recover form
//  *
//  * @param  \Illuminate\Http\Request  $request
//  */
// public function showRecoverForm(Request $request)
// {
//   // Validate the signed URL
//   if (!$request->hasValidRelativeSignature()) {
//     return response()->json(['message' => 'Invalid or expired link.'], 403);
//   }
//   // Return a response prompting the user to enter their survivor number
//   return response()->json(
//     [
//       'message' => 'Please enter your survivor number to proceed.',
//     ],
//     200
//   );
// }
// /**
//  * Verify the survivor number
//  *
//  * @param  \Illuminate\Http\Request  $request
//  * @param  string  $email
//  */
// public function recoverAccountVerify(Request $request)
// {
//   // Validate the signed URL
//   if (!$request->hasValidRelativeSignature()) {
//     return response()->json(['message' => 'Invalid or expired link.'], 403);
//   }
//   $request->validate([
//     'survivor_number' => 'required|numeric',
//   ]);
//   $survivorNumber = $request->input('survivor_number');
//   // Retrieve and decrypt the stored user IDs from the session
//   try {
//     $encryptedUserIds = $request->session()->get('encrypted_user_ids');
//     if (!$encryptedUserIds) {
//       return response()->json(['message' => 'Session expired. Please restart the recovery process.'], 403);
//     }
//     $userIds = json_decode(Crypt::decryptString($encryptedUserIds), true);
//     $survivor = SurvivorNumber::where('survivor_number', $survivorNumber)->whereIn('user_id', $userIds)->first();
//     if (!$survivor) {
//       return response()->json(['message' => 'Invalid survivor number for this group.'], 404);
//     }
//     // forget session encrypted_user_ids and crypt single survivor number and store in session
//     $request->session()->forget('encrypted_user_ids');
//     $request->session()->put('recover_survivor_number', Crypt::encryptString($survivorNumber));
//     return response()->json(
//       [
//         'message' => 'Survivor number verified. Please proceed to update your email, username, and password.',
//         'survivor_number' => $survivorNumber,
//       ],
//       200
//     );
//   } catch (\Exception $e) {
//     return response()->json(['message' => 'An error occurred while processing your request.'], 500);
//   }
// }
// /**
//  * Recover the account
//  *
//  * @param  \Illuminate\Http\Request  $request
//  * @param  string  $email
//  */
// public function recoverAccount(Request $request)
// {
//   // Validate the signed URL
//   if (!$request->hasValidRelativeSignature()) {
//     return response()->json(['message' => 'Invalid or expired link.'], 403);
//   }
//   $request->validate([
//     'survivor_number' => ['required'],
//     'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new UniqueActivatedEmail()],
//     'password' => ['required', 'confirmed', Rules\Password::defaults()],
//   ]);
//   $survivorNumber = $request->input('survivor_number');
//   $newEmail = $request->input('email');
//   $newPassword = $request->input('password');
//   // Retrieve and decrypt the stored survivor number from the session
//   $recoverSurvivorNumber = $request->session()->get('recover_survivor_number');
//   if (!$recoverSurvivorNumber) {
//     return response()->json(['message' => 'Session expired. Please restart the recovery process.'], 403);
//   }
//   // check the input survivor number with the one stored in the session
//   if (Crypt::decryptString($recoverSurvivorNumber) !== $survivorNumber) {
//     return response()->json(['message' => 'Invalid survivor number.'], 404);
//   }
//   DB::beginTransaction();
//   try {
//     // Find the survivor number
//     $survivor = SurvivorNumber::where('survivor_number', $survivorNumber)->first();
//     if (!$survivor) {
//       return response()->json(['message' => 'Invalid survivor number.'], 404);
//     }
//     $user = User::find($survivor->user_id);
//     // Assign the customer role
//     setPermissionsTeamId(1);
//     $user->assignRole('Customer');
//     // Update the claiming user
//     $user->update([
//       'email' => $newEmail,
//       'username' => null,
//       'password' => Hash::make($newPassword),
//       'user_activated_at' => now(),
//       'email_verified_at' => now(),
//     ]);
//     // Update duplicate emails using the repository method
//     $this->userRepository->updateDuplicateEmails($newEmail, $user->id);
//     // forget session encrypted_user_ids
//     $request->session()->forget('encrypted_user_ids');
//     DB::commit();
//     return response()->json(['message' => 'Account updated successfully.'], 200);
//   } catch (\Exception $e) {
//     DB::rollBack();
//     Log::error('Error updating account: ' . $e->getMessage());
//     return response()->json(['error' => 'User registration failed'], 500);
//   }
// }
}
