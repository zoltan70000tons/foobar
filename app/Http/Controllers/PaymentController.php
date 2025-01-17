<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Traits\BookingLogTrait;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Repositories\CalculationRepository;
use App\Repositories\PassengerRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;



class PaymentController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    use BookingLogTrait;

    protected PassengerRepository $passengerRepository;
    protected CalculationRepository $calculationRepository;
    public function __construct(PassengerRepository $passengerRepository, CalculationRepository $calculationRepository)
    {
        $this->passengerRepository = $passengerRepository;
        $this->calculationRepository = $calculationRepository;
    }

    public function store(Request $request)
    {
        try {
            return $this->withPermission([Permissions::CreateFees], function ($request) {
                $booking_id = $request->route('booking_id');
                $event_id = $request->route('event_id');
                $validated = $request->validate([
                    'passenger_id' => 'required|exists:passengers,id',
                    'BIP_ID' => 'nullable|string|max:50',
                    'amount' => 'required|numeric|min:0.01',
                    'type' => 'required|in:PAYMENT,REFOUND',
                    'notes' => 'nullable|string|max:255',
                    'transaction_date' => 'required|date'
                ]);
                $validated['source'] = 'MANUAL';
                Payment::create($validated);
                $this->calculationRepository->recalculateBalance($validated['passenger_id'], $booking_id, $event_id);
                $this->saveBookingLog(
                    $booking_id,
                    'Added Manual Payment',
                    "Manual {$validated['type']} value: \${$validated['amount']} was added to booking"
                );
                return redirect()->back()->with('success', 'Payment added successfully!');
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
            return redirect()->back()->with('error', 'Error creating payment!');
        }
    }
}
