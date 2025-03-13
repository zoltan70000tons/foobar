<?php

  namespace App\Repositories;

  use App\Helpers\CustomerHelper;
  use App\Http\Requests\CustomerRequest;
  use App\Interfaces\CustomerInterface;
  use App\Models\Booking;
  use App\Models\CustomerAddress;
  use App\Models\SurvivorNumber;
  use App\Models\User;
  use App\Models\UserDetail;
  use Exception;
  use Illuminate\Contracts\Pagination\LengthAwarePaginator;
  use Illuminate\Database\Eloquent\Collection as DBCollection;
  use Illuminate\Support\Collection;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Str;
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

        if ($request->filled('first_name')) {
          $user->detail->first_name = $request->input('first_name');
        }

        if ($request->filled('last_name')) {
          $user->detail->last_name = $request->input('last_name');
        }

        if ($request->filled('middle_name')) {
          $user->detail->middle_name = $request->input('middle_name');
        }

        if ($request->filled('email')) {
          $user->email = $request->input('email');
        }

        if ($request->filled('gender')) {
          $user->detail->gender = $request->input('gender');
        }

        if ($request->filled('citizenship')) {
          $user->detail->citizenship = $request->input('citizenship');
        }

        if ($request->filled('phone')) {
          $user->detail->phone = $request->input('phone');
        }

        if ($request->filled('emergency_c_name')) {
          $user->detail->emergency_c_name = $request->input('emergency_c_name');
        }

        if ($request->filled('emergency_c_phone')) {
          $user->detail->emergency_c_phone = $request->input('emergency_c_phone');
        }

        if ($request->filled('language')) {
          $user->detail->language = $request->input('language');
        }

        if ($request->filled('dob')) {
          $timestampDOB = strtotime($request->input('dob'));
          $user->detail->dob = date('Y-m-d', $timestampDOB);
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

        DB::commit();
      } catch (Exception $e) {
        DB::rollBack();
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
  }
