<?php

namespace App\Repositories;

use App\Helpers\CustomerHelper;
use App\Http\Requests\CustomerCreateRequest;
use App\Http\Requests\CustomerRequest;
use App\Interfaces\CustomerInterface;
use App\Models\Booking;
use App\Models\CustomerAddress;
use App\Models\SurvivorNumber;
use App\Models\User;
use App\Models\UserDetail;
use Exception;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        $currentDob = $user->detail->dob ? explode('-', $user->detail->dob) : [null, null, null];

        DB::transaction(function () use ($user, $request, $currentDob) {
            if ($request->filled('first_name')) {
                $user->detail->first_name = $request->input('first_name');
            }

            if ($request->filled('last_name')) {
                $user->detail->last_name = $request->input('last_name');
            }

            if ($request->filled('middle_name')) {
                $user->detail->middle_name = $request->input('middle_name');
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

            $year = $currentDob[0];
            $month = $currentDob[1];
            $day = $currentDob[2];

            if ($request->filled('year')) {
                $year = $request->input('year');
            }

            if ($request->filled('month')) {
                $month = $request->input('month');
            }

            if ($request->filled('day')) {
                $day = $request->input('day');
            }

            if ($year !== $currentDob[0] || $month !== $currentDob[1] || $day !== $currentDob[2]) {
                $user->detail->dob = sprintf('%04d-%02d-%02d', $year, $month, $day);
            }

            if ($request->filled('address_first')) {
                $user->customerAddress->address_first = $request->input('address_first');
            }

            if ($request->filled('address_second')) {
                $user->customerAddress->address_second = $request->input('address_second');
            }

            if ($request->filled('city')) {
                $user->customerAddress->city = $request->input('city');
            }

            if ($request->filled('state')) {
                $user->customerAddress->state = $request->input('state');
            }

            if ($request->filled('postal_code')) {
                $user->customerAddress->postal_code = $request->input('postal_code');
            }

            if ($request->filled('country')) {
                $user->customerAddress->country = $request->input('country');
            }

            $user->detail->save();
            $user->customerAddress->save();
            $user->save();
        });
    }

    /**
     * @param CustomerCreateRequest $request
     * @throws Throwable
     * @property UserDetail|null $detail
     */
    function store(CustomerCreateRequest $request)
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
            $userDetail->middle_name = $request->filled('middle_name') ?? null;
            $userDetail->dob = sprintf('%04d-%02d-%02d', $request->input('year'), $request->input('month'), $request->input('day'));

            $customerAddress = new CustomerAddress();
            $customerAddress->address_first = $request->input('address_first');
            $customerAddress->city = $request->input('city');
            $customerAddress->postal_code = $request->input('postal_code');
            $customerAddress->country = $request->input('country');
            $customerAddress->address_second = $request->filled('address_second') ?? null;
            $customerAddress->state = $request->filled('state') ?? null;

            $user->detail()->save($userDetail);
            $user->customerAddress()->save($customerAddress);

            $survivorNumber = new SurvivorNumber();
            $survivorNumber->survivor_number = CustomerHelper::generateSurvivorNumber();
            $user->survivorNumber()->save($survivorNumber);

            setPermissionsTeamId(1);
            $user->assignRole('Customer');

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }

    function delete($id)
    {
    }

    function getAllCustomerData(): DBCollection|Collection
    {
        return User::with(['detail', 'customerAddress', 'survivorNumber', 'membershipTypes'])
            ->whereIn('id', function ($query) {
                $query->select('model_has_roles.model_id')
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'Customer');
            })
            ->get()
            ->map(function ($user) {
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

    function getBookingDataForCustomer(User $user): DBCollection|Collection
    {
        return Booking::with(['event', 'cabin.cabinType', 'cabin.category'])
            ->where('customer_id', $user->id)
            ->get()
            ->map(function ($booking) {
                return [
                    'booking_id' => $booking->id,
                    'cabin_type' => $booking->cabin->cabinType->cabin_type,
                    'event_name' => $booking->event->name,
                    'booking_code' => $booking->booking_code,
                    'category_full_title' => $booking->cabin->category->getTitleAttribute(),
                ];
            });
    }
}
