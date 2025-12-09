<?php

namespace App\Http\Controllers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Enums\Permissions;
use App\Models\Installment;
use App\Models\Passenger;
use App\Support\GlobalLogger;
use Illuminate\Http\Request;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InstallmentController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;


    public function update(Request $request)
    {

        
        $validator = Validator::make($request->all(), [
            'installment' => 'required|exists:installments,id',
            'due_date' => 'required|date',
        ]);

        $validator->after(function ($validator) use ($request) {
            $instId = $request->input('installment');
            $newDateRaw = $request->input('due_date');
            try {
                $newDate = Carbon::parse($newDateRaw)->startOfDay();
            } catch (\Exception $e) {
                $validator->errors()->add('due_date', 'Invalid due date format.');
                return;
            }

            $current = Installment::find($instId);
            if (!$current) {
                $validator->errors()->add('installment', 'Installment not found.');
                return;
            }
            $passenger = Passenger::find($current->passenger_id);
            if (!$passenger || empty($passenger->booking_id)) {
                return;
            }

            $bookingId = $passenger->booking_id;
            $passengerIds = Passenger::where('booking_id', $bookingId)->pluck('id')->toArray();
            $next = Installment::whereIn('passenger_id', $passengerIds)
                ->where('due_date', '>', $current->due_date)
                ->orderBy('due_date', 'asc')
                ->first();

            if ($next) {
                try {
                    $nextDue = Carbon::parse($next->due_date)->startOfDay();
                } catch (\Exception $e) {
                    Log::warning("Next installment has invalid due_date for id {$next->id}: {$next->due_date}");
                    return;
                }

                if ($newDate->gt($nextDue)) {
                    $validator->errors()->add(
                        'due_date',
                        "The selected due date cannot be after the next installment's due date ({$nextDue->format('Y-m-d')})."
                    );
                    return;
                }
            }
        });

        if ($validator->fails()) {
            Log::info('Installment due date validation failed', $validator->errors()->toArray());
            return redirect()->back()->with('error', 'Error updating due date!')->withErrors($validator)->withInput();
        }

        
       

        $validated = $validator->validated();

        $bookingId = null;
        $currentInstallment = Installment::find($validated['installment']);
        if ($currentInstallment) {
            $passenger = Passenger::find($currentInstallment->passenger_id);
            if ($passenger) {
                $bookingId = $passenger->booking_id;
            }
        }


       try {
           return $this->withPermission([Permissions::EditInstallments], function ($request, $validated, $bookingId) {
              Log::info('inside with permission');
                $installment = Installment::find($validated['installment']);
                $oldDue = $installment ? $installment->due_date : null;
                if ($installment) {
                    $installment->update(['due_date' => $validated['due_date']]);
                }
                GlobalLogger::log(LogActionBooking::PAYMENT_SCHEDULE_UPDATED, 'booking', $bookingId, 'Payment schedule updated', [
                    'installment_id' => $validated['installment'],
                    'new_due_date' => Carbon::parse($validated['due_date'])->format('Y-m-d'),
                    'old_due_date' => $oldDue,
                    'Passenger Name' => $installment->passenger->full_name,
                    'Passsener Order'=> $installment->passenger->passenger_order
                ]);
                return redirect()->back()->with('success', 'Due date updated successfully!');
            }, $request, $validated, $bookingId);
        } catch (\Exception $e) {;
            $this->logException($e);
          return redirect()->back()->with('error', 'Error updating due date!');
        }
    }
}
