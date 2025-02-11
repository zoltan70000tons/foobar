<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;

class EmailUniquenessService
{
  protected UserRepository $userRepository;

  public function __construct(UserRepository $userRepository)
  {
    $this->userRepository = $userRepository;
  }

  /*
  |--------------------------------------------------------------------------
  | Handle email uniqueness
  |--------------------------------------------------------------------------
  |
  |  If more user have the same email, we will return a response with a signed URL
  |  Becuase, page for recover account is visible for non auth user.
  |  We have to add some layer of security to prevent forcing recover accounts. 
  |  We send 3 signed URLs to the user to recover the account.
  |   
  | 
  |  1. Get signed URL ( validate recover account form )
  |  2. Post signed URL ( verify recover account form )
  |  3. Register signed URL ( register new account )
  |
  */
  public function handleEmailUniqueness(string $email): ?JsonResponse
  {
    $usersWithEmailCount = $this->userRepository->countUsersByEmail($email);

    if ($usersWithEmailCount > 1) {
      // get all users with the same email, and store ids encrypted in the session
      $users = $this->userRepository->getUsersByEmail($email);

      $encryptedIds = Crypt::encryptString(json_encode($users->pluck('id')));

      // always keep latests encrypted ids in session
      session(['encrypted_user_ids' => $encryptedIds]);
      session()->save();

      $getSignedURL = URL::temporarySignedRoute('recover.account.form', Carbon::now()->addMinutes(15), [], false);

      $postSignedURL = URL::temporarySignedRoute('recover.account.verify', Carbon::now()->addMinutes(15), [], false);

      $registerSignedUrl = URL::temporarySignedRoute(
        'recover.account.register',
        Carbon::now()->addMinutes(15),
        [],
        false
      );

      return response()->json(
        [
          'status' => 'error-uniqueness',
          'message' => 'Multiple accounts found with this email. Please recover your account.',
          'get_signed_url' => $getSignedURL,
          'post_signed_url' => $postSignedURL,
          'register_signed_url' => $registerSignedUrl,
        ],
        400
      );
    }

    return null;
  }
}
