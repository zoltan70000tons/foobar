<?php

namespace App\Http\Controllers;

use App\Models\PassengerInvitation;
use App\Models\UserLog;
use Illuminate\Http\Request;
use App\Models\Passenger;
use App\Models\Booking;
use App\Models\User;
use App\Repositories\PassengerRepository;
use App\Rules\UniqueSurvivorInEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Log;

class PassengerController extends Controller
{

    protected PassengerRepository $passengerRepository;
    public function __construct(PassengerRepository $passengerRepository)
    {
        $this->passengerRepository = $passengerRepository;
    }


    public function updateSeat(Request $request)
    {

        $event_id = request()->route("id");
        $booking = Booking::findOrFail($request->input('booking_id'));
        $validated = $request->validate([
            'id' => 'required|int',
            'booking_id' => 'required|int',
            'confirmed_booking_email' => 'required|boolean',
            'survivor_number' => ['nullable', 'string', 'regex:/^\d+$/', 'exists:survivor_numbers,survivor_number', new UniqueSurvivorInEvent($event_id, $request->booking_id),],
            Rule::in($booking->payment_plan === 'INSTALLMENTS'  ? ['CREDIT_CARD'] : ['CREDIT_CARD', 'BANK_TRANSFER']),
            'gender' => 'required|string|in:M,F,O',
            'first_name' => [
                'string',
                'max:13',
                'min:2',
                'required',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'middle_name' => [
                'string',
                'max:13',
                'nullable',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'last_name' => [
                'string',
                'max:18',
                'min:2',
                'required',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'dob' => [
                'required',
                'date',
            ],
            'citizenship' => [
                'required',
                'string',
                'min:2',
            ],
            'address_first' => [
                'string',
                'max:50',
                'required',
                'regex:/^[#.0-9a-zA-Z\s,-]+$/'
            ],
            'address_second' => [
                'string',
                'max:50',
                'nullable',
                'regex:/^[#.0-9a-zA-Z\s,-]+$/'
            ],
            'city' => [
                'string',
                'max:30',
                'required',
                'regex:/^[#.0-9a-zA-Z\s,-]+$/'
            ],
            'state' => [
                'string',
                'max:20',
                'nullable',
                'regex:/^[#.0-9a-zA-Z\s,-]+$/',
                Rule::requiredIf(function () {
                    return in_array(request('country'), ['CAN', 'USA']);
                }),
            ],
            'postal_code' => [
                'string',
                'max:10',
                'required',
                'regex:/^[A-Za-z0-9 -]+$/',
            ],
            'country' => 'required|string',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'not_regex:/[<>{}]/',
            ],
            'phone' => [
                'string',
                'required',
                'regex:/^\+?[0-9]{7,15}$/',
                'different:emergency_c_phone',
            ],
            'emergency_c_name' => [
                'string',
                'max:75',
                'required',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'emergency_c_phone' => [
                'string',
                'required',
                'regex:/^\+?[0-9]{7,15}$/',
                'different:phone',
            ],
            'special_request' => 'nullable|string',
            'hear_about' => 'nullable|string',
            // 'newsletter' => 'required|boolean',
            // 'travel_info' => 'required|boolean',
            'terms_n_cons' => 'required|boolean',
            // 'cabin_conf_accp' => 'required|boolean',
            //'single_t_agreement' => 'required|boolean',
            //'passenger_allocated_cost' => 'required|numeric',
            //'passenger_balance' => 'required|numeric',
            // 'was_on_board' => 'required|boolean',
        ]);
        $validated['empty_seat'] = false;


        $slot = Passenger::where('id', '=', $validated['id'])->first();

        Log::info($slot);

        if ($slot->booking_id !== $booking->id) {
            return response()->json([
                'error' => 'Slot and Booking Id inconsistent.',
            ], 422);
        }

        // Perform survivor number validation using the helper
        if (!empty($validated['survivor_number']) && $validated['survivor_number'] !== $slot->survivor_number) {
            if (Passenger::checkSurvivorInActiveBookings($validated['survivor_number'], $booking->event_id)) {
                return response()->json([
                    'error' => 'Passenger with this Survivor Number already exists in an active booking for the event.'
                ], 422);
            }
        }

        $this->passengerRepository->updateSeat($slot, $booking, $validated);
        $slot->update($validated);

        $customer = User::query()->where('email', '=', $slot['email'])->first();
        $user = Auth::user();
        if ($customer) {
            UserLog::create([
                'customer_id' => $customer->id,
                'author_id' => $user->id,
                'action' => 'User assigned to booking',
                'description' => 'Booking id: ' . $booking->id,
            ]);
        }

        return response()->json($slot);
    }

    /*
     |----------------------------------------------------------
     | Release Seat REQUEST
     | ---------------------------------------------------------
    */
    public function releaseSeat(Request $request)
    {
        $validated = $request->validate([
            'slotId' => 'required|int',
            'bookingId' => 'required|int'
        ]);

        $slotPassenger = Passenger::where('id', '=', $validated['slotId'])->first();
        $slot = $this->clearSlot($slotPassenger);

        if ($slotPassenger) {
            $customer = User::query()->where('email', '=', $slotPassenger->email)->first();
            $user = Auth::user();
            if ($customer) {
                UserLog::create([
                    'customer_id' => $customer->id,
                    'author_id' => $user->id,
                    'action' => 'User released from booking',
                    'description' => 'Booking id: ' . $validated['bookingId'],
                ]);
            }
        }

        return response()->json($slot);
    }

    /*
     |----------------------------------------------------------
     | Clear Slot
     | ---------------------------------------------------------
    */
    private function clearSlot($slot)
    {
        if (!$slot) {
            return response()->json('error', 422);
        }

        try {
            // $slot = Passenger::where('id', '=', $slot->id)->first();
            // ---- start @JG if passenger invitation exists, delete it
            $passengerInvitation = PassengerInvitation::where('passenger_id', $slot->id)->first();
            $passengerInvitationEmail = null;
            if ($passengerInvitation) {
                $passengerInvitationEmail = $passengerInvitation->email;
                $passengerInvitation->delete();
            }
            // ---- end @JG

            if ($slot) {
                $slot->confirmed_booking_email = false;
                $slot->lead_passenger = false;
                $slot->survivor_number = null;
                $slot->payment_method = 'CREDIT_CARD';
                $slot->gender = null;
                $slot->first_name = null;
                $slot->middle_name = null;
                $slot->last_name = null;
                $slot->dob = null;
                $slot->citizenship = null;
                $slot->address_first = null;
                $slot->address_second = null;
                $slot->city = null;
                $slot->state = null;
                $slot->postal_code = null;
                $slot->country = null;
                $slot->email = null;
                $slot->phone = null;
                $slot->emergency_c_name = null;
                $slot->emergency_c_phone = null;
                $slot->special_request = null;
                $slot->hear_about = null;
                $slot->newsletter = false;
                $slot->travel_info = false;
                $slot->terms_n_cons = false;
                $slot->cabin_conf_accp = false;
                $slot->single_t_agreement = false;
                // $slot->passenger_allocated_cost = 0;
                // $slot->passenger_balance = 0;
                $slot->was_on_board = false;
                $slot->empty_seat = false;
                //$slot->language = 'en'; //Cannot release seat of uncommented, language column does not exist
                // on passengers table
                $slot->save();

                if ($passengerInvitationEmail) {
                    $customer = User::query()->where('email', '=', $passengerInvitationEmail)->first();

                    if ($customer) {
                        $user = Auth::user();

                        UserLog::create([
                            'customer_id' => $customer->id,
                            'author_id' => $user->id,
                            'action' => 'User slot cleared',
                            'description' => '',
                        ]);
                    }
                }
            }

            return $slot;
        } catch (\Exception $e) {
            //dd($e->getMessage(). ' - ' . $e->getLine());
            Log::error($e->getMessage());
            return response()->json('error');
        }
    }

    /*
     |----------------------------------------------------------
     | Set or Unset Empty Seat
     | ---------------------------------------------------------
    */
    public function emptySeat(Request $request)
    {
        $validated = $request->validate([
            'slot_id' => 'required|int',
            'booking_id' => 'required|int',
            'empty_seat' => 'required|boolean'
        ]);

        $slot = Passenger::where('id', '=', $validated['slot_id'])->first();

        if (!$slot) {
            return response()->json('error', 422);
        }

        $passengerEmail = $slot->email;

        // $this->clearSlot($slot);
        // $slot->refresh();

        // If slot have first name, last name or dob then clear the slot
        if ($slot->first_name || $slot->last_name || $slot->dob) {
            $this->clearSlot($slot);
            $slot->refresh();
        }

        try {

            $slot->empty_seat = $validated['empty_seat'];
            $slot->save();

            if ($passengerEmail) {
                $customer = User::query()->where('email', '=', $passengerEmail)->first();

                if ($customer) {
                    $user = Auth::user();

                    UserLog::create([
                        'customer_id' => $customer->id,
                        'author_id' => $user->id,
                        'action' => 'User seat emptied',
                        'description' => '',
                    ]);
                }
            }

            return response()->json($slot);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json('error');
        }
    }

    /**
     * Search users by first_name, last_name, or email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3',
            'eventId' => 'nullable|integer|min:1',
            'bookingId' => 'nullable|integer|min:1',
        ]);

        $query = $request->get('query');

        $eventId = $request->get('eventId');

        try {
            $results = User::with(['detail', 'survivorNumber', 'customerAddress'])
                ->where('email', 'LIKE', "%{$query}%")
                ->orWhereHas('detail', function ($q) use ($query) {
                    $q->where('first_name', 'LIKE', "%{$query}%")
                        ->orWhere('last_name', 'LIKE', "%{$query}%");
                })
                ->get()
                ->map(function ($user) use ($eventId, $request) {
                    if ($eventId) {
                        //$bookingId = $request->get('bookingId');

                        $baseQuery = Booking::whereHas('passengers', function ($query) use ($user) {
                            $query->where('survivor_number', $user->survivorNumber?->survivor_number);
                        })
                            ->where('event_id', $eventId);

                        // If there is a bookingId, it means it's an EditPassenger call
                        //if ($bookingId) { //Zoltan: Commented it out for https://app.asana.com/1/1208601927370271/project/1208683268730907/task/1210395025088838
                            // Ensure the current booking is excluded in the double-booking check
                            //$baseQuery->where('id', '!=', $bookingId);  // Exclude current booking
                        //}

                        $exists = $baseQuery->where('status', '!=', 'CANCELLED')
                            ->exists();

                        // Set the has_booking flag
                        $has_booking = $exists;
                    } else {
                        // If eventId is not provided, skip double booking check
                        $has_booking = false;
                    }

                    return [
                        'id' => $user->id,
                        'email' => $user->email,
                        'first_name' => $user->detail->first_name ?? null,
                        'last_name' => $user->detail->last_name ?? null,
                        'middle_name' => $user->detail->middle_name ?? null,
                        'survivor_number' => $user->survivorNumber->survivor_number ?? null,
                        'confirmed_booking_email' => $user->detail->confirmed_booking_email ?? null,
                        'lead_passenger' => $user->detail->lead_passenger ?? null,
                        'payment_method' => $user->detail->payment_method ?? null,
                        'phone' => $user->detail->phone ?? null,
                        'address_first' => $user->customerAddress->address_first ?? null,
                        'address_second' => $user->customerAddress->address_second ?? null,
                        'city' => $user->customerAddress->city ?? null,
                        'state' => $user->customerAddress->state ?? null,
                        'postal_code' => $user->customerAddress->postal_code ?? null,
                        'country' => $user->customerAddress->country ?? null,
                        'citizenship' => $user->detail->citizenship ?? null,
                        'gender' => $user->detail->gender ?? null,
                        'dob' =>  $user->detail->dob ?? null,
                        'full_name' => ($user->detail->first_name ?? '') . ' ' . ($user->detail->last_name ?? ''),
                        'emergency_c_name' => $user->detail->emergency_c_name ?? null,
                        'emergency_c_phone' => $user->detail->emergency_c_phone ?? null,
                        'special_request' => $user->detail->special_request ?? null,
                        'hear_about' => $user->detail->hear_about ?? null,
                        'newsletter' => $user->detail->newsletter ?? null,
                        'travel_info' => $user->detail->travel_info ?? null,
                        'term_n_cons' => $user->detail->term_n_cons ?? null,
                        'cabin_conf_accp' => $user->detail->cabin_conf_accp ?? null,
                        'single_t_agreement' => $user->detail->single_t_agreement ?? null,
                        'passenger_allocated_cost' => $user->detail->passenger_allocated_cost ?? null,
                        'passenger_balance' => $user->detail->passenger_balance ?? null,
                        'was_on_board' => $user->detail->was_on_board ?? null,
                        'has_booking' => $has_booking,
                    ];
                });

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json('error');
        }
    }

    public function cancelPassengerInvitation(Request $request)
    {
        $validated = $request->validate([
            'passengerId' => 'required|int',
            'bookingId' => 'required|int'
        ]);

        try {
            PassengerInvitation::query()
                ->where('passenger_id', $validated['passengerId'])
                ->where('booking_id', $validated['bookingId'])
                ->delete();

            $passenger = Passenger::query()
                ->where('id', $validated['passengerId'])
                ->first();
            $passengerEmail = $passenger->email;
            $passenger->empty_seat = false;
            $passenger->save();

            $passengers = Passenger::query()
                ->with(['installments', 'payments', 'fees', 'passengerInvitation'])
                ->where('booking_id', $validated['bookingId'])
                ->get();

            if ($passengerEmail) {
                $customer = User::query()->where('email', '=', $passengerEmail)->first();

                if ($customer) {
                    $user = Auth::user();

                    UserLog::create([
                        'customer_id' => $customer->id,
                        'author_id' => $user->id,
                        'action' => 'User slot cleared',
                        'description' => '',
                    ]);
                }
            }

            return response()->json(['passengers' => $passengers]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json('error');
        }
    }
}
