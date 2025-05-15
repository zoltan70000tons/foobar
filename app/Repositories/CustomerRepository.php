<?php

  namespace App\Repositories;

  use App\Helpers\CustomerHelper;
  use App\Http\Requests\CustomerRequest;
  use App\Interfaces\CustomerInterface;
  use App\Models\Booking;
  use App\Models\Comment;
  use App\Models\CustomerAddress;
  use App\Models\SurvivorNumber;
  use App\Models\User;
  use App\Models\UserComment;
  use App\Models\UserDetail;
  use App\Models\UserLog;
  use App\Models\UserTag;
  use Exception;
  use Illuminate\Contracts\Pagination\LengthAwarePaginator;
  use Illuminate\Database\Eloquent\Collection as DBCollection;
  use Illuminate\Support\Collection;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\Log;
  use Illuminate\Support\Str;
  use InvalidArgumentException;
  use Throwable;

  class CustomerRepository implements CustomerInterface
  {
    function getAll()
    {
      // return User::all();
    }

    function find($id)
    {
      return User::find($id);
    }

    function save(array $data): ?User
    {
      return new User();
    }

    /**
     * @param CustomerRequest $request
     * @param User $user
     * @throws Throwable
     * @property UserDetail|null $detail
     */
    function update(CustomerRequest $request, User $user)
    {
      try {
        DB::beginTransaction();

        $customerData = [];

        if ($request->filled('first_name')) {
          $user->detail->first_name = $request->input('first_name');
          $customerData['first_name'] = $request->input('first_name');
        }

        if ($request->filled('last_name')) {
          $user->detail->last_name = $request->input('last_name');
            $customerData['last_name'] = $request->input('last_name');
        }

        if ($request->filled('middle_name')) {
          $user->detail->middle_name = $request->input('middle_name');
            $customerData['middle_name'] = $request->input('middle_name');
        }

        if ($request->filled('email')) {
          $user->email = $request->input('email');
            $customerData['email'] = $request->input('email');
        }

        if ($request->filled('username')) {
          $user->username = $request->input('username');
            $customerData['username'] = $request->input('username');
        }

        if ($request->filled('gender')) {
          $user->detail->gender = $request->input('gender');
            $customerData['gender'] = $request->input('gender');
        }

        if ($request->filled('citizenship')) {
          $user->detail->citizenship = $request->input('citizenship');
            $customerData['citizenship'] = $request->input('citizenship');
        }

        if ($request->filled('phone')) {
          $user->detail->phone = $request->input('phone');
            $customerData['phone'] = $request->input('phone');
        }

        if ($request->filled('emergency_c_name')) {
          $user->detail->emergency_c_name = $request->input('emergency_c_name');
            $customerData['emergency_c_name'] = $request->input('emergency_c_name');
        }

        if ($request->filled('emergency_c_phone')) {
          $user->detail->emergency_c_phone = $request->input('emergency_c_phone');
            $customerData['emergency_c_phone'] = $request->input('emergency_c_phone');
        }

        if ($request->filled('language')) {
          $user->detail->language = $request->input('language');
            $customerData['language'] = $request->input('language');
        }

        if ($request->filled('dob')) {
          $timestampDOB = strtotime($request->input('dob'));
          $user->detail->dob = date('Y-m-d', $timestampDOB);
            $customerData['dob'] = date('Y-m-d', $timestampDOB);
        }

        $customerAddressData = [];

        if ($request->filled('address_first')) {
          $customerAddressData['address_first'] = $request->input('address_first');
        }

        if ($request->filled('address_second')) {
          $customerAddressData['address_second'] = $request->input('address_second');
        }

        if ($request->filled('city')) {
          $customerAddressData['city'] = $request->input('city');
        }

        if ($request->filled('state')) {
          $customerAddressData['state'] = $request->input('state');
        }

        if ($request->filled('postal_code')) {
          $customerAddressData['postal_code'] = $request->input('postal_code');
        }

        if ($request->filled('country')) {
          $customerAddressData['country'] = $request->input('country');
        }

        // Update or create the customer address for the user
        $user->customerAddress()->updateOrCreate(
          ['user_id' => $user->id], // Match by user_id
          $customerAddressData,
        );

        $user->save();
        $user->detail->save();
        $user->customerAddress->save();
        $user->save();

          UserLog::create([
              'customer_id' => $user->id,
              'author_id' => Auth::id(),
              'action' => 'User updated',
              'description' => json_encode($customerData),
          ]);

          UserLog::create([
              'customer_id' => $user->id,
              'author_id' => Auth::id(),
              'action' => 'Customer address updated',
              'description' => json_encode($customerAddressData),
          ]);

        DB::commit();
      } catch (Exception $e) {
        DB::rollBack();
        dd($e->getMessage());
      }
    }

    /**
     * @param CustomerRequest $request
     * @throws Throwable
     * @property UserDetail|null $detail
     */
    function store(CustomerRequest $request)
    {
      try {
        DB::beginTransaction();

        $user = new User();
        $user->email = $request->input('email');
        $user->username = $request->input('username');
        $user->password = bin2hex(random_bytes(16)); //Something must be saved
        $user->save();

        $userDetail = new UserDetail();
        $userDetail->first_name = $request->input('first_name');
        $userDetail->last_name = $request->input('last_name');
        $userDetail->gender = $request->input('gender');
        $userDetail->citizenship = $request->input('citizenship');
        $userDetail->phone = $request->input('phone');
        $userDetail->emergency_c_name = $request->input('emergency_c_name');
        $userDetail->emergency_c_phone = $request->input('emergency_c_phone');
        $userDetail->language = $request->input('language');
        $userDetail->middle_name = $request->input('middle_name');
        $timestampDOB = strtotime($request->input('dob'));
        $userDetail->dob = date('Y-m-d', $timestampDOB);

        $customerAddress = new CustomerAddress();
        $customerAddress->address_first = $request->input('address_first');
        $customerAddress->city = $request->input('city');
        $customerAddress->postal_code = $request->input('postal_code');
        $customerAddress->country = $request->input('country');
        $customerAddress->address_second = $request->input('address_second');
        $customerAddress->state = $request->input('state');

        $user->detail()->save($userDetail);
        $user->customerAddress()->save($customerAddress);

        SurvivorNumber::create([
          'user_id' => $user->id,
          'survivor_number' => CustomerHelper::generateSurvivorNumber(),
        ]);

        setPermissionsTeamId(1);
        $user->assignRole('Customer');
        $user->save();

          UserLog::create([
              'customer_id' => $user->id,
              'author_id' => Auth::id(),
              'action' => 'User created',
          ]);

        DB::commit();
      } catch (Exception $e) {
        DB::rollBack();
      }
    }

    function delete(User $user): void
    {
      // Anonymize email to prevent duplicate uniqueness constraint issues
      $user->update([
        'email' => 'deleted_' . $user->id . '@example.test',
        'email_verified_at' => null,
        'password' => bcrypt(Str::random(32)), // Securely randomize password
        'remember_token' => null,
      ]);

      // Anonymize user details
      if ($user->detail) {
        $user->detail()->update([
          'first_name' => 'Deleted',
          'middle_name' => null,
          'last_name' => 'User',
          'dob' => null,
          'phone' => null,
          'avatar' => null,
          'emergency_c_name' => null,
          'emergency_c_phone' => null,
        ]);
      }

        UserLog::create([
            'customer_id' => $user->id,
            'author_id' => Auth::id(),
            'action' => 'User deleted',
        ]);
    }

    function getAllCustomerData(int $perPage = 50): LengthAwarePaginator
    {
      return User::with(['detail', 'customerAddress', 'survivorNumber', 'membershipTypes'])
        ->whereIn('id', function ($query) {
          $query->select('model_has_roles.model_id')
            ->from('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'Customer');
        })
        ->paginate($perPage)
        ->through(function ($user) {
          return [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->detail->first_name,
            'last_name' => $user->detail->last_name,
            'dob' => $user->detail->dob,
            'survivor_number' => $user->survivorNumber->survivor_number,
            'membership_type' => optional($user->membershipTypes->first())->name,
          ];
        });
    }

    function getPaginatedCustomerData($page, $perPage, $sortBy, $sortDir, $filters): LengthAwarePaginator
    {
      $sortableFields = [
        'first_name' => 'detail.first_name',
        'last_name' => 'detail.last_name',
        'dob' => 'detail.dob',
        'survivor_number' => 'sn.survivor_number',
        'membership_type' => 'mt.name',
      ];

      $orderBy = $sortableFields[$sortBy] ?? 'u.email';

      $baseQuery = DB::table('users as u')
        ->leftJoin('user_details as detail', 'u.id', '=', 'detail.user_id')
        ->leftJoin('survivor_numbers as sn', 'u.id', '=', 'sn.user_id')
        ->leftJoin('memberships as m', 'u.id', '=', 'm.user_id')
        ->leftJoin('membership_types as mt', 'm.membership_id', '=', 'mt.id')
        ->whereIn('u.id', function ($query) {
          $query->select('model_id')
            ->from('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'Customer');
        });

      $filters = array_filter($filters);

      foreach ($filters as $key => $value) {
        if ($key === 'dob') {
          $baseQuery->whereRaw("TO_CHAR(detail.dob, 'YYYY-MM-DD') ILIKE ?", ["%{$value}%"]);
        } else {
          $columnMap = [
            'first_name' => 'detail.first_name',
            'last_name' => 'detail.last_name',
            'email' => 'u.email',
            'survivor_number' => 'sn.survivor_number',
            'membership_type' => 'mt.name',
          ];

          if (isset($columnMap[$key])) {
            $baseQuery->whereRaw("{$columnMap[$key]} ILIKE ?", ["%{$value}%"]);
          }
        }
      }

      return $baseQuery
        ->orderBy($orderBy, $sortDir)
        ->paginate($perPage, ['u.id as user_id', 'u.email', 'detail.first_name', 'detail.last_name', 'detail.dob', 'sn.survivor_number', 'mt.name'])
        ->through(fn($user) => [
          'id' => $user->user_id,
          'email' => $user->email,
          'first_name' => $user->first_name,
          'last_name' => $user->last_name,
          'dob' => $user->dob,
          'survivor_number' => $user->survivor_number ?? null,
          'membership_type' => $user->name ?? null,
        ]);
    }


    function getBookingDataForCustomer(User $user): DBCollection|Collection
    {
      return Booking::with(['event', 'cabin.cabinType', 'cabin.category'])
        ->where('customer_id', $user->id)
        ->get()
        ->map(function ($booking) {
          return [
            'booking_id' => $booking->id,
            'cabin_type' => optional($booking->cabin?->cabinType)->cabin_type,
            'event_name' => $booking->event->name,
            'booking_code' => $booking->booking_code,
            'category_full_title' => optional($booking->cabin?->category)->getTitleAttribute(),
          ];
        });
    }

      public function addComment(User $customer, string $comment): bool
      {
          try {
              if (empty($comment)) {
                  throw new InvalidArgumentException('The comment field cannot be empty.');
              }
              $sanitizedComment = htmlspecialchars(strip_tags($comment));
              $formattedComment = ucfirst($sanitizedComment);
              UserComment::create([
                  'customer_id' => $customer->id,
                  'author_id' => Auth::id(),
                  'comment' => $formattedComment,
              ]);

              UserLog::create([
                  'customer_id' => $customer->id,
                  'author_id' => Auth::id(),
                  'action' => 'New comment added',
                  'description' => $formattedComment,
              ]);

              return true;
          } catch (\Exception $e) {
              Log::info($e);
              return false;
          }
      }

      function addTags(User $customer, array $tags)
      {
          try {
              if (!is_array($tags)) {
                  throw new InvalidArgumentException('Tags must be an array.');
              }
              $uniqueTags = array_unique($tags);
              $originalTags = $customer->tags;
              $originalTagIds = $originalTags->pluck('id')->toArray();

              $customer->tags()->sync($uniqueTags);

              $user = Auth::user();

              UserLog::create([
                  'customer_id' => $customer->id,
                  'author_id' => $user->id,
                  'action' => 'Tags updated on user',
                  'description' => 'The original tags were: ' .json_encode($originalTagIds). ', and the new tags are: '
                      . json_encode($uniqueTags),
              ]);

              return true;
          } catch (\Throwable $e) {
              \Log::error("Failed to update tags for customer ID {$customer->id}: {$e->getMessage()}");
              throw $e;
          }
      }

      function deleteComment(User $customer, int $commentId)
      {
          try {
              UserComment::query()
                  ->where('customer_id', '=', $customer->id)
                  ->where('id', '=', $commentId)
                  ->delete();

              $user = Auth::user();
              UserLog::create([
                  'customer_id' => $customer->id,
                  'author_id' => $user->id,
                  'action' => 'Comment deleted from user',
                  'description' => 'Comment ID: ' . $commentId,
              ]);
          } catch (\Throwable $e) {
              \Log::error("Failed to delete comment from customer ID {$customer->id}: {$e->getMessage()}");
              throw $e;
          }
      }
  }
