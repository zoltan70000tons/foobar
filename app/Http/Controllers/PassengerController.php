<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Passenger;
use App\Models\Booking;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\PassengerRepository;
use Log;

class PassengerController extends Controller
{

    protected PassengerRepository $passengerRepository;
    public function __construct(PassengerRepository $passengerRepository){
        $this->passengerRepository = $passengerRepository;
    }


    public function updateSeat(Request $request)
    {


        $validated = $request->validate([
            'id' => 'required|int',
            'booking_id' => 'required|int',
            'confirmed_booking_email' => 'required|boolean',
            'survivor_number' => 'nullable|string', 
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
            'passenger_allocated_cost' => 'required|numeric',
            'passenger_balance' => 'required|numeric',
           // 'was_on_board' => 'required|boolean',
        ]);

        $user = User::where('email','=',$validated['email'])->first();
        if($user){
            return response()->json([
                'error' => 'User have an account, please use autocomplete',
            ], 422);
        }
        
        $slot = Passenger::where('id','=',$validated['id'])->first();
       
        Log::info($slot);
        $booking = Booking::findOrFail($validated['booking_id']);
        if($slot->booking_id !== $booking->id){
            return response()->json([
                'error' => 'Slot and Booking Id inconsistent.',
            ], 422);
        }

        $existingBooking = Booking::whereHas('passengers', function ($query) use ($validated) {
            $query->where('email', '=', $validated['email']);
        })
        ->where('id', '!=', $booking->id)
        ->where('event_id', '=', $booking->event_id)
        ->first();
        
        if ($existingBooking) {
            return response()->json([
                'error' => 'Passenger already found on other booking for the eventw.',
            ], 422);
        }
         
        $this->passengerRepository->updateSeat($slot, $booking, $validated);
        $slot->update($validated);

        return response()->json($slot);
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
        $results = User::with('detail')
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
                    'survivor_number' => $user->detail->survivor_number ?? null,
                    'confirmed_booking_email' => $user->detail->confirmed_booking_email ?? null,
                    'lead_passenger' => $user->detail->lead_passenger ?? null,
                    'payment_method' => $user->detail->payment_method ?? null,
                    'phone' => $user->detail->phone ?? null,
                    'address_first' => $user->detail->address_first ?? null,
                    'address_second' => $user->detail->address_second ?? null,
                    'city' => $user->detail->city ?? null,
                    'state' => $user->detail->state ?? null,
                    'postal_code' => $user->detail->postal_code ?? null,
                    'country' => $user->detail->country ?? null,
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
                ];
            });

        return response()->json($results);
    }
}
