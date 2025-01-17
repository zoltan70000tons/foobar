<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Fee;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Repositories\CalculationRepository;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;



class FeeController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected CalculationRepository $calculationRepository;
    public function __construct(PassengerRepository $passengerRepository, CalculationRepository $calculationRepository)
    {
        $this->passengerRepository = $passengerRepository;
        $this->calculationRepository =$calculationRepository;
    }

    public function store(Request $request)
    {
        try {
            return $this->withPermission([Permissions::CreateFees], function ($request) {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'passenger_id' => 'required|exists:passengers,id',
                    'amount' => 'required|numeric|min:0.01',
                    'type' => 'required|string|max:255',

                ]);
                Fee::create($validated);
                $allocated_cost = $this->calculationRepository->recalculateAllocatedCost($validated['passenger_id'], $booking_id, $event_id);
                $this->saveBookingLog(
                    $booking_id,
                    'Added Manual Fee',
                    "Manual {$validated['type']} value: \${$validated['amount']} was added to booking"
                );
                return redirect()->back()->with('success', 'Fee added successfully!');
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
            return redirect()->back()->with('error', 'Error creating fee!');
        }
    }
}