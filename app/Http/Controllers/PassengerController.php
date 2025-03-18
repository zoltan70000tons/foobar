<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Passenger;
use App\Models\Booking;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\PassengerRepository;
use App\Rules\UniqueSurvivorInEvent;
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
        $validated = $request->validate([
            'id' => 'required|int',
            'booking_id' => 'required|int',
            'confirmed_booking_email' => 'required|boolean',
            'survivor_number' => ['required', 'string', 'regex:/^\d+$/', 'exists:survivor_numbers,survivor_number',new UniqueSurvivorInEvent($event_id, $request->booking_id),],
            'payment_method' => 'required|string|in:CREDIT_CARD,BANK_TRANSFER',
            'gender' => 'nullable|string|in:M,F,O',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'dob' => 'nullable|date',
            'citizenship' => 'nullable|string',
            'address_first' => 'required|string',
            'address_second' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'emergency_c_name' => 'required|string',
            'emergency_c_phone' => 'nullable|string',
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


        $slot = Passenger::where('id', '=', $validated['id'])->first();

        Log::info($slot);
        $booking = Booking::findOrFail($validated['booking_id']);
        if ($slot->booking_id !== $booking->id) {
            return response()->json([
                'error' => 'Slot and Booking Id inconsistent.',
            ], 422);
        }

        $existingBooking = Booking::whereHas('passengers', function ($query) use ($validated) {
            $query->where('survivor_number', '=', $validated['survivor_number']);
        })
            ->where('id', '!=', $booking->id)
            ->where('event_id', '=', $booking->event_id)
            ->first();

        if ($existingBooking) {
            return response()->json([
                'error' => 'Passenger already found on other booking for the event.',
            ], 422);
        }

        $this->passengerRepository->updateSeat($slot, $booking, $validated);
        $slot->update($validated);

        return response()->json($slot);
    }

    public function releaseSeat(Request $request)
    {
        $validated = $request->validate([
            'slotId' => 'required|int',
            'bookingId' => 'required|int'
        ]);
        try {
            $slot = Passenger::where('id', '=', $validated['slotId'])->first();
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
                $slot->passenger_allocated_cost = 0;
                $slot->passenger_balance = 0;
                $slot->was_on_board = 0;
                $slot->save();
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
        ]);

        $query = $request->get('query');
        $results = User::with(['detail', 'survivorNumber', 'customerAddress'])
            ->where('email', 'LIKE', "%{$query}%")
            ->orWhereHas('detail', function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                    ->orWhere('last_name', 'LIKE', "%{$query}%");
            })
            ->get()
            ->map(function ($user) {
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
                    'was_on_board' => $user->detail->was_on_board ?? null
                ];
            });

        return response()->json($results);
    }
}
